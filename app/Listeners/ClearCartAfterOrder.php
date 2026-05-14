<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ClearCartAfterOrder
{
    public function handle(OrderPlaced $event): void
    {
        $order = Order::query()->find($event->orderId);

        if (! $order) {
            return;
        }

        DB::transaction(function () use ($order, $event) {
            if ($order->user_id) {
                Cart::query()
                    ->where('user_id', $order->user_id)
                    ->each(fn (Cart $cart) => $cart->items()->delete());
            }

            if ($event->sessionId) {
                Cart::query()
                    ->where('session_id', $event->sessionId)
                    ->each(fn (Cart $cart) => $cart->items()->delete());
            }
        });
    }
}
