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
            ->with([
                'items.variant',
                'items.product.primaryImage',
                'items.product.images',
            ])
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
                    'shipping_city' => $order->shipping_city,
                    'shipping_address' => $order->shipping_address,
                    'shipping_postcode' => $order->shipping_postcode,
                    'econt_office_code' => $order->econt_office_code,
                    'econt_office_name' => $order->econt_office_name,
                    'econt_office_address' => $order->econt_office_address,
                    'econt_office_is_aps' => (bool) $order->econt_office_is_aps,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'product_name' => $item->product_name,
                        'variant' => $item->variant ? [
                            'id' => $item->variant->id,
                            'size' => $item->variant->size,
                        ] : null,
                        'product_image_url' => $item->product?->primaryImage?->url
                            ?? $item->product?->images?->first()?->url,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'slug' => $item->product->slug,
                            'image' => $item->product->primaryImage?->url
                                ?? $item->product->images?->first()?->url,
                            'images' => $item->product->images->map(fn ($image) => [
                                'id' => $image->id,
                                'url' => $image->url,
                                'is_primary' => (bool) $image->is_primary,
                                'sort_order' => $image->sort_order,
                            ])->values(),
                        ] : null,
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
