<?php

namespace Tests\Feature;

use App\Http\Resources\ProductAPIResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductVariantStockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_stock_is_derived_from_quantity(): void
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'price' => 10,
            'stock' => false,
            'quantity' => 5,
        ]);

        $this->assertTrue($product->stock);
        $this->assertSame(5, (int) $product->quantity);

        $product->update([
            'stock' => true,
            'quantity' => 0,
        ]);

        $this->assertFalse($product->refresh()->stock);
        $this->assertSame(0, (int) $product->quantity);
    }

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

    public function test_variant_stock_is_forced_true_when_quantity_is_positive(): void
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
            'stock' => false,
            'quantity' => 3,
        ]);

        $variant->refresh();

        $this->assertTrue($variant->stock);
        $this->assertSame(3, $variant->quantity);
    }

    public function test_parent_product_quantity_is_not_calculated_from_variants(): void
    {
        $product = Product::create([
            'name' => 'Variant Parent',
            'price' => 10,
            'stock' => true,
            'quantity' => 10,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'price' => 12,
            'stock' => true,
            'quantity' => 3,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'XL',
            'price' => 14,
            'stock' => true,
            'quantity' => 4,
        ]);

        $product->refresh();

        $this->assertSame(0, (int) $product->quantity);
        $this->assertTrue($product->stock);

        $payload = (new ProductAPIResource($product->load('variants')))->toArray(Request::create('/'));

        $this->assertSame(0, $payload['quantity']);
        $this->assertTrue($payload['stock']);
        $this->assertSame([3, 4], collect($payload['variants'])->pluck('quantity')->all());
    }
}
