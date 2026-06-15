<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class CatalogTermTranslator
{
    public function translate(mixed $value, string $targetLocale, string $sourceLocale = 'bg'): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $text = trim($value);
        $targetLocale = strtolower(trim($targetLocale));
        $sourceLocale = strtolower(trim($sourceLocale));

        if ($text === '' || $targetLocale === '' || $targetLocale === $sourceLocale) {
            return $value;
        }

        $cacheKey = $this->cacheKey($text, $targetLocale, $sourceLocale);
        $store = config('catalog_translation.cache_store', config('cache.default'));
        $ttl = max(60, (int) config('catalog_translation.cache_ttl', 2592000));

        return Cache::store($store)->remember($cacheKey, $ttl, function () use ($text, $targetLocale, $sourceLocale) {
            $translated = $this->translateViaProvider($text, $targetLocale, $sourceLocale);

            if (! is_string($translated) || trim($translated) === '') {
                $translated = $this->applyLegacyFallback($text, $targetLocale);
            }

            return $this->finalizeTranslatedText($translated !== '' ? $translated : $text, $targetLocale, $text);
        });
    }

    public function translateOffline(mixed $value, string $targetLocale, string $sourceLocale = 'bg'): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $text = trim($value);
        $targetLocale = strtolower(trim($targetLocale));
        $sourceLocale = strtolower(trim($sourceLocale));

        if ($text === '' || $targetLocale === '' || $targetLocale === $sourceLocale) {
            return $value;
        }

        $translated = $this->applyLegacyFallback($text, $targetLocale);

        return $this->finalizeTranslatedText($translated !== '' ? $translated : $text, $targetLocale, $text);
    }

    public function translateProviderOnly(mixed $value, string $targetLocale, string $sourceLocale = 'bg'): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim($value);
        $targetLocale = strtolower(trim($targetLocale));
        $sourceLocale = strtolower(trim($sourceLocale));

        if ($text === '' || $targetLocale === '' || $targetLocale === $sourceLocale) {
            return null;
        }

        $cacheKey = 'provider-only:v8:'.$this->cacheKey($text, $targetLocale, $sourceLocale);
        $store = config('catalog_translation.cache_store', config('cache.default'));
        $ttl = max(60, (int) config('catalog_translation.cache_ttl', 2592000));
        $cached = Cache::store($store)->get($cacheKey);

        if (is_string($cached)) {
            $finalized = $this->finalizeTranslatedText($cached, $targetLocale, $text);

            if ($finalized !== $cached) {
                Cache::store($store)->put($cacheKey, $finalized, $ttl);
            }

            return $finalized;
        }

        $translated = $this->translateViaProvider($text, $targetLocale, $sourceLocale);

        if (! is_string($translated) || trim($translated) === '') {
            return null;
        }

        $translated = $this->finalizeTranslatedText($translated, $targetLocale, $text);

        if (! is_string($translated) || trim($translated) === '') {
            return null;
        }

        Cache::store($store)->put($cacheKey, $translated, $ttl);

        return $translated;
    }

    public function normalizeForLocale(mixed $value, string $targetLocale): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $text = trim($value);
        $targetLocale = strtolower(trim($targetLocale));

        if ($text === '' || $targetLocale === 'bg') {
            return $value;
        }

        $text = $this->normalizeEmbeddedTechnicalCodes($text);

        if (! preg_match('/\p{Cyrillic}/u', $text)) {
            return $text;
        }

        $normalized = $this->transliterateCyrillicToLatin($text);

        return $normalized !== '' ? preg_replace('/\s+/u', ' ', trim($normalized)) : $text;
    }

    private function translateViaProvider(string $text, string $targetLocale, string $sourceLocale): ?string
    {
        return match (strtolower((string) config('catalog_translation.provider', 'google'))) {
            'google' => $this->translateViaGoogle($text, $targetLocale, $sourceLocale),
            default => null,
        };
    }

    private function translateViaGoogle(string $text, string $targetLocale, string $sourceLocale): ?string
    {
        $baseUrl = rtrim((string) config('catalog_translation.google.base_url', ''), '/');
        $apiKey = trim((string) config('catalog_translation.google.api_key', ''));
        $endpoint = '/'.ltrim((string) config('catalog_translation.google.endpoint', '/language/translate/v2'), '/');

        if ($baseUrl === '' || $apiKey === '') {
            return null;
        }

        [$preparedText, $restorers] = $this->prepareText($text, $targetLocale);

        try {
            $response = Http::baseUrl($baseUrl)
                ->asForm()
                ->acceptJson()
                ->timeout((int) config('catalog_translation.timeout', 20))
                ->withQueryParameters(['key' => $apiKey])
                ->post($endpoint, array_filter([
                    'q' => $preparedText,
                    'source' => $sourceLocale,
                    'target' => $targetLocale,
                    'format' => 'text',
                ], static fn ($value) => $value !== null && $value !== ''));

            if (! $response->successful()) {
                return null;
            }

            $translated = $response->json('data.translations.0.translatedText');

            if (! is_string($translated) || trim($translated) === '') {
                return null;
            }

            return $this->restorePlaceholders(
                html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                $restorers
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function prepareText(string $text, string $targetLocale): array
    {
        $prepared = preg_replace('/Ф(?=\s*\d)/u', 'Ø', $text) ?? $text;
        $restorers = [];

        $prepared = $this->protectGlossaryTerms($prepared, $targetLocale, $restorers);
        $prepared = $this->protectTechnicalTokens($prepared, $restorers);

        return [$prepared, $restorers];
    }

    private function protectGlossaryTerms(string $text, string $targetLocale, array &$restorers): string
    {
        $entries = config("catalog_translation.glossary.{$targetLocale}", []);

        if (! is_array($entries) || $entries === []) {
            return $text;
        }

        foreach ($entries as $entry) {
            $pattern = (string) ($entry['pattern'] ?? '');
            $replacement = (string) ($entry['replace'] ?? '');

            if ($pattern === '') {
                continue;
            }

            $text = preg_replace_callback($pattern, function (array $matches) use (&$restorers, $replacement): string {
                $placeholder = '__CAT_GLOSSARY_'.count($restorers).'__';
                $restorers[$placeholder] = $replacement;

                return $placeholder;
            }, $text) ?? $text;
        }

        return $text;
    }

    private function protectTechnicalTokens(string $text, array &$restorers): string
    {
        $patterns = [
            '~Ø\d+(?:[.,]\d+)?~u',
            '~(?:ISO|ИСО)\s*\d+(?:[A-Z0-9./-]*)~iu',
            '~\b[A-Z]{1,4}(?:\.[A-Z]{1,4}\.?)\b~u',
            '~\b[A-ZА-Я]{1,4}\d+[A-ZА-Я0-9./-]*\b~u',
            '~\b\d+(?:[.,]\d+)?(?:\s*/\s*\d+(?:[.,]\d+)?)+\b~u',
            '~\b\d+(?:[.,]\d+)?(?:\s*[xх]\s*\d+(?:[.,]\d+)?)+(?:\s*[xх]\s*\d+(?:[.,]\d+)?)?\b~u',
            '~\b\d+(?:[.,]\d+)?\s*(?:mm|мм|cm|см|m|м)\b~iu',
        ];

        foreach ($patterns as $pattern) {
            $text = preg_replace_callback($pattern, function (array $matches) use (&$restorers): string {
                $placeholder = '__CAT_CODE_'.count($restorers).'__';
                $restorers[$placeholder] = $this->normalizeTechnicalToken($matches[0]);

                return $placeholder;
            }, $text) ?? $text;
        }

        return $text;
    }

    private function normalizeTechnicalToken(string $token): string
    {
        $map = [
            'ЦО' => 'CO',
            'КО' => 'KO',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ж' => 'ZH',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'CH',
            'Ш' => 'SH',
            'Щ' => 'SHT',
            'Ъ' => 'A',
            'Ь' => '',
            'Ю' => 'YU',
            'Я' => 'YA',
        ];

        return strtr($token, $map);
    }

    private function restorePlaceholders(string $text, array $restorers): string
    {
        return trim((string) preg_replace_callback('/__CAT_(?:GLOSSARY|CODE)_\d+__/', function (array $matches) use ($restorers): string {
            return $restorers[$matches[0]] ?? $matches[0];
        }, $text));
    }

    private function applyLegacyFallback(string $text, string $targetLocale): string
    {
        $translated = $text;
        $rules = config("catalog_translation.rules.{$targetLocale}", []);

        if (is_array($rules)) {
            foreach ($rules as $rule) {
                if (! is_array($rule) || blank($rule['pattern'] ?? null)) {
                    continue;
                }

                $translated = preg_replace(
                    (string) $rule['pattern'],
                    (string) ($rule['replace'] ?? ''),
                    $translated
                ) ?? $translated;
            }
        }

        return trim((string) preg_replace('/\s+/u', ' ', $translated));
    }

    private function finalizeTranslatedText(string $text, string $targetLocale, ?string $sourceText = null): string
    {
        $translated = trim($text);
        $translated = $this->correctSourceAwareCatalogTranslations($sourceText, $translated, $targetLocale);
        $translated = $this->correctKnownCatalogTranslations($translated, $targetLocale);
        $translated = $this->applyLegacyFallback($translated, $targetLocale);

        return (string) $this->normalizeForLocale($translated, $targetLocale);
    }

    private function correctSourceAwareCatalogTranslations(?string $sourceText, string $translatedText, string $targetLocale): string
    {
        if (! is_string($sourceText) || trim($sourceText) === '') {
            return $translatedText;
        }

        $normalizedSource = $this->normalizeSourceTextForCatalogCorrections($sourceText);

        if ($normalizedSource === '') {
            return $translatedText;
        }

        $sourceReplacements = match ($targetLocale) {
            'en' => [
                'SVREDLO BARZOPROBIVNO' => 'HSS DRILL',
                'ZENKER KO' => 'COUNTERSINK KO',
                'ZENKOVKA KO' => 'COUNTERSINK KO',
                'DIAMANTEN IZRAVNITEL' => 'DIAMOND DRESSER',
                'DIAMANTEN IZRAVNITEL CO' => 'DIAMOND DRESSER CO',
                'DIAMANTEN IZRAVNITEL KO' => 'DIAMOND DRESSER KO',
                'DIAMANTEN SHLAYFGRIFER' => 'DIAMOND MOUNTED POINT',
                'CHASHKA EB' => 'EB CUP',
                'SHLAYFGRIFER KECHE' => 'MOUNTED POINT FELT',
                'LAMELEN SHLAYFGRIFER KECHE' => 'FLAP MOUNTED POINT FELT',
                'PLANSHAYBA' => 'FACEPLATE',
                'TSANGA' => 'COLLET',
                'ZAMBA' => 'PUNCH',
                'DISK ZA SHLAY' => 'GRINDING DISC',
                'PLASTINA SANDVIK' => 'SANDVIK INSERT',
            ],
            'de' => [
                'SVREDLO BARZOPROBIVNO' => 'HSS-BOHRER',
                'ZENKER KO' => 'SENKER KO',
                'ZENKOVKA KO' => 'SENKER KO',
                'DIAMANTEN IZRAVNITEL' => 'DIAMANTABRICHTER',
                'DIAMANTEN IZRAVNITEL CO' => 'DIAMANTABRICHTER CO',
                'DIAMANTEN IZRAVNITEL KO' => 'DIAMANTABRICHTER KO',
                'DIAMANTEN SHLAYFGRIFER' => 'DIAMANT-SCHLEIFSTIFT',
                'CHASHKA EB' => 'EB-BECHER',
                'SHLAYFGRIFER KECHE' => 'SCHLEIFSTIFT FILZ',
                'LAMELEN SHLAYFGRIFER KECHE' => 'LAMELLEN-SCHLEIFSTIFT FILZ',
                'PLANSHAYBA' => 'PLANSCHEIBE',
                'TSANGA' => 'SPANNZANGE',
                'ZAMBA' => 'LOCHPUNZE',
                'DISK OTREZEN' => 'TRENNSCHEIBE',
                'DISK ZA SHLAY' => 'SCHLEIFSCHEIBE',
                'PLASTINA SANDVIK' => 'SANDVIK-WENDEPLATTE',
            ],
            default => [],
        };

        return $sourceReplacements[$normalizedSource] ?? $translatedText;
    }

    private function normalizeSourceTextForCatalogCorrections(string $sourceText): string
    {
        $normalized = $this->normalizeForLocale($sourceText, 'en');

        if (! is_string($normalized)) {
            return '';
        }

        return strtoupper(trim($normalized));
    }

    private function correctKnownCatalogTranslations(string $text, string $targetLocale): string
    {
        $exactReplacements = match ($targetLocale) {
            'en' => [
                'HARDWARE DRIVER' => 'CARBIDE REAMER',
                'MILLING CUTTER HARDWARE' => 'CARBIDE MILLING CUTTER',
                'MILLING CUTTER HARDWARE CO' => 'CARBIDE MILLING CUTTER CO',
                'MILLING CUTTER RADIUS HARDWARE' => 'RADIUS CARBIDE MILLING CUTTER',
                'MILLING CUTTER RADIUS HARDWARE CO' => 'RADIUS CARBIDE MILLING CUTTER CO',
                'ZENKER' => 'COUNTERSINK',
                'ZENKER CO.' => 'COUNTERSINK CO',
                'ZENKER ZTP CO' => 'COUNTERSINK ZTP CO',
                'ZENKER ZTP KO' => 'COUNTERSINK ZTP KO',
                'ZENKER DRUM ZTP' => 'COUNTERSINK ARBOR ZTP',
                'ZENKER CONE CO' => 'CONICAL COUNTERSINK CO',
                'ZENKER SM PL S 09' => 'COUNTERSINK SM PL S 09',
                'ZENKER CON OP 1/10' => 'COUNTERSINK CON OP 1/10',
                'ZENKER SM S PL CSO' => 'COUNTERSINK SM S PL CO',
                'ZENKOVKA KO MK1' => 'COUNTERSINK KO MK1',
                'ZENKOVKA KO MK2' => 'COUNTERSINK KO MK2',
                'ZENKOVKA KO MK3' => 'COUNTERSINK KO MK3',
                'ZENKOVKA KO MK4' => 'COUNTERSINK KO MK4',
                'DRILL TSO' => 'DRILL CO',
                'DIAMOND LEVELER' => 'DIAMOND DRESSER',
                'DIAMOND LEVELER C.O.' => 'DIAMOND DRESSER CO',
                'DISC SOBER' => 'CUT-OFF DISC',
                'SANDWICH PLATE' => 'SANDVIK INSERT',
                'ARBOR ISO40 FOR RV ON' => 'ARBOR ISO40 FOR RV',
                'ARBOR ISO50 FOR RV ON' => 'ARBOR ISO50 FOR RV',
                'ARBOR ISO30 ON' => 'ARBOR ISO30',
                'ARBOR ISO40 ON' => 'ARBOR ISO40',
                'ARBOR ISO50 ON' => 'ARBOR ISO50',
                'ARBOR ISO50 FOR FU320 ON' => 'ARBOR ISO50 FOR FU320',
                'ARBOR ISO50 FOR FU320 EXTENDED TO' => 'ARBOR ISO50 FOR FU320 EXTENDED',
                'ARBOR FOR INST MILLING CUTTER MK2 OF' => 'ARBOR FOR INST MILLING CUTTER MK2',
                'ARBOR FOR INST MILLING CUTTER MK3 OF' => 'ARBOR FOR INST MILLING CUTTER MK3',
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
                'TEETH SECT FOR CIRCUS' => 'SECTOR TEETH FOR CIRCULAR CUTTER',
                'MILLING CUTTER CIRCUS' => 'CIRCULAR MILLING CUTTER',
                'MILLING CUTTER CIRCUS HARD' => 'CIRCULAR CARBIDE MILLING CUTTER',
                'MILLING CUTTER CEL CIL T N' => 'FACE CYLINDRICAL MILLING CUTTER T N',
                'MILLING CUTTER CEL CIL T R' => 'FACE CYLINDRICAL MILLING CUTTER T R',
                'MILLING CUTTER CEL CIL T T' => 'FACE CYLINDRICAL MILLING CUTTER T T',
                'MILLING CUTTER 4ERVYAČNA' => 'WORM MILLING CUTTER',
                'MILLING CUTTER DORNIKOVA FROM' => 'ARBOR MILLING CUTTER OT',
                'MILLING CUTTER PROTRUSION' => 'CONVEX MILLING CUTTER',
                'MILLING CUTTER TRISTR R30' => 'MILLING CUTTER TRISTR P30',
                'MILLING CUTTER 2 HARDWOOD FEATHERS' => 'MILLING CUTTER 2 CARBIDE FLUTES',
                'MILLING CUTTER 3 HARDWOOD FEATHERS' => 'MILLING CUTTER 3 CARBIDE FLUTES',
            ],
            'de' => [
                'ZENKER' => 'SENKER',
                'ZENKER CO.' => 'SENKER CO',
                'ZENKER ZTP CO' => 'SENKER ZTP CO',
                'ZENKER ZTP KO' => 'SENKER ZTP KO',
                'ZENKER DRUM ZTP' => 'SENKER DORN ZTP',
                'ZENKER CONE CO' => 'KONISCHER SENKER CO',
                'ZENKER SM PL S 09' => 'SENKER SM PL S 09',
                'ZENKER CON OP 1/10' => 'SENKER CON OP 1/10',
                'ZENKER SM S PL CSO' => 'SENKER SM S PL CO',
                'ZENKOVKA KO MK1' => 'SENKER KO MK1',
                'ZENKOVKA KO MK2' => 'SENKER KO MK2',
                'ZENKOVKA KO MK3' => 'SENKER KO MK3',
                'ZENKOVKA KO MK4' => 'SENKER KO MK4',
                'SPÜLBECKEN CO' => 'SENKER CO',
                'SPÜLBECKEN CO T N' => 'SENKER CO T N',
                'BOHRER TSO' => 'BOHRER CO',
                'GRINDER EN' => 'SCHLEIFSTIFT EN',
                'SANDWICH GRIPPER ER' => 'SCHLEIFSTIFT ER',
                'EB GRIPPER' => 'SCHLEIFSTIFT EB',
                'KECHANA-WASCHMASCHINE' => 'FILZSCHEIBE',
                'BLECHSANDWICH' => 'SCHLEIFBLATT',
                'RUNDES SANDWICH' => 'RUNDSCHLEIFPAPIER',
                'DIAMANT-NIVEAUSCHALTER' => 'DIAMANTABRICHTER',
                'DIAMOND LEVELER C.O.' => 'DIAMANTABRICHTER CO',
                'SANDWICHTELLER' => 'SANDVIK-WENDEPLATTE',
                'KURZER BAR __KATALOGGLOSSAR_1__ __KATALOGGLOSSAR_0__' => 'KURZE SPANNZANGE MIT FRÄSER',
                'BAR LANG FÜR WOOD' => 'LANGE SPANNZANGE FÜR HOLZ',
                'FRÄSER HARTMETALLFRÄSER' => 'HARTMETALLFRÄSER',
                'FRÄSER TRISTR TYPE H' => 'FRÄSER TRISTR TYP N',
                'DORN ISO40 FÜR RV ON' => 'DORN ISO40 FÜR RV',
                'DORN ISO50 FÜR RV ON' => 'DORN ISO50 FÜR RV',
                'DORN ISO30 EIN' => 'DORN ISO30',
                'DORN ISO40 EIN' => 'DORN ISO40',
                'DORN ISO50 EIN' => 'DORN ISO50',
                'DORN ISO50 FÜR FU320 EIN' => 'DORN ISO50 FÜR FU320',
                'DORN ISO50 FÜR FU320 ERWEITERT AUF' => 'DORN ISO50 FÜR FU320 ERWEITERT',
                'DORN FÜR INST FRÄSER MK2 ON' => 'DORN FÜR INST FRÄSER MK2',
                'DORN FÜR INST FRÄSER MK3 ON' => 'DORN FÜR INST FRÄSER MK3',
                'HARDWARETREIBER' => 'HARTMETALLREIBAHLE',
                'FRÄSER HARDWARE' => 'HARTMETALLFRÄSER',
                'FRÄSER HARDWARE CO' => 'HARTMETALLFRÄSER CO',
                'FRÄSER RADIUS-HARDWARE' => 'RADIUS-HARTMETALLFRÄSER',
                'FRÄSER RADIUS HARDWARE' => 'RADIUS-HARTMETALLFRÄSER',
                'FRÄSER RADIUS HARDWARE CO' => 'RADIUS-HARTMETALLFRÄSER CO',
                'FRÄSER ZIRKUS' => 'KREISFRÄSER',
                'FRÄSER CIRCUS HARD' => 'KREIS-HARTMETALLFRÄSER',
                'FRÄSER CEL CIL T N' => 'STIRN-ZYLINDERFRÄSER T N',
                'FRÄSER CEL CIL T R' => 'STIRN-ZYLINDERFRÄSER T R',
                'FRÄSER CEL CIL T T' => 'STIRN-ZYLINDERFRÄSER T T',
                'FRÄSER 4ERVYAČNA' => 'SCHNECKENFRÄSER',
                'FRÄSER DORNIKOVA VON' => 'DORNFRÄSER OT',
                'FRÄSER VORSPRUNG' => 'KONVEXFRÄSER',
                'FRÄSER TRISTR R30' => 'FRÄSER TRISTR P30',
            ],
            default => [],
        };

        $translated = $exactReplacements[$text] ?? $text;

        $patternReplacements = match ($targetLocale) {
            'en' => [
                '/\bZENKER\b/u' => 'COUNTERSINK',
                '/\bZENKOVKA\b/u' => 'COUNTERSINK',
                '/\bHARDWARE\b/u' => 'CARBIDE',
                '/\bCIRCUS\b/u' => 'CIRCULAR',
                '/\bFEATHERS\b/u' => 'FLUTES',
                '/\bTRISTR R(?=\d+\b)/u' => 'TRISTR P',
                '/\b4ERVYAČNA\b/u' => 'WORM',
            ],
            'de' => [
                '/\bZENKER\b/u' => 'SENKER',
                '/\bZENKOVKA\b/u' => 'SENKER',
                '/\bSPÜLBECKEN\b/u' => 'SENKER',
                '/\bHARDWARE\b/u' => 'HARTMETALL',
                '/\bZIRKUS\b/u' => 'KREIS',
                '/\bCIRCUS\b/u' => 'KREIS',
                '/\bFEDERN\b/u' => 'SCHNEIDEN',
                '/\bTRISTR R(?=\d+\b)/u' => 'TRISTR P',
                '/\b4ERVYAČNA\b/u' => 'SCHNECKEN',
            ],
            default => [],
        };

        foreach ($patternReplacements as $pattern => $replacement) {
            $translated = preg_replace($pattern, $replacement, $translated) ?? $translated;
        }

        return trim((string) preg_replace('/\s+/u', ' ', $translated));
    }

    private function normalizeEmbeddedTechnicalCodes(string $text): string
    {
        $replacements = [
            '/(?<!\pL)ЦО(?!\pL)/u' => 'CO',
            '/(?<!\pL)КО(?!\pL)/u' => 'KO',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $text;
    }

    private function transliterateCyrillicToLatin(string $text): string
    {
        $map = [
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ж' => 'ZH',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'TS',
            'Ч' => 'CH',
            'Ш' => 'SH',
            'Щ' => 'SHT',
            'Ъ' => 'A',
            'Ь' => 'Y',
            'Ю' => 'YU',
            'Я' => 'YA',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sht',
            'ъ' => 'a',
            'ь' => 'y',
            'ю' => 'yu',
            'я' => 'ya',
        ];

        return strtr($text, $map);
    }

    private function cacheKey(string $text, string $targetLocale, string $sourceLocale): string
    {
        return 'catalog-translation:'.md5($sourceLocale.'|'.$targetLocale.'|'.$text);
    }
}
