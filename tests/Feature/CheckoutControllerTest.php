<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_returns_422_when_stripe_is_enabled_but_not_configured(): void
    {
        config([
            'services.econt.enabled' => false,
            'services.stripe.sk' => '',
        ]);

        Setting::create([
            'delivery_enabled' => false,
            'stripe_enabled' => true,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 25,
            'quantity' => 5,
            'description' => 'Test description',
        ]);

        $response = $this->postJson('/api/checkout?lang=en', $this->checkoutPayload($product->id));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Card payments are currently unavailable. Please try again later.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_cleans_up_pending_order_when_stripe_session_creation_fails(): void
    {
        config([
            'services.econt.enabled' => false,
            'services.stripe.sk' => 'sk_test_dummy',
        ]);

        Setting::create([
            'delivery_enabled' => false,
            'stripe_enabled' => true,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 25,
            'quantity' => 5,
            'description' => 'Test description',
        ]);

        $stripeCheckoutService = Mockery::mock(StripeCheckoutService::class);
        $stripeCheckoutService
            ->shouldReceive('createSession')
            ->once()
            ->andThrow(new \RuntimeException('Stripe API request failed.'));

        $this->app->instance(StripeCheckoutService::class, $stripeCheckoutService);

        $response = $this->postJson('/api/checkout?lang=en', $this->checkoutPayload($product->id));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Card payments are currently unavailable. Please try again later.');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    private function checkoutPayload(int $productId): array
    {
        return [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0888123456',
            'shipping_method' => 'address',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'payment_method' => 'stripe',
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 1,
                ],
            ],
        ];
    }
}
