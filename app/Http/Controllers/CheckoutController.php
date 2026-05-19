<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalculateShippingRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\EcontOfficesRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\Econt\EcontCityResolverService;
use App\Services\OrderService;
use App\Services\SettingsService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    /**
     * Resolve CartService using the session_id sent by the React client.
     * This keeps checkout pricing aligned with the cart endpoints instead of falling back to Laravel's cookie session ID.
     * If user is authenticated, ignore session_id and use user cart.
     */
    private function getCartService(Request $request): CartService
    {
        if (Auth::check()) {
            return new CartService(null);
        }

        return new CartService($this->frontendCartSessionId($request));
    }

    private function frontendCartSessionId(Request $request): ?string
    {
        $sessionId = $request->input('session_id')
            ?? $request->input('sessionId')
            ?? $request->query('session_id')
            ?? $request->query('sessionId')
            ?? $request->header('X-Cart-Session-Id');

        if (! is_scalar($sessionId)) {
            return null;
        }

        $sessionId = trim((string) $sessionId);

        if ($sessionId === '') {
            return null;
        }

        Session::put('cart_session_id', $sessionId);

        return $sessionId;
    }

    /**
     * Build temporary order items from request payload when the React page has not synced a server cart yet.
     */
    private function buildItemsFromRequest(array $items): Collection
    {
        $products = Product::query()
            ->whereIn('id', collect($items)->pluck('product_id')->all())
            ->get()
            ->keyBy('id');
        $variants = ProductVariant::query()
            ->whereIn('id', collect($items)
                ->map(fn (array $item) => $item['product_variant_id'] ?? $item['variant_id'] ?? null)
                ->filter()
                ->all())
            ->get()
            ->keyBy('id');

        return collect($items)->map(function (array $item) use ($products, $variants) {
            $product = $products->get($item['product_id']);
            $variantId = $item['product_variant_id'] ?? $item['variant_id'] ?? null;
            $variant = $variantId ? $variants->get($variantId) : null;
            $variant = $variant?->product_id === $product?->id ? $variant : null;
            $quantity = (int) $item['quantity'];
            $price = (float) ($variant?->sale_price ?? $variant?->price ?? $product?->sale_price ?? $product?->price ?? 0);

            $orderItem = new OrderItem([
                'product_id' => $item['product_id'],
                'product_variant_id' => $variant?->id,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $price * $quantity,
            ]);
            $orderItem->setRelation('product', $product);
            $orderItem->setRelation('variant', $variant);

            return $orderItem;
        });
    }

    /**
     * Normalize raw Econt office payloads into a stable frontend-friendly shape.
     */
    private function normalizeEcontOffice(array $office): array
    {
        $code = $office['code']
            ?? $office['officeCode']
            ?? $office['office_code']
            ?? $office['id']
            ?? null;

        $name = $office['name']
            ?? $office['officeName']
            ?? $office['office_name']
            ?? $office['fullName']
            ?? null;

        $city = $office['city']
            ?? $office['cityName']
            ?? data_get($office, 'address.city.name')
            ?? data_get($office, 'address.cityName')
            ?? null;

        $address = $office['address']
            ?? $office['fullAddress']
            ?? $office['addressLine']
            ?? $office['streetAddress']
            ?? null;

        if (is_array($address)) {
            $address = $address['fullAddress']
                ?? $address['addressLine']
                ?? $address['streetAddress']
                ?? trim(implode(' ', array_filter([
                    $address['street'] ?? null,
                    $address['num'] ?? null,
                    $address['quarter'] ?? null,
                ])));
        }

        return [
            'code' => $code !== null ? (string) $code : null,
            'name' => $name !== null ? (string) $name : null,
            'city' => $city !== null ? (string) $city : null,
            'address' => is_string($address) && trim($address) !== '' ? trim($address) : null,
            'is_aps' => (bool) ($office['isAPS'] ?? $office['is_aps'] ?? false),
        ];
    }

    private function enrichSelectedOffice(array $validated, EcontCityResolverService $econtCityResolverService): array
    {
        if (($validated['shipping_method'] ?? null) === 'address') {
            $validated['econt_office_code'] = null;
            $validated['econt_office_name'] = null;
            $validated['econt_office_address'] = null;
            $validated['econt_office_is_aps'] = false;

            return $validated;
        }

        $officeCode = trim((string) ($validated['econt_office_code'] ?? ''));

        if ($officeCode === '') {
            return $validated;
        }

        $office = $econtCityResolverService->getOfficeByCode($officeCode);

        if (! is_array($office)) {
            return $validated;
        }

        $normalizedOffice = $this->normalizeEcontOffice($office);

        $validated['econt_office_code'] = $normalizedOffice['code'] ?? $validated['econt_office_code'];
        $validated['econt_office_name'] = $validated['econt_office_name'] ?? $normalizedOffice['name'];
        $validated['econt_office_address'] = $validated['econt_office_address'] ?? $normalizedOffice['address'];
        $validated['econt_office_is_aps'] = (bool) ($validated['econt_office_is_aps'] ?? $normalizedOffice['is_aps'] ?? false);

        if (($validated['shipping_method'] ?? null) !== 'apm' && $validated['econt_office_is_aps']) {
            $validated['shipping_method'] = 'apm';
        }

        return $validated;
    }

    /**
     * Return Econt offices for a city in a stable JSON shape used by the React checkout.
     */
    public function econtOffices(EcontOfficesRequest $request, EcontCityResolverService $econtCityResolverService)
    {
        $validated = $request->validated();

        $city = trim($validated['city']);
        try {
            $offices = collect($econtCityResolverService->getOffices($city))
                ->filter(fn ($office) => is_array($office))
                ->map(fn (array $office) => $this->normalizeEcontOffice($office))
                ->filter(fn (array $office) => ! empty($office['code']) && ! empty($office['name']))
                ->values();
        } catch (\Throwable $e) {
            Log::error('Econt offices endpoint failed', [
                'city' => $city,
                'error' => $e->getMessage(),
            ]);

            $offices = collect();
        }

        Log::info('Econt offices lookup response', [
            'city' => $city,
            'count' => $offices->count(),
        ]);

        return response()->json([
            'offices' => $offices,
        ]);
    }

    /**
     * Calculate shipping cost for the current cart based on shipping address.
     * This is used by frontend to show shipping cost before checkout.
     */
    public function calculateShipping(
        CalculateShippingRequest $request,
        SettingsService $settingsService,
        EcontCityResolverService $econtCityResolverService
    )
    {
        $validated = $this->enrichSelectedOffice($request->validated(), $econtCityResolverService);

        $cartService = $this->getCartService($request);
        $requestedSessionId = $this->frontendCartSessionId($request);
        $cartItems = $cartService->items();
        $requestItems = ! empty($validated['items'])
            ? $this->buildItemsFromRequest($validated['items'])
            : collect();
        $effectiveItems = $cartItems->isNotEmpty() ? $cartItems : $requestItems;
        $effectiveSubtotal = $cartItems->isNotEmpty()
            ? $cartService->subtotal()
            : (float) $requestItems->sum('total');

        $user = Auth::user();

        $tempOrder = new Order([
            'customer_name' => $user?->name ?? ($validated['customer_name'] ?? 'Shipping Estimate'),
            'customer_email' => $user?->email ?? ($validated['customer_email'] ?? null),
            'customer_phone' => $user?->phone ?? ($validated['customer_phone'] ?? (string) config('services.econt.sender.phone', '0000000000')),
            'subtotal' => $effectiveSubtotal,
            'shipping_method' => $validated['shipping_method'],
            'shipping_address' => $validated['shipping_address'] ?? '',
            'shipping_city' => $validated['shipping_city'],
            'shipping_postcode' => $validated['shipping_postcode'] ?? null,
            'econt_office_code' => $validated['shipping_method'] === 'address'
                ? null
                : ($validated['econt_office_code'] ?? null),
            'econt_office_name' => $validated['shipping_method'] === 'address'
                ? null
                : ($validated['econt_office_name'] ?? null),
            'econt_office_address' => $validated['shipping_method'] === 'address'
                ? null
                : ($validated['econt_office_address'] ?? null),
            'econt_office_is_aps' => $validated['shipping_method'] === 'address'
                ? false
                : (bool) ($validated['econt_office_is_aps'] ?? false),
            'payment_method' => $validated['payment_method'] ?? null,
        ]);
        $tempOrder->setRelation('items', $effectiveItems);

        if ($effectiveItems->isEmpty()) {
            Log::warning('calculate shipping skipped because cart is empty', [
                'requested_session_id' => $requestedSessionId,
                'resolved_session_id' => $cartService->getSessionId(),
                'user_id' => Auth::id(),
                'shipping_method' => $validated['shipping_method'],
                'shipping_city' => $validated['shipping_city'],
            ]);

            return response()->json([
                'shipping_price' => 0.0,
                'message' => 'Cart is empty.',
            ], 422);
        }

        // Use a live estimate here so the checkout UI can show Econt pricing
        // even for payment methods that are deferred in the final order flow.
        $tempOrder->shipping_price = $settingsService->estimateShipping($tempOrder);

        Log::info('calculate shipping price', [
            'requested_session_id' => $requestedSessionId,
            'resolved_session_id' => $cartService->getSessionId(),
            'user_id' => Auth::id(),
            'subtotal' => $effectiveSubtotal,
            'shipping_price' => $tempOrder->shipping_price ?? 0.0,
            'order_id' => $tempOrder->id,
            'items_count' => $effectiveItems->count(),
            'items_source' => $cartItems->isNotEmpty() ? 'cart' : 'request',
            'econt_office_code' => $tempOrder->econt_office_code,
            'shipping_method' => $tempOrder->shipping_method,
            'payment_method' => $tempOrder->payment_method,
        ]);

        return response()->json([
            'shipping_price' => $tempOrder->shipping_price ?? 0.0,
        ]);
    }
    /**
     * Process the checkout by validating the request data and creating an order using the OrderService.
      * @param \Illuminate\Http\Request $request
      * @param \App\Services\OrderService $orderService
      * @return \Illuminate\Http\JsonResponse
      * @throws \App\Exceptions\CheckoutException
     */
    public function store(
        CheckoutRequest $request,
        OrderService $orderService,
        StripeCheckoutService $stripeCheckoutService,
        EcontCityResolverService $econtCityResolverService
    ) 
    {
        $validated = $this->enrichSelectedOffice($request->validated(), $econtCityResolverService);
        $validated['session_id'] = $this->frontendCartSessionId($request);

        try {
            $order = $orderService->createFromItems($validated);

            if ($order->payment_method === 'stripe') {
                $session = $stripeCheckoutService->createSession($order, $validated['session_id'] ?? null);
                $order->updateQuietly([
                    'stripe_checkout_session_id' => $session->id,
                    'stripe_payment_intent_id' => is_string($session->payment_intent ?? null)
                        ? $session->payment_intent
                        : null,
                ]);

                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'checkout_url' => $session->url,
                ]);
            }

            return response()->json([
                'success'  => true,
                'order_id' => $order->id,
            ]);

        } catch (CheckoutException $e) {
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->status());

        } catch (\Exception $e) {
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred during checkout. Please try again later.',
            ], 500);
        }
    }
}
