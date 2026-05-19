<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Stripe\Refund;
use Stripe\StripeClient;

class StripeRefundService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.sk'));
    }

    public function refund(Order $order, float $amount): Refund
    {
        if ($order->payment_method !== 'stripe') {
            throw new \InvalidArgumentException('Refunds from this action are available only for Stripe orders.');
        }

        if (! in_array($order->payment_status, [PaymentStatus::PAID->value, PaymentStatus::PARTIALLY_REFUNDED->value], true)) {
            throw new \InvalidArgumentException('Only paid Stripe orders can be refunded.');
        }

        if (blank($order->stripe_payment_intent_id)) {
            throw new \InvalidArgumentException('Missing Stripe payment intent for this order.');
        }

        $remaining = max(0, (float) $order->total - (float) $order->refunded_amount);
        $amount = round($amount, 2);

        if ($amount <= 0 || $amount > $remaining) {
            throw new \InvalidArgumentException('Refund amount must be greater than zero and not exceed the remaining paid amount.');
        }

        $refund = $this->stripe->refunds->create([
            'payment_intent' => $order->stripe_payment_intent_id,
            'amount' => $this->toMinorUnit($amount),
            'metadata' => [
                'order_id' => (string) $order->id,
            ],
        ]);

        $this->applyRefund($order, $refund->id, $amount);

        return $refund;
    }

    public function applyRefund(Order $order, ?string $refundId, float $amount): void
    {
        DB::transaction(function () use ($order, $refundId, $amount): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $refundedAmount = min(
                (float) $lockedOrder->total,
                round((float) $lockedOrder->refunded_amount + $amount, 2),
            );

            $isFullyRefunded = $refundedAmount >= (float) $lockedOrder->total;

            $lockedOrder->updateQuietly([
                'stripe_refund_id' => $refundId ?? $lockedOrder->stripe_refund_id,
                'refunded_amount' => $refundedAmount,
                'refunded_at' => now(),
                'payment_status' => $isFullyRefunded
                    ? PaymentStatus::REFUNDED->value
                    : PaymentStatus::PARTIALLY_REFUNDED->value,
                'status' => $isFullyRefunded
                    ? OrderStatus::RETURNED->value
                    : $lockedOrder->status,
            ]);
        });
    }

    private function toMinorUnit(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
