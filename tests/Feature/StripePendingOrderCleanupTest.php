<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripePendingOrderCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_pending_stripe_orders_and_restores_stock(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 12.50,
            'sale_price' => null,
            'quantity' => 0,
            'stock' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'price' => 12.50,
            'sale_price' => null,
            'quantity' => 3,
            'stock' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'shipping_method' => 'address',
            'status' => 'pending',
            'subtotal' => 25,
            'shipping_price' => 0,
            'total' => 25,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test Product - M',
            'price' => 12.50,
            'quantity' => 2,
            'total' => 25,
        ]);

        app(OrderService::class)->deleteOrderWithItems($order);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);

        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'quantity' => 5,
            'stock' => 1,
        ]);
    }
}
