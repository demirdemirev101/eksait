<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\StripeRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeRefundServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_refund_can_preserve_return_requested_status(): void
    {
        config(['services.stripe.sk' => 'sk_test_dummy']);

        $order = Order::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'status' => OrderStatus::IN_TRANSIT->value,
            'subtotal' => 25,
            'shipping_price' => 0,
            'total' => 25,
            'payment_method' => 'stripe',
            'payment_status' => PaymentStatus::PAID->value,
            'stripe_payment_intent_id' => 'pi_test',
        ]);

        app(StripeRefundService::class)->applyRefund(
            $order,
            're_test',
            25,
            OrderStatus::RETURN_REQUESTED,
        );

        $order->refresh();

        $this->assertSame(PaymentStatus::REFUNDED->value, $order->payment_status);
        $this->assertSame(OrderStatus::RETURN_REQUESTED->value, $order->status);
        $this->assertSame('25.00', (string) $order->refunded_amount);
        $this->assertSame('re_test', $order->stripe_refund_id);
    }
}
