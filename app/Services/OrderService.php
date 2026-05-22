<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Events\OrderPlaced;
use App\Jobs\CalculateBankTransferShippingJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
            protected SettingsService $settingsService,
            private PaymentService $paymentService,
            private CartService $cartService,
            private StockService $stockService
        ) 
        {}

    /**
     * Recalculate the total for a given order. This method performs the following steps:
     *  1. It calculates the subtotal by summing the total of all items associated with the order.
     *  2. It applies the shipping rules and recalculates the shipping price using the SettingsService.
     *  3. It saves the updated order totals to the database without triggering model events.
     *  4. It refreshes the order instance to ensure it has the latest data from the database.  
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

            if (($data['payment_method'] ?? null) === 'stripe' && ! Setting::current()->stripe_enabled) {
                throw new CheckoutException('Stripe payments are currently disabled.', 422);
            }

            $user = Auth::user();

            $order = Order::create([
                // If the user is authenticated, associate the order with the user's ID. Otherwise, the order will be created without a user association.
                'user_id'           => $user?->id,
                'customer_name'     => $user?->name ?? $data['customer_name'],
                'customer_email'    => $user?->email ?? $data['customer_email'],
                'customer_phone'    => $user?->phone ?? ($data['customer_phone'] ?? null),

                'shipping_address'  => $data['shipping_address'] ?? '',
                'shipping_city'     => $data['shipping_city'],
                'shipping_postcode' => $data['shipping_postcode'] ?? null,
                'shipping_method'   => $shippingMethod,
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

                'status'            => 'pending',

                'subtotal'          => 0,
                'shipping_price'    => 0,
                'total'             => 0,

                'payment_method'    => $data['payment_method'],
                'payment_status'    => 'pending',

                'notes'             => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $variantId = $itemData['product_variant_id'] ?? $itemData['variant_id'] ?? null;
                $variant = null;

                if ($variantId) {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->whereKey($variantId)
                        ->first();

                    if (! $variant) {
                        throw new \App\Exceptions\CheckoutException("Невалиден вариант за продукт: {$product->name}", 422);
                    }
                } elseif ($product->variants()->exists()) {
                    throw new \App\Exceptions\CheckoutException("Моля, изберете вариант за продукт: {$product->name}", 422);
                }

                if ($variant) {
                    $this->stockService->reserveVariant($variant, (int) $itemData['quantity']);
                } else {
                    $this->stockService->reserve($product, (int) $itemData['quantity']);
                }

                $productName = $variant?->size
                    ? "{$product->name} - {$variant->size}"
                    : $product->name;
                $price = $variant
                    ? ($variant->sale_price ?? $variant->price)
                    : ($product->sale_price ?? $product->price);
                $total = $price * $itemData['quantity'];
                
                $subtotal += $total;

                $order->items()->create([
                    'product_id'   => $itemData['product_id'],
                    'product_variant_id' => $variant?->id,
                    'product_name' => $productName,
                    'price'        => $price,
                    'quantity'     => $itemData['quantity'],
                    'total'        => $total,
                ]);
            }

            $order->subtotal = $subtotal;
            $this->settingsService->applyTotals($order);
            $order->save();

            // ⚠️ PaymentService вътре в транзакцията (OK за сега)
            $this->paymentService->handle($order);

            // ✅ ЕДИН event, ясно и чисто
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

    /**
     * Cancel an existing order. This method performs the following steps:
         *  1. It starts a database transaction to ensure atomicity of the operation.
         *  2. It updates the order's status to 'cancelled' in the database without triggering model events.
         *  3. Finally, it completes the transaction, ensuring that the order cancellation is applied atomically to maintain data integrity.
         *  
         * Note: This method assumes that any necessary stock adjustments or other related operations are handled elsewhere,
         *  as it only updates the order's status.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order){
            $order->updateQuietly([
                'status' => 'cancelled',
            ]);
        });
    }

    /**
     * INTERNAL / DEV ONLY
     * Delte an order along with its associated items. This method performs the following steps:
     *  1. It starts a database transaction to ensure atomicity of the operation.
     *  2. It deletes all items associated with the order from the database.
     *  3. It deletes the order itself from the database.
     *  4. Finally, it completes the transaction, ensuring that all deletions are applied atomically to maintain data integrity.
     */
    public function deleteOrderWithItems(Order $order): void
    {
          DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });
    }
}
