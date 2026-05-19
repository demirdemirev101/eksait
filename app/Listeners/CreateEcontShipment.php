<?php

namespace App\Listeners;

use App\Events\OrderReadyForShipment;
use App\Events\ShipmentCreated;
use App\Models\Order;
use App\Services\EcontShippingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateEcontShipment implements ShouldQueue
{
    public function __construct(
        private EcontShippingService $econtShippingService,
    ) {}

    public function handle(OrderReadyForShipment $event): void
    {
        $order = Order::with('shipment')->findOrFail($event->orderId);

        if ($order->shipment) {
            Log::warning('Shipment already exists', [
                'order_id' => $order->id,
                'shipment_id' => $order->shipment->id,
            ]);

            return;
        }

        $shipment = $this->econtShippingService->createShipmentRecord($order);

        Log::info('Shipment created', [
            'order_id' => $order->id,
            'shipment_id' => $shipment->id,
            'weight' => $shipment->weight,
            'delivery_type' => $shipment->delivery_type,
        ]);

        event(new ShipmentCreated($shipment->id, $order->id));
    }
}
