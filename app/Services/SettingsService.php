<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    public function __construct(
        private EcontShippingService $econtShippingService,
    ) {}

    public function applyShipping(Order $order): void
    {
        if (! $this->deliveryEnabled()) {
            $order->shipping_price = 0;

            return;
        }

        if ($this->qualifiesForFreeDelivery($order)) {
            $order->shipping_price = 0;

            return;
        }

        if ($this->shouldDeferShippingCalculation($order)) {
            $order->shipping_price = 0;

            return;
        }

        if (! config('services.econt.enabled')) {
            $order->shipping_price = 0;

            return;
        }

        $order->shipping_price = $this->calculateEcontShippingOrFail($order);
    }

    public function estimateShipping(Order $order): float
    {
        if (! $this->deliveryEnabled()) {
            return 0.0;
        }

        if ($this->qualifiesForFreeDelivery($order)) {
            return 0.0;
        }

        if (! config('services.econt.enabled')) {
            return 0.0;
        }

        return $this->calculateEcontShippingOrFail($order);
    }

    public function applyTotals(Order $order): void
    {
        $this->applyShipping($order);
        $order->total = ($order->subtotal ?? 0) + ($order->shipping_price ?? 0);
    }

    public function calculateEcontShippingForOrder(Order $order): float
    {
        return $this->calculateEcontShipping($order);
    }

    public function qualifiesForFreeDelivery(Order $order): bool
    {
        $settings = Setting::current();

        return $settings->delivery_enabled
            && $settings->free_delivery_over !== null
            && $order->subtotal >= $settings->free_delivery_over;
    }

    public function shouldDeferShippingCalculation(Order $order): bool
    {
        return in_array($order->payment_method, ['bank_transfer', 'cod'], true);
    }

    private function deliveryEnabled(): bool
    {
        return (bool) Setting::current()->delivery_enabled;
    }

    private function calculateEcontShipping(Order $order): float
    {
        try {
            return $this->econtShippingService->estimate($order);
        } catch (\Throwable $e) {
            Log::warning('Econt shipping calculate failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    private function calculateEcontShippingOrFail(Order $order): float
    {
        try {
            $shippingPrice = $this->econtShippingService->estimate($order);
        } catch (\Throwable $e) {
            Log::warning('Econt shipping calculate failed', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'error' => $e->getMessage(),
            ]);

            throw new CheckoutException('Unable to calculate shipping price. Please try again.', 422);
        }

        if ($shippingPrice <= 0) {
            Log::warning('Econt shipping returned non-positive price', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'shipping_price' => $shippingPrice,
            ]);

            throw new CheckoutException('Unable to calculate shipping price. Please try again.', 422);
        }

        return $shippingPrice;
    }
}
