<?php

namespace App\Services;

use App\Models\Order;

class WeightCalculatorService
{
    public function forOrder(Order $order): float
    {
        $order->loadMissing('items.variant');

        $weight = $order->items->sum(function ($item) {
            // Ако има variant - чети от него, иначе от продукта
            $productWeight = (float) ($item->variant?->weight ?? $item->product?->weight ?? 0);
            return $productWeight * $item->quantity;
        });

        return max($weight, 0.100);
    }

    public function maxDimension(Order $order, string $dimension): ?float
    {
        $order->loadMissing(['items.variant', 'items.product']);

        $max = $order->items
            ->map(fn($item) => (float) (
                $item->variant?->{$dimension}
                ?? $item->product?->{$dimension}
                ?? 0
            ))
            ->max();

        return $max > 0 ? $max : null;
    }
}
