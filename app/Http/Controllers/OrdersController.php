<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request) : JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->id,
                    'status' => $order->status,
                    'status_label' => match ($order->status) {
                        'pending' => 'В очакване',
                        'pending_review' => 'За преглед',
                        'ready_for_shipment' => 'Готова за изпращане',
                        'processing' => 'Обработва се',
                        'shipped' => 'Изпратена',
                        'completed' => 'Завършена',
                        'cancelled' => 'Отказана',
                        'return_requested' => 'Заявено връщане',
                        'returned' => 'Върната',
                        default => $order->status,
                    },
                    'total' => (float) $order->total,
                    'total_amount' => (float) $order->total,
                    'created_at' => $order->created_at,
                    'payment_method' => $order->payment_method,
                    'shipping_method' => $order->shipping_method,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'total' => (float) $item->total,
                    ]),
                ];
            });

        return response()->json([
            'orders' => $orders
        ]);
    }
}
