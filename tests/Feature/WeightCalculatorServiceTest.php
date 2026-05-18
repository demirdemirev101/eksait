<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\WeightCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_variant_dimensions_when_variant_exists(): void
    {
        $product = Product::create([
            'name' => 'Base Product',
            'price' => 10,
            'stock' => true,
            'quantity' => 5,
            'weight' => 1.20,
            'width' => 11,
            'height' => 12,
            'length' => 13,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'price' => 12,
            'stock' => true,
            'quantity' => 3,
            'weight' => 2.50,
            'width' => 21,
            'height' => 22,
            'length' => 23,
        ]);

        $order = Order::create([
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'shipping_address' => 'Test Address',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'subtotal' => 24,
            'shipping_price' => 5,
            'total' => 29,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'shipping_method' => 'address',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Base Product - M',
            'price' => 12,
            'quantity' => 2,
            'total' => 24,
        ]);

        $service = app(WeightCalculatorService::class);

        $this->assertSame(21.0, $service->maxDimension($order, 'width'));
        $this->assertSame(22.0, $service->maxDimension($order, 'height'));
        $this->assertSame(23.0, $service->maxDimension($order, 'length'));
    }

    public function test_it_falls_back_to_product_dimensions_when_no_variant_exists(): void
    {
        $product = Product::create([
            'name' => 'Simple Product',
            'price' => 10,
            'stock' => true,
            'quantity' => 5,
            'weight' => 1.10,
            'width' => 31,
            'height' => 32,
            'length' => 33,
        ]);

        $order = Order::create([
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'shipping_address' => 'Test Address',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'subtotal' => 10,
            'shipping_price' => 5,
            'total' => 15,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'shipping_method' => 'address',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Simple Product',
            'price' => 10,
            'quantity' => 1,
            'total' => 10,
        ]);

        $service = app(WeightCalculatorService::class);

        $this->assertSame(31.0, $service->maxDimension($order, 'width'));
        $this->assertSame(32.0, $service->maxDimension($order, 'height'));
        $this->assertSame(33.0, $service->maxDimension($order, 'length'));
    }
}
