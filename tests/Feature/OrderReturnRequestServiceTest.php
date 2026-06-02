<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\ShipmentCreated;
use App\Mail\OrderReturnRequestedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Services\OrderReturnRequestService;
use App\Services\StripeRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Mockery;
use RuntimeException;
use Stripe\Refund;
use Tests\TestCase;

class OrderReturnRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_return_request_creates_return_shipment_and_refunds_remaining_amount(): void
    {
        Event::fake([ShipmentCreated::class]);
        Mail::fake();

        $order = $this->createReturnableOrder([
            'payment_method' => 'stripe',
            'payment_status' => PaymentStatus::PAID->value,
            'stripe_payment_intent_id' => 'pi_test',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'outbound',
            'carrier_shipment_id' => 'OUT-1',
            'tracking_number' => 'OUT-1',
            'status' => 'in_transit',
            'delivery_type' => 'address',
            'weight' => 0.500,
            'pack_count' => 1,
            'declared_value' => 25,
            'cash_on_delivery' => 0,
        ]);

        $stripeRefundService = Mockery::mock(StripeRefundService::class);
        $stripeRefundService
            ->shouldReceive('refund')
            ->once()
            ->with(
                Mockery::on(fn (Order $refundedOrder) => $refundedOrder->id === $order->id),
                25.0,
                OrderStatus::RETURN_REQUESTED,
            )
            ->andReturnUsing(function (Order $refundedOrder, float $amount, OrderStatus $status): Refund {
                $refundedOrder->updateQuietly([
                    'stripe_refund_id' => 're_test',
                    'refunded_amount' => $amount,
                    'refunded_at' => now(),
                    'payment_status' => PaymentStatus::REFUNDED->value,
                    'status' => $status->value,
                ]);

                return Refund::constructFrom(['id' => 're_test']);
            });
        $this->app->instance(StripeRefundService::class, $stripeRefundService);

        $returnShipment = app(OrderReturnRequestService::class)->requestReturn($order);

        $order->refresh();

        $this->assertSame(OrderStatus::RETURN_REQUESTED->value, $order->status);
        $this->assertSame(PaymentStatus::REFUNDED->value, $order->payment_status);
        $this->assertSame('25.00', (string) $order->refunded_amount);
        $this->assertSame('return', $returnShipment->direction);
        $this->assertSame('created', $returnShipment->status);
        $this->assertSame('0.00', (string) $returnShipment->cash_on_delivery);

        Event::assertDispatched(
            ShipmentCreated::class,
            fn (ShipmentCreated $event) => $event->shipmentId === $returnShipment->id
                && $event->orderId === $order->id,
        );
        Mail::assertSent(OrderReturnRequestedMail::class);
    }

    public function test_stripe_return_request_keeps_order_returnable_when_refund_fails(): void
    {
        Event::fake([ShipmentCreated::class]);
        Mail::fake();

        $order = $this->createReturnableOrder([
            'payment_method' => 'stripe',
            'payment_status' => PaymentStatus::PAID->value,
            'stripe_payment_intent_id' => 'pi_test',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'outbound',
            'carrier_shipment_id' => 'OUT-1',
            'tracking_number' => 'OUT-1',
            'status' => 'in_transit',
            'delivery_type' => 'address',
            'weight' => 0.500,
            'pack_count' => 1,
            'declared_value' => 25,
            'cash_on_delivery' => 0,
        ]);

        $stripeRefundService = Mockery::mock(StripeRefundService::class);
        $stripeRefundService
            ->shouldReceive('refund')
            ->once()
            ->andThrow(new RuntimeException('Stripe unavailable'));
        $this->app->instance(StripeRefundService::class, $stripeRefundService);

        try {
            app(OrderReturnRequestService::class)->requestReturn($order);
            $this->fail('Expected return request to fail when Stripe refund fails.');
        } catch (RuntimeException $e) {
            $this->assertSame('Stripe unavailable', $e->getMessage());
        }

        $order->refresh();
        $returnShipment = $order->shipments()->where('direction', 'return')->firstOrFail();

        $this->assertSame(OrderStatus::IN_TRANSIT->value, $order->status);
        $this->assertSame(PaymentStatus::PAID->value, $order->payment_status);
        $this->assertSame('error', $returnShipment->status);
        $this->assertStringContainsString('Stripe unavailable', $returnShipment->error_message);

        Event::assertNotDispatched(ShipmentCreated::class);
        Mail::assertNothingSent();
    }

    private function createReturnableOrder(array $overrides = []): Order
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
            'weight' => 0.250,
        ]);

        $order = Order::create(array_merge([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'shipping_method' => 'address',
            'status' => OrderStatus::IN_TRANSIT->value,
            'subtotal' => 25,
            'shipping_price' => 0,
            'total' => 25,
            'payment_method' => 'cod',
            'payment_status' => PaymentStatus::PENDING->value,
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Test Product - M',
            'price' => 12.50,
            'quantity' => 2,
            'total' => 25,
        ]);

        return $order;
    }
}
