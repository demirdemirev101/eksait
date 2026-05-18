<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantStockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_stock_is_forced_false_when_quantity_is_zero(): void
    {
        $product = Product::create([
            'name' => 'Variant Parent',
            'price' => 10,
            'stock' => true,
            'quantity' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'price' => 12,
            'stock' => true,
            'quantity' => 0,
        ]);

        $variant->refresh();

        $this->assertFalse($variant->stock);
        $this->assertSame(0, $variant->quantity);
    }
}

