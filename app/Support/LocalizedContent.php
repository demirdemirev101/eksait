<?php

namespace App\Support;

use App\Services\CatalogTermTranslator;
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
            $translator = app(CatalogTermTranslator::class);
            $localized = $translator->normalizeForLocale($directTranslatedValue, $locale);

            if (self::shouldRebuildFromSource($localized)) {
                $localized = $translator->translateOffline($baseValue, 'en', self::DEFAULT_LOCALE);
                $localized = $translator->normalizeForLocale($localized, $locale);
            }

            return self::formatLocalizedValue($field, $localized);
        }

        $englishValue = self::rawValue($source, "{$field}_en");
        if (self::hasContent($englishValue)) {
            $translator = app(CatalogTermTranslator::class);
            $localized = $translator->normalizeForLocale($englishValue, $locale);

            if (self::shouldRebuildFromSource($localized)) {
                $localized = $translator->translateOffline($baseValue, 'en', self::DEFAULT_LOCALE);
                $localized = $translator->normalizeForLocale($localized, $locale);
            }

            return self::formatLocalizedValue($field, $localized);
        }

        $localized = app(CatalogTermTranslator::class)
            ->translate($baseValue, $locale === 'de' ? 'en' : $locale, self::DEFAULT_LOCALE);

        return self::formatLocalizedValue(
            $field,
            app(CatalogTermTranslator::class)->normalizeForLocale($localized, $locale)
        );
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

    private static function shouldRebuildFromSource(mixed $value): bool
    {
        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        $normalized = Str::upper($value);

        return (bool) preg_match(
            '/\b(?:SHAYBA|KECHANA|PLOSAK|OTREZEN|OTREZNA|SHKURKA|IZRAVNITEL|TARELKA|DIAMANTEN|KALIBAR|KALIBRI|PLASHKA|PLASHKI|FREZI|PETAOGALNA|SHESTOAGALNA|PERA|PERO|TSANGA|KECHE|ZARN|DVIGATEL|RAZVERTKA|UDALZHEN|PRAVOTOKOV|SACHMEN|RAZDVIZHEN|DORNIKOV|GAECHEN|SHESTOSTEN|SEKTOREN|SLYAP|KLESHTI|SEKACHKI|KOMBIN|KONTAKTOR|ZAMBA|ZABOLEKAR|ZVEZDA|LULA|TRISTR|AGLOVA|KVADR|TRIAG|CHERVYACHNA|MODULNA|RAZLICHNI|TSOLOVA|TSOLA?|NAVIVKI|ZAGOTOVKA|KOBALTOVA|TVARD|TVARDOSPLAVNA|LYAVA|LYAVO|PROBKA|GRIVNA|GLADAK|SHINA|LYAV|DESEN|KERAMICHNA|GLUH|OTVOR|VATRESHEN|VANSH|VANSHNO|KANALNA|KANAL|ZEGEROV|ZAPOYAEMA|PROREZEN|BORSHTANGA|CHIST|NOZHOVKA|GILOTINA|KRAGAL|GRADUSA|REZBOVA|REZBOVI|REZB|VATR\s+(?:R-?BA|THREAD)|METCHIK|METCHITSI|STOYKA|MAGNITNA|MENGEME|SHLOSERSKO|TRABNO|ELEKTR|NAKATK(?:A|I)?|KRASTOSAN|NAKLON|ROLKI|OTVERKA|VTULKA|PREHODNA|VINT|KRASTAT|TRIAGALNA|KVADRATNA|OBLA|POLUOBLA|PRESOSTAT|RAKAVITSI|ZAVARKA|CHERVENI|DALGI|DALGO|SINI|ZELENI|SIVI|KOZHENI|GUMIRANI|PROMAZANI|ZHALTI|STRUYNIK|SAEDINITEL|STAKLOREZ|TEKSTOLIT|PLANSHAYBA|PODVIZHNA|PROZHEKTOR|HALOGEN|PANTA|ROLETKA|FLANETS|REZBONAKATNI|REZBONAREZEN|CHERTILKA|CHELYUSTI|PRUZHINKI|KRACHE|SHABAR|FIBROSTAKLO|TSIFRI|PRITISKACHI|PALTSOVA|DYASNO|TABLO|PRAVI|OBRATNI|DIAMOND\s+SHAYBA|GRINDER\s+SHAYBA)\b/u',
            $normalized
        );
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
