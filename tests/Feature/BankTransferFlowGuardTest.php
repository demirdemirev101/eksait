<?php

namespace Tests\Feature;

use App\Jobs\CalculateBankTransferShippingJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Policies\ConfirmBankTransferPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class BankTransferFlowGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_transfer_cannot_be_confirmed_before_payment_instructions_email_is_sent(): void
    {
        $order = Order::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_method' => 'address',
            'status' => 'pending',
            'subtotal' => 25,
            'shipping_price' => 0,
            'total' => 25,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $policy = app(ConfirmBankTransferPolicy::class);

        $this->assertFalse($policy->canConfirmBankTransfer($order));

        $order->forceFill([
            'order_confirmation_sent_at' => now(),
        ])->saveQuietly();

        $this->assertTrue($policy->canConfirmBankTransfer($order->fresh()));
    }

    public function test_bank_transfer_confirmation_timestamp_is_not_set_when_email_sending_fails(): void
    {
        config([
            'services.econt.enabled' => false,
        ]);

        Setting::create([
            'delivery_enabled' => false,
            'stripe_enabled' => false,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 25,
            'quantity' => 5,
            'description' => 'Test description',
            'stock' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_method' => 'address',
            'status' => 'pending',
            'subtotal' => 0,
            'shipping_price' => 0,
            'total' => 0,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 25,
            'quantity' => 1,
            'total' => 25,
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with('john@example.com')
            ->andReturn(new class
            {
                public function send(OrderConfirmationMail $mailable): void
                {
                    throw new RuntimeException('Mail transport failed.');
                }
            });

        try {
            (new CalculateBankTransferShippingJob($order->id))->handle(app('App\Services\SettingsService'));
            $this->fail('Expected the mail transport failure to bubble up.');
        } catch (RuntimeException $e) {
            $this->assertSame('Mail transport failed.', $e->getMessage());
        }

        $this->assertNull($order->fresh()->order_confirmation_sent_at);
    }

    public function test_bank_transfer_confirmation_timestamp_is_set_after_successful_email_send(): void
    {
        config([
            'services.econt.enabled' => false,
        ]);

        Mail::fake();

        Setting::create([
            'delivery_enabled' => false,
            'stripe_enabled' => false,
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 25,
            'quantity' => 5,
            'description' => 'Test description',
            'stock' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_method' => 'address',
            'status' => 'pending',
            'subtotal' => 0,
            'shipping_price' => 0,
            'total' => 0,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 25,
            'quantity' => 1,
            'total' => 25,
        ]);

        (new CalculateBankTransferShippingJob($order->id))->handle(app('App\Services\SettingsService'));

        Mail::assertSent(OrderConfirmationMail::class);
        $this->assertNotNull($order->fresh()->order_confirmation_sent_at);
    }
}
