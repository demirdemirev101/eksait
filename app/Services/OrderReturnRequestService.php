<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\ShipmentCreated;
use App\Mail\OrderReturnRequestedMail;
use App\Models\Order;
use App\Models\Shipment;
use App\Policies\CancelOrderPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class OrderReturnRequestService
{
    public function __construct(
        private CancelOrderPolicy $cancelOrderPolicy,
        private EcontShippingService $econtShippingService,
        private StripeRefundService $stripeRefundService,
    ) {}

    public function requestReturn(Order $order): Shipment
    {
        $order->loadMissing(['returnShipment', 'shipment', 'items.product', 'items.variant']);

        if (! $this->cancelOrderPolicy->canRequestReturn($order)) {
            throw new InvalidArgumentException('This order cannot request a return.');
        }

        $this->assertStripeRefundCanBeAttempted($order);

        $shipment = DB::transaction(function () use ($order): Shipment {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedOrder->load(['returnShipment', 'shipment', 'items.product', 'items.variant']);

            if (! $this->cancelOrderPolicy->canRequestReturn($lockedOrder)) {
                throw new InvalidArgumentException('This order cannot request a return.');
            }

            return $this->econtShippingService->createReturnShipmentRecord($lockedOrder);
        });

        try {
            $refreshedOrder = $order->fresh(['returnShipment']);
            $this->refundStripePaymentIfNeeded($refreshedOrder);
        } catch (\Throwable $e) {
            $shipment->update([
                'status' => 'error',
                'error_message' => 'Return shipment was created, but Stripe refund did not finish: '.$e->getMessage(),
            ]);

            throw $e;
        }

        $this->markReturnRequested($order->fresh());

        event(new ShipmentCreated($shipment->id, $order->id));

        if ($order->customer_email) {
            Mail::to($order->customer_email)->send(new OrderReturnRequestedMail($order->id));
        }

        return $shipment->fresh();
    }

    private function assertStripeRefundCanBeAttempted(Order $order): void
    {
        if ($this->remainingStripeRefund($order) <= 0) {
            return;
        }

        if (blank($order->stripe_payment_intent_id)) {
            throw new InvalidArgumentException('Missing Stripe payment intent for this order.');
        }
    }

    private function refundStripePaymentIfNeeded(Order $order): void
    {
        $remaining = $this->remainingStripeRefund($order);

        if ($remaining <= 0) {
            return;
        }

        $this->stripeRefundService->refund($order, $remaining, OrderStatus::RETURN_REQUESTED);
    }

    private function remainingStripeRefund(Order $order): float
    {
        if ($order->payment_method !== 'stripe') {
            return 0.0;
        }

        if (! in_array($order->payment_status, [
            PaymentStatus::PAID->value,
            PaymentStatus::PARTIALLY_REFUNDED->value,
        ], true)) {
            return 0.0;
        }

        return round(max(0, (float) $order->total - (float) $order->refunded_amount), 2);
    }

    private function markReturnRequested(Order $order): void
    {
        $order->updateQuietly([
            'status' => OrderStatus::RETURN_REQUESTED->value,
        ]);
    }
}
