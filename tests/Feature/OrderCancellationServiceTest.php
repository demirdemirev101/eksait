<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderCancelledMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Services\Econt\EcontService;
use App\Services\OrderCancellationService;
use App\Services\OrderService;
use App\Services\StripeRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Stripe\Refund;
use Tests\TestCase;

class OrderCancellationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_stripe_cancellation_deletes_econt_label_and_releases_stock_once(): void
    {
        Mail::fake();
        config(['services.econt.enabled' => true]);

        [$order, $variant] = $this->createCancellableOrder([
            'payment_method' => 'cod',
            'payment_status' => PaymentStatus::PENDING->value,
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'outbound',
            'carrier_shipment_id' => '123456',
            'tracking_number' => '123456',
            'status' => 'confirmed',
        ]);

        $econtService = Mockery::mock(EcontService::class);
        $econtService
            ->shouldReceive('deleteLabels')
            ->once()
            ->with(['123456'])
            ->andReturn(['ok' => true]);
        $this->app->instance(EcontService::class, $econtService);

        app(OrderCancellationService::class)->cancel($order);

        $order->refresh();

        $this->assertSame(OrderStatus::CANCELLED->value, $order->status);
        $this->assertNotNull($order->stock_released_at);
        $this->assertSame(5, $variant->fresh()->quantity);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'carrier_shipment_id' => null,
            'tracking_number' => null,
        ]);
        Mail::assertSent(OrderCancelledMail::class);

        app(OrderService::class)->deleteOrderWithItems($order);

        $this->assertSame(5, $variant->fresh()->quantity);
    }

    public function test_stripe_cancellation_deletes_econt_label_refunds_remaining_amount_and_cancels_order(): void
    {
        Mail::fake();
        config(['services.econt.enabled' => true]);

        [$order, $variant] = $this->createCancellableOrder([
            'payment_method' => 'stripe',
            'payment_status' => PaymentStatus::PAID->value,
            'stripe_payment_intent_id' => 'pi_test',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'outbound',
            'carrier_shipment_id' => '654321',
            'tracking_number' => '654321',
            'status' => 'confirmed',
        ]);

        $econtService = Mockery::mock(EcontService::class);
        $econtService
            ->shouldReceive('deleteLabels')
            ->once()
            ->with(['654321'])
            ->andReturn(['ok' => true]);
        $this->app->instance(EcontService::class, $econtService);

        $stripeRefundService = Mockery::mock(StripeRefundService::class);
        $stripeRefundService
            ->shouldReceive('refund')
            ->once()
            ->with(Mockery::on(fn (Order $refundedOrder) => $refundedOrder->id === $order->id), 25.0)
            ->andReturnUsing(function (Order $refundedOrder): Refund {
                $refundedOrder->updateQuietly([
                    'stripe_refund_id' => 're_test',
                    'refunded_amount' => 25,
                    'refunded_at' => now(),
                    'payment_status' => PaymentStatus::REFUNDED->value,
                    'status' => OrderStatus::RETURNED->value,
                ]);

                return Refund::constructFrom(['id' => 're_test']);
            });
        $this->app->instance(StripeRefundService::class, $stripeRefundService);

        app(OrderCancellationService::class)->cancel($order);

        $order->refresh();

        $this->assertSame(OrderStatus::CANCELLED->value, $order->status);
        $this->assertSame(PaymentStatus::REFUNDED->value, $order->payment_status);
        $this->assertSame('25.00', (string) $order->refunded_amount);
        $this->assertNotNull($order->stock_released_at);
        $this->assertSame(5, $variant->fresh()->quantity);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'status' => 'cancelled',
            'carrier_shipment_id' => null,
            'tracking_number' => null,
        ]);
    }

    private function createCancellableOrder(array $overrides = []): array
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

        $order = Order::create(array_merge([
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'shipping_method' => 'address',
            'status' => OrderStatus::READY_FOR_SHIPMENT->value,
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

        return [$order, $variant];
    }
}
