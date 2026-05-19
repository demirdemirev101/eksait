<?php

namespace App\Support;

final class EcontDeliveryTypeResolver
{
    public static function resolve(
        ?string $shippingMethod,
        ?string $officeCode,
        ?bool $officeIsAps = null,
        ?string $officeName = null,
        ?string $officeAddress = null,
    ): string
    {
        $normalizedMethod = is_string($shippingMethod)
            ? strtolower(trim($shippingMethod))
            : null;
        $normalizedOfficeCode = is_string($officeCode)
            ? strtoupper(trim($officeCode))
            : null;
        $normalizedOfficeText = mb_strtolower(trim(implode(' ', array_filter([
            $officeName,
            $officeAddress,
        ]))));

        if ($normalizedMethod === 'address') {
            return 'address';
        }

        if ($normalizedMethod === 'apm' || $officeIsAps === true || self::isAutomaticPostStation($normalizedOfficeCode, $normalizedOfficeText)) {
            return 'apm';
        }

        if ($normalizedMethod === 'office') {
            return 'office';
        }

        if (! empty($normalizedOfficeCode)) {
            return 'office';
        }

        return 'address';
    }

    private static function isAutomaticPostStation(?string $officeCode, string $officeText): bool
    {
        if (str_starts_with($officeCode ?? '', 'APM')) {
            return true;
        }

        return str_contains($officeText, 'еконтомат')
            || str_contains($officeText, 'econtomat')
            || str_contains($officeText, 'автомат');
    }
}
