<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\CalculateShippingRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\EcontOfficesRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\Econt\EcontCityResolverService;
use App\Services\OrderService;
use App\Services\SettingsService;
use App\Services\StripeCheckoutService;
use App\Support\CartSessionToken;
use App\Support\LocalizedContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function paymentMethods(Request $request)
    {
        $locale = LocalizedContent::requestedLocale($request);
        $settings = Setting::current();
        $methods = [
            [
                'value' => 'bank_transfer',
                'label' => trans('orders.payment_methods.bank_transfer', [], $locale),
            ],
            [
                'value' => 'cod',
                'label' => trans('orders.payment_methods.cod', [], $locale),
            ],
        ];

        if ($settings->stripe_enabled) {
            $methods[] = [
                'value' => 'stripe',
                'label' => trans('orders.payment_methods.stripe', [], $locale),
            ];
        }

        return response()->json([
            'stripe_enabled' => (bool) $settings->stripe_enabled,
            'payment_methods' => $methods,
        ]);
    }

    /**
     * Resolve CartService using the session_id sent by the React client.
     * This keeps checkout pricing aligned with the cart endpoints instead of
     * falling back to Laravel's cookie session ID.
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
        return CartSessionToken::fromRequest($request, generateWhenMissing: true);
    }

    /**
     * Build temporary order items from request payload when the React page has
     * not synced a server cart yet.
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
    private function normalizeEcontOffice(array $office, string $locale = 'bg'): array
    {
        $locale = LocalizedContent::normalizeLocale($locale);
        $code = $office['code']
            ?? $office['officeCode']
            ?? $office['office_code']
            ?? $office['id']
            ?? null;

        $name = $locale === 'bg'
            ? ($office['name']
                ?? $office['officeName']
                ?? $office['office_name']
                ?? $office['fullName']
                ?? null)
            : ($office['nameEn']
                ?? $office['officeNameEn']
                ?? $office['fullNameEn']
                ?? $office['name']
                ?? $office['officeName']
                ?? $office['office_name']
                ?? $office['fullName']
                ?? null);

        $city = $locale === 'bg'
            ? ($office['city']
                ?? $office['cityName']
                ?? data_get($office, 'address.city.name')
                ?? data_get($office, 'address.cityName')
                ?? $office['hubName']
                ?? null)
            : ($office['cityEn']
                ?? $office['cityNameEn']
                ?? data_get($office, 'address.city.nameEn')
                ?? data_get($office, 'address.cityNameEn')
                ?? $office['hubNameEn']
                ?? $office['city']
                ?? $office['cityName']
                ?? data_get($office, 'address.city.name')
                ?? data_get($office, 'address.cityName')
                ?? $office['hubName']
                ?? null);

        $address = $office['address']
            ?? $office['fullAddress']
            ?? $office['addressLine']
            ?? $office['streetAddress']
            ?? null;

        if (is_array($address)) {
            $address = $this->formatOfficeAddress($address, $locale);
        } elseif ($locale !== 'bg' && is_string($address)) {
            $address = $this->transliterateToLatin($address);
        }

        return [
            'code' => $code !== null ? (string) $code : null,
            'name' => $name !== null ? (string) $name : null,
            'city' => $city !== null ? (string) $city : null,
            'address' => is_string($address) && trim($address) !== '' ? trim($address) : null,
            'is_aps' => (bool) ($office['isAPS'] ?? $office['is_aps'] ?? false),
        ];
    }

    private function formatOfficeAddress(array $address, string $locale): ?string
    {
        if ($locale === 'bg') {
            $formatted = $address['fullAddress']
                ?? $address['addressLine']
                ?? $address['streetAddress']
                ?? trim(implode(' ', array_filter([
                    $address['street'] ?? null,
                    $address['num'] ?? null,
                    $address['quarter'] ?? null,
                ])));

            return is_string($formatted) && trim($formatted) !== '' ? trim($formatted) : null;
        }

        $city = data_get($address, 'city.nameEn')
            ?? data_get($address, 'cityNameEn')
            ?? data_get($address, 'city.name')
            ?? data_get($address, 'cityName')
            ?? null;

        $parts = array_filter([
            $city,
            $address['quarter'] ?? null,
            $address['street'] ?? null,
            $address['num'] ?? null ? 'No. '.$address['num'] : null,
        ], fn ($part) => is_string($part) ? trim($part) !== '' : $part !== null);

        if ($parts !== []) {
            return $this->transliterateToLatin(implode(' ', $parts));
        }

        $formatted = $address['fullAddress']
            ?? $address['addressLine']
            ?? $address['streetAddress']
            ?? null;

        return is_string($formatted) && trim($formatted) !== ''
            ? $this->transliterateToLatin($formatted)
            : null;
    }

    private function transliterateToLatin(string $value): string
    {
        $normalized = str_replace('№', 'No. ', $value);
        $normalized = Str::of($normalized)
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        return $normalized;
    }

    private function enrichSelectedOffice(
        array $validated,
        EcontCityResolverService $econtCityResolverService,
        string $locale = 'bg'
    ): array
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

        $normalizedOffice = $this->normalizeEcontOffice($office, $locale);

        $validated['econt_office_code'] = $normalizedOffice['code'] ?? $validated['econt_office_code'];
        $validated['econt_office_name'] = $normalizedOffice['name'] ?? $validated['econt_office_name'];
        $validated['econt_office_address'] = $normalizedOffice['address'] ?? $validated['econt_office_address'];
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
        $locale = LocalizedContent::requestedLocale($request);

        try {
            $offices = collect($econtCityResolverService->getOffices($city))
                ->filter(fn ($office) => is_array($office))
                ->map(fn (array $office) => $this->normalizeEcontOffice($office, $locale))
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
    ) {
        $validated = $this->enrichSelectedOffice(
            $request->validated(),
            $econtCityResolverService,
            LocalizedContent::requestedLocale($request)
        );

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

        try {
            $tempOrder->shipping_price = $settingsService->estimateShipping($tempOrder);
        } catch (CheckoutException $e) {
            return response()->json([
                'shipping_price' => null,
                'message' => $e->getMessage(),
            ], $e->status());
        }

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
     * Process the checkout by validating the request data and creating an order.
     *
     * @throws CheckoutException
     */
    public function store(
        CheckoutRequest $request,
        OrderService $orderService,
        EcontCityResolverService $econtCityResolverService
    ) {
        $locale = LocalizedContent::requestedLocale($request);
        $validated = $this->enrichSelectedOffice(
            $request->validated(),
            $econtCityResolverService,
            $locale
        );
        $validated['locale'] = $locale;
        $validated['session_id'] = $this->frontendCartSessionId($request);

        try {
            $order = $orderService->createFromItems($validated);

            if ($order->payment_method === 'stripe') {
                $stripeCheckoutService = app(StripeCheckoutService::class);
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
                'success' => true,
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
