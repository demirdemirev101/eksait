<?php

namespace Tests\Feature;

use App\Services\Econt\EcontCityResolverService;
use Mockery;
use Tests\TestCase;

class CheckoutOfficeLocalizationTest extends TestCase
{
    public function test_econt_offices_endpoint_returns_english_office_fields_when_requested(): void
    {
        $resolver = Mockery::mock(EcontCityResolverService::class);
        $resolver->shouldReceive('getOffices')
            ->once()
            ->with('Sofia')
            ->andReturn([
                [
                    'code' => '9707',
                    'name' => 'София Резбарска',
                    'nameEn' => 'Sofia Rezbarska',
                    'address' => [
                        'city' => [
                            'name' => 'София',
                            'nameEn' => 'Sofia',
                        ],
                        'street' => 'Резбарска',
                        'num' => '12',
                    ],
                ],
            ]);

        $this->app->instance(EcontCityResolverService::class, $resolver);

        $response = $this->getJson('/api/checkout/econt-offices?city=Sofia&lang=en');

        $response
            ->assertOk()
            ->assertJsonPath('offices.0.code', '9707')
            ->assertJsonPath('offices.0.name', 'Sofia Rezbarska')
            ->assertJsonPath('offices.0.city', 'Sofia')
            ->assertJsonPath('offices.0.address', 'Sofia Rezbarska No. 12');
    }
}
