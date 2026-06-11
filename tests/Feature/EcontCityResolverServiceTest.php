<?php

namespace Tests\Feature;

use App\Services\Econt\EcontCityResolverService;
use App\Services\Econt\EcontService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use RuntimeException;
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

    public function test_get_offices_falls_back_to_all_offices_when_city_lookup_fails(): void
    {
        $econtService = Mockery::mock(EcontService::class);
        $econtService->shouldReceive('getCities')
            ->once()
            ->with('София')
            ->andThrow(new RuntimeException('Econt getCities response exceeded 16777216 bytes.'));

        $econtService->shouldReceive('getOffices')
            ->once()
            ->withNoArgs()
            ->andReturn([
                [
                    'code' => '1000',
                    'name' => 'София Център',
                    'address' => [
                        'city' => ['name' => 'София'],
                    ],
                ],
                [
                    'code' => '2000',
                    'name' => 'Пловдив Център',
                    'address' => [
                        'city' => ['name' => 'Пловдив'],
                    ],
                ],
            ]);

        $resolver = new EcontCityResolverService($econtService);

        $offices = $resolver->getOffices('София');

        $this->assertCount(1, $offices);
        $this->assertSame('1000', $offices[0]['code']);
    }

    public function test_get_offices_matches_english_city_names_from_econt_data(): void
    {
        $econtService = Mockery::mock(EcontService::class);
        $econtService->shouldReceive('getCities')
            ->once()
            ->with('Sofia')
            ->andReturn([
                [
                    'id' => 68134,
                    'name' => 'София',
                    'nameEn' => 'Sofia',
                    'regionName' => 'София',
                ],
            ]);

        $econtService->shouldReceive('getOffices')
            ->once()
            ->with(68134)
            ->andReturn([
                [
                    'code' => '1111',
                    'name' => 'София Център',
                    'nameEn' => 'Sofia Center',
                    'address' => [
                        'city' => [
                            'name' => 'София',
                            'nameEn' => 'Sofia',
                        ],
                    ],
                ],
            ]);

        $resolver = new EcontCityResolverService($econtService);

        $offices = $resolver->getOffices('Sofia');

        $this->assertCount(1, $offices);
        $this->assertSame('1111', $offices[0]['code']);
    }
}
