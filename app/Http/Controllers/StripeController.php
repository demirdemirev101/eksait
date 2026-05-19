<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\OrderReadyForShipment;
use App\Models\Order;
use App\Services\StripeRefundService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function webhook(Request $request, StockService $stockService): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            Log::error('Stripe webhook secret is not configured.');

            return response()->json(['error' => 'Webhook is not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;
            $cartSessionId = $session->metadata->cart_session_id ?? null;

            if (($session->payment_status ?? null) !== 'paid') {
                Log::warning('Stripe checkout completed without paid status', [
                    'order_id' => $orderId,
                    'stripe_payment_status' => $session->payment_status ?? null,
                ]);

                return response()->json(['received' => true]);
            }

            $shouldDispatchEvents = DB::transaction(function () use ($orderId, $session) {
                $order = Order::whereKey($orderId)->lockForUpdate()->first();

                if (! $order || $order->payment_status === 'paid') {
                    return false;
                }

                if ($order->payment_method !== 'stripe') {
                    Log::warning('Stripe webhook received for non-stripe order', [
                        'order_id' => $orderId,
                        'payment_method' => $order->payment_method ?? null,
                    ]);

                    return false;
                }

                $expectedAmount = (int) round(((float) $order->total) * 100);
                $stripeAmount = (int) ($session->amount_total ?? 0);

                if ($expectedAmount !== $stripeAmount) {
                    Log::error('Stripe paid amount does not match order total', [
                        'order_id' => $order->id,
                        'expected_amount' => $expectedAmount,
                        'stripe_amount' => $stripeAmount,
                    ]);

                    return false;
                }

                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'ready_for_shipment',
                    'stripe_checkout_session_id' => $session->id ?? $order->stripe_checkout_session_id,
                    'stripe_payment_intent_id' => is_string($session->payment_intent ?? null)
                        ? $session->payment_intent
                        : $order->stripe_payment_intent_id,
                ]);

                return true;
            });

            if ($shouldDispatchEvents) {
                event(new OrderPlaced((int) $orderId, $cartSessionId !== '' ? $cartSessionId : null));
                event(new OrderReadyForShipment((int) $orderId));
            }
        }

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            DB::transaction(function () use ($orderId, $stockService) {
                $order = Order::with(['items.product', 'items.variant'])
                    ->whereKey($orderId)
                    ->where('payment_method', 'stripe')
                    ->where('payment_status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    return;
                }

                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);

                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $stockService->releaseVariant($item->variant, (int) $item->quantity);
                    } elseif ($item->product) {
                        $stockService->release($item->product, (int) $item->quantity);
                    }
                }
            });
        }

        if ($event->type === 'charge.succeeded') {
            $charge = $event->data->object;
            $paymentIntentId = $charge->payment_intent ?? null;
            $orderId = $charge->metadata->order_id ?? null;

            if ($paymentIntentId || $orderId) {
                Order::query()
                    ->when($paymentIntentId, fn ($query) => $query->where('stripe_payment_intent_id', $paymentIntentId))
                    ->when(! $paymentIntentId && $orderId, fn ($query) => $query->whereKey($orderId))
                    ->update([
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'stripe_charge_id' => $charge->id ?? null,
                    ]);
            }
        }

        if ($event->type === 'charge.refunded') {
            $charge = $event->data->object;
            $paymentIntentId = $charge->payment_intent ?? null;
            $orderId = $charge->metadata->order_id ?? null;
            $amountRefunded = ((int) ($charge->amount_refunded ?? 0)) / 100;

            $order = Order::query()
                ->when($paymentIntentId, fn ($query) => $query->where('stripe_payment_intent_id', $paymentIntentId))
                ->when(! $paymentIntentId && $orderId, fn ($query) => $query->whereKey($orderId))
                ->first();

            if ($order && $amountRefunded > (float) $order->refunded_amount) {
                app(StripeRefundService::class)->applyRefund(
                    $order,
                    null,
                    $amountRefunded - (float) $order->refunded_amount,
                );
            }
        }

        if ($event->type === 'refund.updated') {
            $refund = $event->data->object;
            $orderId = $refund->metadata->order_id ?? null;

            if (($refund->status ?? null) === 'succeeded' && (! empty($refund->payment_intent) || $orderId)) {
                $order = Order::query()
                    ->when(! empty($refund->payment_intent), fn ($query) => $query->where('stripe_payment_intent_id', $refund->payment_intent))
                    ->when(empty($refund->payment_intent) && $orderId, fn ($query) => $query->whereKey($orderId))
                    ->first();

                if ($order && $order->stripe_refund_id !== ($refund->id ?? null)) {
                    app(StripeRefundService::class)->applyRefund(
                        $order,
                        $refund->id ?? null,
                        ((int) ($refund->amount ?? 0)) / 100,
                    );
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
