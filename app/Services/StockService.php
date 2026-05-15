<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Product;

class StockService
{
    /**
     * Reserve stock for a product using an atomic conditional decrement.
     */
    public function reserve(Product $product, int $quantity): void
    {
        if (! $product->stock || (int) $product->quantity <= 0) {
            throw new CheckoutException("Продуктът не е наличен: {$product->name}", 422);
        }

        $affected = Product::where('id', $product->id)
            ->where('stock', true)
            ->where('quantity', '>=', $quantity)
            ->decrement('quantity', $quantity);

        if ($affected === 0) {
            throw new CheckoutException("Недостатъчна наличност за продукт: {$product->name}", 409);
        }

        Product::where('id', $product->id)
            ->where('quantity', '<=', 0)
            ->update([
                'quantity' => 0,
                'stock' => false,
            ]);
    }

    /**
     * Release reserved stock for a product.
     */
    public function release(Product $product, int $quantity): void
    {
        Product::where('id', $product->id)
            ->increment('quantity', $quantity);

        Product::where('id', $product->id)
            ->where('quantity', '>', 0)
            ->update(['stock' => true]);
    }
}
