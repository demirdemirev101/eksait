<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductNameNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_name_is_saved_uppercase(): void
    {
        $product = Product::create([
            'name' => 'дорник исо40',
            'price' => 10,
            'stock' => true,
            'quantity' => 1,
        ]);

        $this->assertSame('ДОРНИК ИСО40', $product->name);
    }

    public function test_product_variant_size_is_saved_uppercase(): void
    {
        $product = Product::create([
            'name' => 'дорник исо40',
            'price' => 10,
            'stock' => true,
            'quantity' => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'за rv на',
            'price' => 10,
            'stock' => true,
            'quantity' => 1,
        ]);

        $this->assertSame('ЗА RV НА', $variant->size);
    }
}
