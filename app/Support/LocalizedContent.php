<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $candidates = [
            $request?->query('lang'),
            $request?->query('locale'),
            $request?->header('X-Locale'),
            $request?->header('X-Language'),
            self::localeFromAcceptLanguage($request),
            app()->getLocale(),
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $normalized = self::normalizeLocale($candidate);

            if (in_array($normalized, self::supportedLocales(), true)) {
                return $normalized;
            }
        }

        return self::DEFAULT_LOCALE;
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
        $baseValue = self::rawValue($source, $field);

        if ($locale === self::DEFAULT_LOCALE) {
            return $baseValue;
        }

        $directTranslatedValue = self::rawValue($source, "{$field}_{$locale}");
        if (self::hasContent($directTranslatedValue)) {
            return self::formatLocalizedValue($field, $directTranslatedValue);
        }

        return self::formatLocalizedValue($field, $baseValue);
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
                $translations[$locale][$field] = self::localizedValue($source, $field, $locale);
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

    private static function localeFromAcceptLanguage(?Request $request): ?string
    {
        $header = (string) $request?->header('Accept-Language', '');

        if ($header === '') {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $locale = Str::of($part)->before(';')->trim()->lower()->value();
            $primary = Str::of($locale)->before('-')->value();

            if (in_array($primary, self::supportedLocales(), true)) {
                return $primary;
            }
        }

        return null;
    }

    private static function formatLocalizedValue(string $field, mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (in_array($field, ['name', 'size'], true)) {
            return Str::upper(trim($value));
        }

        return $value;
    }
}
