<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Listeners\ClearCartAfterOrder;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearCartAfterOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_clears_authenticated_user_cart_after_order_is_placed(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();
        $cart = Cart::create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10,
            'total' => 20,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'shipping_address' => 'Test address',
            'shipping_city' => 'Sofia',
            'shipping_method' => 'address',
            'status' => 'paid',
            'subtotal' => 20,
            'shipping_price' => 0,
            'total' => 20,
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
        ]);

        (new ClearCartAfterOrder())->handle(new OrderPlaced($order->id));

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_it_clears_guest_cart_after_order_is_placed(): void
    {
        $sessionId = 'frontend-session-id';
        $product = $this->createProduct();
        $cart = Cart::create(['session_id' => $sessionId]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10,
            'total' => 10,
        ]);

        $order = Order::create([
            'customer_name' => 'Guest Customer',
            'customer_email' => 'guest@example.com',
            'shipping_address' => 'Test address',
            'shipping_city' => 'Sofia',
            'shipping_method' => 'address',
            'status' => 'paid',
            'subtotal' => 10,
            'shipping_price' => 0,
            'total' => 10,
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
        ]);

        (new ClearCartAfterOrder())->handle(new OrderPlaced($order->id, $sessionId));

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'Test Product',
            'price' => 10,
            'sale_price' => null,
            'quantity' => 50,
            'stock' => true,
        ]);
    }
}
