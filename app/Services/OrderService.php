<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Exceptions\CheckoutException;
use App\Jobs\CalculateBankTransferShippingJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\LocalizedContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected SettingsService $settingsService,
        private PaymentService $paymentService,
        private CartService $cartService,
        private StockService $stockService
    ) {}

    /**
     * Recalculate subtotal, shipping and total for an existing order.
     */
    public function recalculateTotal(Order $order): void
    {
        $order->subtotal = $order->items()->sum('total');
        $this->settingsService->applyTotals($order);
        $order->saveQuietly();
        $order->refresh();
    }

    public function createFromItems(array $data = []): Order
    {
        return DB::transaction(function () use ($data) {
            $shippingMethod = $data['shipping_method'] ?? 'address';
            $locale = LocalizedContent::normalizeLocale($data['locale'] ?? null);
            $stripeUnavailableMessage = 'Card payments are currently unavailable. Please try again later.';

            if (($data['payment_method'] ?? null) === 'stripe' && ! Setting::current()->stripe_enabled) {
                throw new CheckoutException(trans('orders.errors.stripe_disabled', [], $locale), 422);
            }

            if (($data['payment_method'] ?? null) === 'stripe' && blank(config('services.stripe.sk'))) {
                throw new CheckoutException($stripeUnavailableMessage, 422);
            }

            $user = Auth::user();

            $order = Order::create([
                'user_id' => $user?->id,
                'locale' => $locale,
                'customer_name' => $user?->name ?? $data['customer_name'],
                'customer_email' => $user?->email ?? $data['customer_email'],
                'customer_phone' => $user?->phone ?? ($data['customer_phone'] ?? null),
                'shipping_address' => $data['shipping_address'] ?? '',
                'shipping_city' => $data['shipping_city'],
                'shipping_postcode' => $data['shipping_postcode'] ?? null,
                'shipping_method' => $shippingMethod,
                'econt_office_code' => $shippingMethod === 'address'
                    ? null
                    : ($data['econt_office_code'] ?? null),
                'econt_office_name' => $shippingMethod === 'address'
                    ? null
                    : ($data['econt_office_name'] ?? null),
                'econt_office_address' => $shippingMethod === 'address'
                    ? null
                    : ($data['econt_office_address'] ?? null),
                'econt_office_is_aps' => $shippingMethod === 'address'
                    ? false
                    : (bool) ($data['econt_office_is_aps'] ?? false),
                'holiday_delivery_day' => $data['holiday_delivery_day'] ?? null,
                'status' => 'pending',
                'subtotal' => 0,
                'shipping_price' => 0,
                'total' => 0,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $localizedProductName = (string) LocalizedContent::localizedValue($product, 'name', $locale);
                $variantId = $itemData['product_variant_id'] ?? $itemData['variant_id'] ?? null;
                $variant = null;

                if ($variantId) {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->whereKey($variantId)
                        ->first();

                    if (! $variant) {
                        throw new CheckoutException(trans('orders.errors.invalid_variant', [
                            'product' => $localizedProductName,
                        ], $locale), 422);
                    }
                } elseif ($product->variants()->exists()) {
                    throw new CheckoutException(trans('orders.errors.variant_required', [
                        'product' => $localizedProductName,
                    ], $locale), 422);
                }

                if ($variant) {
                    $this->stockService->reserveVariant($variant, (int) $itemData['quantity']);
                } else {
                    $this->stockService->reserve($product, (int) $itemData['quantity']);
                }

                $localizedVariantSize = $variant
                    ? LocalizedContent::localizedValue($variant, 'size', $locale)
                    : null;
                $productName = $localizedVariantSize
                    ? "{$localizedProductName} - {$localizedVariantSize}"
                    : $localizedProductName;
                $price = $variant
                    ? ($variant->sale_price ?? $variant->price)
                    : ($product->sale_price ?? $product->price);
                $total = $price * $itemData['quantity'];

                $subtotal += $total;

                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $variant?->id,
                    'product_name' => $productName,
                    'price' => $price,
                    'quantity' => $itemData['quantity'],
                    'total' => $total,
                ]);
            }

            $order->subtotal = $subtotal;
            $this->settingsService->applyTotals($order);
            $order->save();

            $this->paymentService->handle($order);

            if ($order->payment_method !== 'stripe') {
                DB::afterCommit(fn () => OrderPlaced::dispatch(
                    $order->id,
                    $data['session_id'] ?? $data['sessionId'] ?? null
                ));
            }

            if (in_array($order->payment_method, ['bank_transfer', 'cod'], true)) {
                DB::afterCommit(fn () => dispatch(new CalculateBankTransferShippingJob($order->id)));
            }

            return $order;
        });
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->updateQuietly([
                'status' => 'cancelled',
            ]);
        });
    }

    public function releaseReservedStock(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->releaseReservedStockForLockedOrder($lockedOrder);
        });
    }

    public function releaseReservedStockForLockedOrder(Order $order): void
    {
        $order->loadMissing(['items.product', 'items.variant']);

        if ($order->stock_released_at !== null) {
            return;
        }

        foreach ($order->items as $item) {
            if ($item->variant) {
                $this->stockService->releaseVariant($item->variant, (int) $item->quantity);
            } elseif ($item->product) {
                $this->stockService->release($item->product, (int) $item->quantity);
            }
        }

        $order->forceFill([
            'stock_released_at' => now(),
        ])->saveQuietly();
    }

    public function deleteOrderWithItems(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->releaseReservedStockForLockedOrder($order);

            $order->items()->delete();
            $order->delete();
        });
    }
}
