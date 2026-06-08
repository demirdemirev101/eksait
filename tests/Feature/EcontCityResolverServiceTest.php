<?php

namespace Tests\Feature;

use App\Services\Econt\EcontCityResolverService;
use App\Services\Econt\EcontService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class EcontCityResolverServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();
    }

    public function test_get_offices_prefers_city_specific_lookup(): void
    {
        $econtService = Mockery::mock(EcontService::class);
        $econtService->shouldReceive('getCities')
            ->once()
            ->with('Шумен')
            ->andReturn([
                [
                    'id' => 47,
                    'name' => 'Шумен',
                    'regionName' => 'Шумен',
                ],
            ]);

        $econtService->shouldReceive('getOffices')
            ->once()
            ->with(47)
            ->andReturn([
                [
                    'code' => '9707',
                    'name' => 'Шумен Осми март',
                    'address' => [
                        'city' => ['name' => 'Шумен'],
                    ],
                ],
            ]);

        $econtService->shouldNotReceive('getOffices')
            ->withNoArgs();

        $resolver = new EcontCityResolverService($econtService);

        $offices = $resolver->getOffices('Шумен');

        $this->assertCount(1, $offices);
        $this->assertSame('9707', $offices[0]['code']);
    }

    public function test_empty_office_results_are_not_cached_forever(): void
    {
        $econtService = Mockery::mock(EcontService::class);
        $econtService->shouldReceive('getCities')
            ->once()
            ->with('Шумен')
            ->andReturn([
                [
                    'id' => 47,
                    'name' => 'Шумен',
                    'regionName' => 'Шумен',
                ],
            ]);

        $econtService->shouldReceive('getOffices')
            ->twice()
            ->with(47)
            ->andReturn([]);

        $econtService->shouldReceive('getOffices')
            ->twice()
            ->withNoArgs()
            ->andReturn([]);

        $resolver = new EcontCityResolverService($econtService);

        $this->assertSame([], $resolver->getOffices('Шумен'));
        $this->assertSame([], $resolver->getOffices('Шумен'));
    }
}
