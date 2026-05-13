<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\OrderReadyForShipment;
use App\Models\Order;
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
                $order = Order::with('items.product')
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
                    if ($item->product) {
                        $stockService->release($item->product, (int) $item->quantity);
                    }
                }
            });
        }

        return response()->json(['received' => true]);
    }
}
