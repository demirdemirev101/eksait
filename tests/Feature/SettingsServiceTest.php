<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\Setting;
use App\Services\EcontShippingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_checkout_fails_when_econt_shipping_cannot_be_calculated(): void
    {
        config(['services.econt.enabled' => true]);
        Setting::create([
            'delivery_enabled' => true,
            'free_delivery_over' => null,
        ]);

        $econtShippingService = Mockery::mock(EcontShippingService::class);
        $econtShippingService
            ->shouldReceive('estimate')
            ->once()
            ->andReturn(0.0);

        $service = new SettingsService($econtShippingService);

        $order = new Order([
            'subtotal' => 25,
            'payment_method' => 'stripe',
        ]);

        $this->expectException(CheckoutException::class);

        $service->applyTotals($order);
    }

    public function test_cod_shipping_is_deferred_until_the_shipping_job_runs(): void
    {
        config(['services.econt.enabled' => true]);
        Setting::create([
            'delivery_enabled' => true,
            'free_delivery_over' => null,
        ]);

        $econtShippingService = Mockery::mock(EcontShippingService::class);
        $econtShippingService
            ->shouldNotReceive('estimate');

        $service = new SettingsService($econtShippingService);

        $order = new Order([
            'subtotal' => 25,
            'payment_method' => 'cod',
        ]);

        $service->applyTotals($order);

        $this->assertSame(0, $order->shipping_price);
        $this->assertSame(25, $order->total);
    }

    public function test_calculate_shipping_requires_payment_method(): void
    {
        $response = $this->postJson('/api/checkout/calculate-shipping', [
            'shipping_method' => 'address',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payment_method']);
    }

    public function test_calculate_shipping_accepts_english_payment_method_label(): void
    {
        $response = $this->postJson('/api/checkout/calculate-shipping', [
            'shipping_method' => 'address',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'payment_method' => 'Cash on delivery',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['payment_method']);
    }

    public function test_calculate_shipping_accepts_german_payment_method_label(): void
    {
        $response = $this->postJson('/api/checkout/calculate-shipping', [
            'shipping_method' => 'address',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'payment_method' => 'Nachnahme',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['payment_method']);
    }
}
