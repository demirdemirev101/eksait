<?php

namespace App\Services\Econt;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EcontCityResolverService
{
    private const ALL_OFFICES_CACHE_KEY = 'econt_offices_all';
    private const CITY_OFFICES_CACHE_DAYS = 7;

    public function __construct(
        private EcontService $econtService
    ) {}

    /**
     * Attempts to resolve an Econt city ID based on the provided city name and optional post code.
     * This method currently relies on the remote cities nomenclature, which may be too large for normal checkout lookups.
     * Keep it for explicit city-ID use cases, but do not use it in the office lookup flow.
     */
    public function getCityId(string $cityName, ?string $postCode = null): ?int
    {
        $normalizedName = $this->normalizeCityName($cityName);
        $normalizedPostCode = $postCode ? trim($postCode) : null;

        $cacheKey = 'econt_city_'.md5($normalizedName.'_'.$normalizedPostCode);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($cityName, $normalizedName, $normalizedPostCode) {
            $cities = $this->econtService->getCities($cityName);

            Log::info('Resolving Econt city', [
                'city' => $cityName,
                'postCode' => $normalizedPostCode,
                'count' => count($cities),
            ]);

            if ($normalizedPostCode) {
                foreach ($cities as $city) {
                    if (
                        $this->cityMatchesNormalizedName($city, $normalizedName) &&
                        isset($city['postCode']) &&
                        $city['postCode'] === $normalizedPostCode
                    ) {
                        return (int) $city['id'];
                    }
                }
            }

            foreach ($cities as $city) {
                if (
                    $this->cityMatchesNormalizedName($city, $normalizedName) &&
                    ! empty($city['regionName'])
                ) {
                    return (int) $city['id'];
                }
            }

            foreach ($cities as $city) {
                if (
                    $this->cityMatchesNormalizedName($city, $normalizedName) &&
                    ($city['expressCityDeliveries'] ?? false)
                ) {
                    return (int) $city['id'];
                }
            }

            Log::error('Econt city could not be uniquely resolved', [
                'searched' => $cityName,
                'postCode' => $normalizedPostCode,
            ]);

            return null;
        });
    }

    /**
     * Retrieves a list of Econt offices for a given city name.
     * To avoid loading the oversized cities nomenclature on every checkout request,
     * this flow uses the offices nomenclature and filters it locally by city name.
     */
    public function getOffices(string $cityName): array
    {
        $normalizedCityName = $this->normalizeCityName($cityName);

        if ($normalizedCityName === '') {
            return [];
        }

        $cacheKey = 'econt_offices_city_'.md5($normalizedCityName);

        $cached = Cache::get($cacheKey);

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $cityId = $this->resolveCityIdForOfficeLookup($cityName);

        try {
            $offices = [];

            if ($cityId !== null) {
                try {
                    $offices = $this->fetchOfficesByCityId($cityId, $normalizedCityName);
                } catch (\Throwable $e) {
                    Log::warning('Econt city-specific office lookup failed, falling back to all offices', [
                        'city' => $cityName,
                        'city_id' => $cityId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($offices === []) {
                $offices = $this->fetchAndFilterAllOffices($normalizedCityName);
            }

            if ($offices !== []) {
                Cache::put($cacheKey, $offices, now()->addDays(self::CITY_OFFICES_CACHE_DAYS));
            } else {
                Cache::forget($cacheKey);
            }

            Log::info('Econt offices resolved by city name', [
                'city' => $cityName,
                'city_id' => $cityId,
                'count' => count($offices),
            ]);

            return $offices;
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);

            Log::error('Econt offices lookup failed', [
                'city' => $cityName,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function resolveCityIdForOfficeLookup(string $cityName): ?int
    {
        try {
            return $this->getCityId($cityName);
        } catch (\Throwable $e) {
            Log::warning('Econt city id lookup failed, falling back to office list filtering', [
                'city' => $cityName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getOfficeByCode(string $officeCode): ?array
    {
        $normalizedOfficeCode = trim($officeCode);

        if ($normalizedOfficeCode === '') {
            return null;
        }

        $allOffices = Cache::remember(self::ALL_OFFICES_CACHE_KEY, now()->addDay(), function () {
            return $this->econtService->getOffices();
        });

        foreach ($allOffices as $office) {
            if (! is_array($office)) {
                continue;
            }

            $code = (string) ($office['code'] ?? $office['officeCode'] ?? $office['office_code'] ?? '');

            if ($code === $normalizedOfficeCode) {
                return $office;
            }
        }

        return null;
    }

    private function fetchOfficesByCityId(int $cityId, string $normalizedCityName): array
    {
        $offices = $this->econtService->getOffices($cityId);

        return array_values(array_filter($offices, function ($office) use ($normalizedCityName) {
            return is_array($office) && $this->officeMatchesCity($office, $normalizedCityName);
        }));
    }

    private function fetchAndFilterAllOffices(string $normalizedCityName): array
    {
        $allOffices = Cache::get(self::ALL_OFFICES_CACHE_KEY);

        if (! is_array($allOffices) || $allOffices === []) {
            $allOffices = $this->econtService->getOffices();

            if (is_array($allOffices) && $allOffices !== []) {
                Cache::put(self::ALL_OFFICES_CACHE_KEY, $allOffices, now()->addDay());
            } else {
                Cache::forget(self::ALL_OFFICES_CACHE_KEY);
            }
        }

        return array_values(array_filter($allOffices, function ($office) use ($normalizedCityName) {
            return is_array($office) && $this->officeMatchesCity($office, $normalizedCityName);
        }));
    }

    public function getOfficeByName(string $cityName, string $officeName): ?array
    {
        $normalizedCityName = $this->normalizeCityName($cityName);
        $normalizedOfficeName = $this->normalizeOfficeName($officeName);

        if ($normalizedCityName === '' || $normalizedOfficeName === '') {
            return null;
        }

        $matches = [];

        foreach ($this->getOffices($cityName) as $office) {
            if (! is_array($office)) {
                continue;
            }

            if ($this->officeMatchesName($office, $normalizedOfficeName, $normalizedCityName)) {
                $matches[] = $office;
            }
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        return null;
    }

    private function officeMatchesCity(array $office, string $normalizedCityName): bool
    {
        $candidates = [
            $office['cityName'] ?? null,
            $office['cityNameEn'] ?? null,
            $office['city'] ?? null,
            data_get($office, 'address.city.name'),
            data_get($office, 'address.city.nameEn'),
            data_get($office, 'address.cityName'),
            data_get($office, 'address.cityNameEn'),
            $office['hubName'] ?? null,
            $office['hubNameEn'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            if ($this->normalizeCityName($candidate) === $normalizedCityName) {
                return true;
            }
        }

        return false;
    }

    private function cityMatchesNormalizedName(array $city, string $normalizedCityName): bool
    {
        $candidates = [
            $city['name'] ?? null,
            $city['nameEn'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            if ($this->normalizeCityName($candidate) === $normalizedCityName) {
                return true;
            }
        }

        return false;
    }

    private function officeMatchesName(array $office, string $normalizedOfficeName, string $normalizedCityName): bool
    {
        $candidates = [
            $office['name'] ?? null,
            $office['nameEn'] ?? null,
            $office['officeName'] ?? null,
            data_get($office, 'address.fullAddress'),
            data_get($office, 'address.street'),
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $normalizedCandidate = $this->normalizeOfficeName($candidate, $normalizedCityName);

            if ($normalizedCandidate === $normalizedOfficeName) {
                return true;
            }

            if (str_contains($normalizedCandidate, $normalizedOfficeName)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeCityName(?string $cityName): string
    {
        $normalized = mb_strtolower(trim((string) $cityName));
        $normalized = preg_replace('/^(гр\\.?|град)\s+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return $normalized;
    }

    private function normalizeOfficeName(?string $officeName, ?string $normalizedCityName = null): string
    {
        $normalized = mb_strtolower(trim((string) $officeName));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        $normalizedCityName ??= '';

        if ($normalizedCityName !== '' && str_starts_with($normalized, $normalizedCityName.' ')) {
            $normalized = mb_substr($normalized, mb_strlen($normalizedCityName.' '));
        }

        return trim($normalized);
    }
}
