<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Econt\EcontService;
use App\Services\Shipment\ShipmentTrackingSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReturnShipmentTrackingSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_shipment_delivery_marks_order_as_returned(): void
    {
        $order = Order::create([
            'customer_name' => 'Ivan Ivanov',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '0888123456',
            'shipping_address' => 'Test street 1',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'status' => OrderStatus::RETURN_REQUESTED->value,
            'subtotal' => 120,
            'shipping_price' => 0,
            'total' => 120,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'outbound',
            'carrier_shipment_id' => 'OUT-1',
            'tracking_number' => 'OUT-1',
            'status' => 'delivered',
        ]);

        $returnShipment = Shipment::create([
            'order_id' => $order->id,
            'carrier' => 'econt',
            'direction' => 'return',
            'carrier_shipment_id' => 'RET-1',
            'tracking_number' => 'RET-1',
            'status' => 'confirmed',
        ]);

        config(['services.econt.enabled' => true]);

        $econtService = Mockery::mock(EcontService::class);
        $econtService
            ->shouldReceive('trackShipment')
            ->once()
            ->with('RET-1')
            ->andReturn([
                'shipmentStatuses' => [[
                    'status' => [
                        'shortDeliveryStatusEn' => 'Delivered',
                        'deliveryTime' => now()->timestamp * 1000,
                    ],
                ]],
            ]);

        $this->app->instance(EcontService::class, $econtService);

        $changed = $this->app->make(ShipmentTrackingSyncService::class)
            ->syncShipmentTracking($order);

        $this->assertTrue($changed);
        $this->assertSame('delivered', $returnShipment->fresh()->status);
        $this->assertSame(OrderStatus::RETURNED->value, $order->fresh()->status);
    }
}
