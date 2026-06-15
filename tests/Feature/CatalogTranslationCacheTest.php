<?php

namespace Tests\Feature;

use App\Services\CatalogTermTranslator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CatalogTranslationCacheTest extends TestCase
{
    public function test_cached_translation_is_corrected_for_known_bad_exact_value(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $key = $this->providerOnlyCacheKey('ФРЕЗА ТРИСТР Р30', 'en', 'bg');
        Cache::store('array')->put($key, 'MILLING CUTTER TRISTR R30', 3600);

        $translated = app(CatalogTermTranslator::class)->translateProviderOnly('ФРЕЗА ТРИСТР Р30', 'en', 'bg');

        $this->assertSame('MILLING CUTTER TRISTR P30', $translated);
        $this->assertSame('MILLING CUTTER TRISTR P30', Cache::store('array')->get($key));
    }

    public function test_cached_translation_is_corrected_for_generic_bad_term(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $key = $this->providerOnlyCacheKey('Ф0.22 ТВЪРДОСПЛ', 'en', 'bg');
        Cache::store('array')->put($key, 'Ø0.22 HARDWARE', 3600);

        $translated = app(CatalogTermTranslator::class)->translateProviderOnly('Ф0.22 ТВЪРДОСПЛ', 'en', 'bg');

        $this->assertSame('Ø0.22 CARBIDE', $translated);
        $this->assertSame('Ø0.22 CARBIDE', Cache::store('array')->get($key));
    }

    public function test_cached_translation_keeps_embedded_co_code(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $key = $this->providerOnlyCacheKey('СВРЕДЛО ЦО', 'en', 'bg');
        Cache::store('array')->put($key, 'DRILL ЦО', 3600);

        $translated = app(CatalogTermTranslator::class)->translateProviderOnly('СВРЕДЛО ЦО', 'en', 'bg');

        $this->assertSame('DRILL CO', $translated);
        $this->assertSame('DRILL CO', Cache::store('array')->get($key));
    }

    public function test_cached_translation_is_corrected_for_recent_bad_catalog_terms(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $cases = [
            'SHEET PEEL' => 'ABRASIVE SHEET',
            'ROUND SANDWICH' => 'ROUND SANDPAPER',
            'SANDING PAPER - ROLLER' => 'SANDPAPER ROLL',
            'GRINDER EN' => 'MOUNTED POINT EN',
            'SANDWICH GRIPPER ER' => 'MOUNTED POINT ER',
            'EB GRIPPER' => 'MOUNTED POINT EB',
            'KECHANA WASHER' => 'KECHANA PUCK',
            'SHORT BAR WITH MILLING CUTTER' => 'SHORT COLLET WITH MILLING CUTTER',
            'BAR LONG FOR WOOD' => 'LONG COLLET FOR WOOD',
            'MILLING CUTTER CARBIDE CUTTER' => 'CARBIDE MILLING CUTTER',
            'MILLING CUTTER TRISTR TYPE H' => 'MILLING CUTTER TRISTR TYPE N',
            'ZENKER CO.' => 'COUNTERSINK CO',
            'ZENKER DRUM ZTP' => 'COUNTERSINK ARBOR ZTP',
            'ZENKOVKA KO MK2' => 'COUNTERSINK KO MK2',
            'DRILL TSO' => 'DRILL CO',
            'DIAMOND LEVELER C.O.' => 'DIAMOND DRESSER CO',
            'SANDWICH PLATE' => 'SANDVIK INSERT',
            'ARBOR ISO40 FOR RV ON' => 'ARBOR ISO40 FOR RV',
        ];

        foreach ($cases as $cachedValue => $expectedValue) {
            $key = $this->providerOnlyCacheKey('TEST SOURCE '.$cachedValue, 'en', 'bg');
            Cache::store('array')->put($key, $cachedValue, 3600);

            $translated = app(CatalogTermTranslator::class)->translateProviderOnly('TEST SOURCE '.$cachedValue, 'en', 'bg');

            $this->assertSame($expectedValue, $translated);
            $this->assertSame($expectedValue, Cache::store('array')->get($key));
        }
    }

    public function test_cached_translation_is_corrected_for_recent_bad_catalog_terms_in_german(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $cases = [
            'ZENKER CO.' => 'SENKER CO',
            'ZENKER DRUM ZTP' => 'SENKER DORN ZTP',
            'ZENKOVKA KO MK2' => 'SENKER KO MK2',
            'SPÜLBECKEN CO' => 'SENKER CO',
            'BOHRER TSO' => 'BOHRER CO',
            'BLECHSANDWICH' => 'SCHLEIFBLATT',
            'FRÄSER TRISTR TYPE H' => 'FRÄSER TRISTR TYP N',
            'DORN ISO40 FÜR RV ON' => 'DORN ISO40 FÜR RV',
        ];

        foreach ($cases as $cachedValue => $expectedValue) {
            $key = $this->providerOnlyCacheKey('TEST SOURCE '.$cachedValue, 'de', 'bg');
            Cache::store('array')->put($key, $cachedValue, 3600);

            $translated = app(CatalogTermTranslator::class)->translateProviderOnly('TEST SOURCE '.$cachedValue, 'de', 'bg');

            $this->assertSame($expectedValue, $translated);
            $this->assertSame($expectedValue, Cache::store('array')->get($key));
        }
    }

    public function test_cached_translation_can_be_corrected_using_source_text_context(): void
    {
        config([
            'catalog_translation.cache_store' => 'array',
        ]);

        $cases = [
            ['ЗЕНКЕР КО', 'en', 'ZENKER CO.', 'COUNTERSINK KO'],
            ['ЗЕНКОВКА КО', 'de', 'SPÜLBECKEN CO', 'SENKER KO'],
            ['ПЛАНШАЙБА', 'en', 'WASHER', 'FACEPLATE'],
            ['ЦАНГА', 'de', 'HALSKRAUSE', 'SPANNZANGE'],
            ['ДИАМАНТЕН ИЗРАВНИТЕЛ КО', 'en', 'DIAMOND LEVELER KO', 'DIAMOND DRESSER KO'],
            ['ДИАМАНТЕН ШЛАЙФГРИФЕР', 'de', 'DIAMANTSCHLEIFER-GRIFF', 'DIAMANT-SCHLEIFSTIFT'],
            ['СВРЕДЛО БЪРЗОПРОБИВНО', 'en', 'QUICK DRILL DRILL', 'HSS DRILL'],
        ];

        foreach ($cases as [$sourceText, $locale, $cachedValue, $expectedValue]) {
            $key = $this->providerOnlyCacheKey($sourceText, $locale, 'bg');
            Cache::store('array')->put($key, $cachedValue, 3600);

            $translated = app(CatalogTermTranslator::class)->translateProviderOnly($sourceText, $locale, 'bg');

            $this->assertSame($expectedValue, $translated);
            $this->assertSame($expectedValue, Cache::store('array')->get($key));
        }
    }

    private function providerOnlyCacheKey(string $text, string $targetLocale, string $sourceLocale): string
    {
        return 'provider-only:v8:catalog-translation:' . md5($sourceLocale . '|' . $targetLocale . '|' . $text);
    }
}
