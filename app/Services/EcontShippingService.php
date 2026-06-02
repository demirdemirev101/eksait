<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Econt\EcontPayloadMapper;
use App\Services\Econt\EcontService;
use App\Support\EcontDeliveryTypeResolver;
use RuntimeException;

class EcontShippingService
{
    public function __construct(
        private EcontService $econtService,
        private EcontPayloadMapper $econtPayloadMapper,
        private WeightCalculatorService $weightCalculator,
    ) {}

    public function estimate(Order $order): float
    {
        $shipment = $this->buildShipment($order);
        $payload = $this->econtPayloadMapper->map($shipment);
        $response = $this->createLabelWithFallback($payload, 'calculate');
        $price = $response['label']['totalPrice'] ?? null;

        return $price !== null ? (float) $price : 0.0;
    }

    public function createShipmentRecord(Order $order): Shipment
    {
        $shipment = $this->buildShipment($order, $order->shipping_price);

        return $order->shipments()->create([
            'carrier' => 'econt',
            'direction' => 'outbound',
            'weight' => $shipment->weight,
            'width' => $shipment->width,
            'height' => $shipment->height,
            'length' => $shipment->length,
            'pack_count' => $shipment->pack_count,
            'delivery_type' => $shipment->delivery_type,
            'office_code' => $shipment->office_code,
            'declared_value' => $shipment->declared_value,
            'cash_on_delivery' => $shipment->cash_on_delivery,
            'shipping_price_estimated' => $shipment->shipping_price_estimated,
            'status' => 'created',
        ]);
    }

    public function createReturnShipmentRecord(Order $order): Shipment
    {
        $order->load(['returnShipment', 'shipment', 'items.product', 'items.variant']);

        if ($order->returnShipment && ! in_array($order->returnShipment->status, ['cancelled', 'error'], true)) {
            throw new RuntimeException('A return shipment already exists for this order.');
        }

        $shipment = $this->buildReturnShipment($order);

        return $order->shipments()->create([
            'carrier' => 'econt',
            'direction' => 'return',
            'weight' => $shipment->weight,
            'width' => $shipment->width,
            'height' => $shipment->height,
            'length' => $shipment->length,
            'pack_count' => $shipment->pack_count,
            'delivery_type' => $shipment->delivery_type,
            'office_code' => $shipment->office_code,
            'declared_value' => $shipment->declared_value,
            'cash_on_delivery' => $shipment->cash_on_delivery,
            'shipping_price_estimated' => $shipment->shipping_price_estimated,
            'status' => 'created',
        ]);
    }

    public function send(Shipment $shipment): array
    {
        $shipment->loadMissing('order');

        $payload = $this->econtPayloadMapper->map($shipment);
        $shipment->update([
            'carrier_payload' => $payload,
        ]);

        return $this->createLabelWithFallback($payload, 'create', $shipment);
    }

    public function resolveDeliveryType(Order $order): string
    {
        return EcontDeliveryTypeResolver::resolve(
            $order->shipping_method ?? null,
            $order->econt_office_code ?? null,
            $order->econt_office_is_aps,
            $order->econt_office_name ?? null,
            $order->econt_office_address ?? null,
        );
    }

    private function buildShipment(Order $order, ?float $estimatedShippingPrice = null): Shipment
    {
        $order->loadMissing(['items.product', 'items.variant']);

        $deliveryType = $this->resolveDeliveryType($order);
        $shipment = new Shipment();
        $shipment->setRelation('order', $order);
        $shipment->weight = $this->weightCalculator->forOrder($order);
        $shipment->width = $this->weightCalculator->maxDimension($order, 'width');
        $shipment->height = $this->weightCalculator->maxDimension($order, 'height');
        $shipment->length = $this->weightCalculator->maxDimension($order, 'length');
        $shipment->pack_count = $deliveryType === 'apm' ? 1 : $this->calculatePackCount($order);
        $shipment->delivery_type = $deliveryType;
        $shipment->office_code = $deliveryType !== 'address' ? $order->econt_office_code : null;
        $shipment->declared_value = $deliveryType === 'apm' ? 0.0 : (float) ($order->subtotal ?? 0);
        $shipment->cash_on_delivery = $order->payment_method === 'cod'
            ? (float) ($order->subtotal ?? 0)
            : 0.0;
        $shipment->shipping_price_estimated = $estimatedShippingPrice;

        return $shipment;
    }

    private function buildReturnShipment(Order $order): Shipment
    {
        $order->loadMissing(['items.product', 'items.variant', 'shipment']);

        $sourceShipment = $order->shipment;
        $deliveryType = $sourceShipment?->delivery_type ?? $this->resolveDeliveryType($order);
        $shipment = new Shipment();
        $shipment->setRelation('order', $order);
        $shipment->direction = 'return';
        $shipment->weight = (float) ($sourceShipment?->weight ?? $this->weightCalculator->forOrder($order));
        $shipment->width = $sourceShipment?->width ?? $this->weightCalculator->maxDimension($order, 'width');
        $shipment->height = $sourceShipment?->height ?? $this->weightCalculator->maxDimension($order, 'height');
        $shipment->length = $sourceShipment?->length ?? $this->weightCalculator->maxDimension($order, 'length');
        $shipment->pack_count = max(1, (int) ($sourceShipment?->pack_count ?? $this->calculatePackCount($order)));
        $shipment->delivery_type = $deliveryType;
        $shipment->office_code = $deliveryType !== 'address'
            ? ($sourceShipment?->office_code ?? $order->econt_office_code)
            : null;
        $shipment->declared_value = $deliveryType === 'apm'
            ? 0.0
            : (float) ($sourceShipment?->declared_value ?? $order->subtotal ?? 0);
        $shipment->cash_on_delivery = 0.0;
        $shipment->shipping_price_estimated = null;

        return $shipment;
    }

    private function createLabelWithFallback(array $payload, string $mode, ?Shipment $shipment = null): array
    {
        try {
            return $this->econtService->createLabel($payload, $mode);
        } catch (RuntimeException $e) {
            if (! $this->shouldRetryForAutomaticPostStation($e, $payload)) {
                throw $e;
            }

            $payload = $this->normalizeAutomaticPostStationPayload($payload);

            if ($shipment) {
                $shipment->update([
                    'carrier_payload' => $payload,
                ]);
            }

            return $this->econtService->createLabel($payload, $mode);
        }
    }

    private function normalizeAutomaticPostStationPayload(array $payload): array
    {
        unset($payload['services']['declaredValueAmount']);
        $payload['packCount'] = 1;

        if (isset($payload['services']) && empty($payload['services'])) {
            unset($payload['services']);
        }

        return $payload;
    }

    private function shouldRetryForAutomaticPostStation(RuntimeException $e, array $payload): bool
    {
        if (
            ! isset($payload['services']['declaredValueAmount'])
            && (($payload['packCount'] ?? 1) === 1)
        ) {
            return false;
        }

        $message = mb_strtolower($e->getMessage());

        return str_contains($message, 'еконтомат')
            || str_contains($message, 'автоматична пощенска станция')
            || str_contains($message, 'automatic postal station')
            || str_contains($message, 'declared value');
    }

    private function calculatePackCount(Order $order): int
    {
        $itemsCount = $order->items->sum('quantity');

        return max(1, (int) ceil($itemsCount / 5));
    }
}
