<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Econt\EcontCityResolverService;
use App\Services\Econt\EcontPayloadMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SenderOfficeResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbound_payload_resolves_sender_office_name_to_code(): void
    {
        config([
            'services.econt.sender.name' => 'Eksait',
            'services.econt.sender.phone' => '+359888111222',
            'services.econt.sender.city' => 'Шумен',
            'services.econt.sender.office_code' => 'Осми март',
        ]);

        $resolver = Mockery::mock(EcontCityResolverService::class);
        $resolver->shouldReceive('getOfficeByName')
            ->once()
            ->with('Шумен', 'Осми март')
            ->andReturn(['code' => '9707']);

        $this->app->instance(EcontCityResolverService::class, $resolver);

        $order = new Order([
            'customer_name' => 'Ivan Ivanov',
            'customer_phone' => '0888123456',
            'shipping_city' => 'Burgas',
            'shipping_postcode' => '8000',
            'shipping_address' => 'Test street 1',
            'subtotal' => 120,
        ]);

        $shipment = new Shipment([
            'direction' => 'outbound',
            'delivery_type' => 'office',
            'office_code' => '8009',
            'weight' => 1.250,
            'pack_count' => 1,
            'declared_value' => 120,
            'cash_on_delivery' => 0,
        ]);
        $shipment->setRelation('order', $order);

        $payload = app(EcontPayloadMapper::class)->map($shipment);

        $this->assertSame('9707', data_get($payload, 'senderOfficeCode'));
    }

    public function test_return_payload_resolves_store_office_name_to_code(): void
    {
        config([
            'services.econt.sender.name' => 'Eksait',
            'services.econt.sender.phone' => '+359888111222',
            'services.econt.sender.city' => 'Шумен',
            'services.econt.sender.office_code' => 'Осми март',
        ]);

        $resolver = Mockery::mock(EcontCityResolverService::class);
        $resolver->shouldReceive('getOfficeByName')
            ->once()
            ->with('Шумен', 'Осми март')
            ->andReturn(['code' => '9707']);

        $this->app->instance(EcontCityResolverService::class, $resolver);

        $order = new Order([
            'customer_name' => 'Ivan Ivanov',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '0888123456',
            'shipping_city' => 'Sofia',
            'shipping_postcode' => '1000',
            'shipping_address' => 'Test street 1',
            'subtotal' => 120,
        ]);

        $shipment = new Shipment([
            'direction' => 'return',
            'delivery_type' => 'office',
            'office_code' => '5500',
            'weight' => 1.250,
            'pack_count' => 1,
            'declared_value' => 120,
            'cash_on_delivery' => 0,
        ]);
        $shipment->setRelation('order', $order);

        $payload = app(EcontPayloadMapper::class)->map($shipment);

        $this->assertSame('9707', data_get($payload, 'receiverOfficeCode'));
    }
}
