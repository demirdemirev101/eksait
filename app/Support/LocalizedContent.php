<?php

namespace App\Support;

use Illuminate\Http\Request;

class LocalizedContent
{
    public const DEFAULT_LOCALE = 'bg';

    /**
     * @return array<int, string>
     */
    public static function supportedLocales(): array
    {
        return ['bg', 'en', 'de'];
    }

    public static function requestedLocale(?Request $request = null): string
    {
        return self::normalizeLocale($request?->query('lang'));
    }

    public static function normalizeLocale(mixed $locale): string
    {
        $locale = is_string($locale) ? strtolower(trim($locale)) : '';

        return in_array($locale, self::supportedLocales(), true)
            ? $locale
            : self::DEFAULT_LOCALE;
    }

    public static function localizedValue(object|array $source, string $field, string $locale): mixed
    {
        $locale = self::normalizeLocale($locale);

        if ($locale === self::DEFAULT_LOCALE) {
            return self::rawValue($source, $field);
        }

        $translatedValue = self::rawValue($source, "{$field}_{$locale}");

        if (self::hasContent($translatedValue)) {
            return $translatedValue;
        }

        return self::rawValue($source, $field);
    }

    public static function rawValue(object|array $source, string $field): mixed
    {
        if (is_array($source)) {
            return $source[$field] ?? null;
        }

        return $source->{$field} ?? null;
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<string, array<string, mixed>>
     */
    public static function translations(object|array $source, array $fields): array
    {
        $translations = [];

        foreach (self::supportedLocales() as $locale) {
            foreach ($fields as $field) {
                $translations[$locale][$field] = $locale === self::DEFAULT_LOCALE
                    ? self::rawValue($source, $field)
                    : self::rawValue($source, "{$field}_{$locale}");
            }
        }

        return $translations;
    }

    private static function hasContent(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
    }
}
