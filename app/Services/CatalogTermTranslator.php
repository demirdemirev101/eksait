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

            return $translated !== '' ? $translated : $text;
        });
    }

    private function translateViaProvider(string $text, string $targetLocale, string $sourceLocale): ?string
    {
        if (strtolower((string) config('catalog_translation.provider', 'libretranslate')) !== 'libretranslate') {
            return null;
        }

        $baseUrl = rtrim((string) config('catalog_translation.base_url', ''), '/');
        if ($baseUrl === '') {
            return null;
        }

        [$preparedText, $restorers] = $this->prepareText($text, $targetLocale);

        try {
            $response = Http::baseUrl($baseUrl)
                ->asForm()
                ->acceptJson()
                ->timeout((int) config('catalog_translation.timeout', 20))
                ->post('/translate', array_filter([
                    'q' => $preparedText,
                    'source' => $sourceLocale,
                    'target' => $targetLocale,
                    'format' => 'text',
                    'api_key' => blank(config('catalog_translation.api_key')) ? null : (string) config('catalog_translation.api_key'),
                ], static fn ($value) => $value !== null && $value !== ''));

            if (! $response->successful()) {
                return null;
            }

            $translated = $response->json('translatedText');

            if (! is_string($translated) || trim($translated) === '') {
                return null;
            }

            return $this->restorePlaceholders($translated, $restorers);
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
                $placeholder = '__CAT_GLOSSARY_' . count($restorers) . '__';
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
            '~\b[A-ZА-Я]{1,4}(?:\.[A-ZА-Я]{1,4}\.?)\b~u',
            '~\b[A-ZА-Я]{1,4}\d+[A-ZА-Я0-9./-]*\b~u',
            '~\b[А-Я]{1,3}\b~u',
            '~\b\d+(?:[.,]\d+)?(?:\s*[xх]\s*\d+(?:[.,]\d+)?)+(?:\s*[xх]\s*\d+(?:[.,]\d+)?)?\b~u',
            '~\b\d+(?:[.,]\d+)?\s*(?:mm|мм|cm|см|m|м)\b~iu',
        ];

        foreach ($patterns as $pattern) {
            $text = preg_replace_callback($pattern, function (array $matches) use (&$restorers): string {
                $placeholder = '__CAT_CODE_' . count($restorers) . '__';
                $restorers[$placeholder] = $this->normalizeTechnicalToken($matches[0]);

                return $placeholder;
            }, $text) ?? $text;
        }

        return $text;
    }

    private function normalizeTechnicalToken(string $token): string
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

    private function cacheKey(string $text, string $targetLocale, string $sourceLocale): string
    {
        return 'catalog-translation:' . md5($sourceLocale . '|' . $targetLocale . '|' . $text);
    }
}
