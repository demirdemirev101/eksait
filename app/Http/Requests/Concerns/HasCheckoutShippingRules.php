<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait HasCheckoutShippingRules
{
    protected function checkoutShippingRules(): array
    {
        return [
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'shipping_method' => 'required|in:address,office,apm',
            'shipping_address' => 'required_if:shipping_method,address|string',
            'shipping_city' => 'required|string',
            'shipping_postcode' => 'nullable|string',
            'econt_office_code' => 'required_if:shipping_method,office,apm|string|nullable',
            'econt_office_name' => 'nullable|string',
            'econt_office_address' => 'nullable|string',
            'econt_office_is_aps' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
            'session_id' => ['sometimes', 'string', 'min:16', 'max:128', 'regex:/\A[A-Za-z0-9_-]+\z/'],
            'sessionId' => ['sometimes', 'string', 'min:16', 'max:128', 'regex:/\A[A-Za-z0-9_-]+\z/'],
        ];
    }

    protected function normalizeCheckoutOptionValues(): void
    {
        $this->merge([
            'payment_method' => $this->normalizePaymentMethod($this->input('payment_method')),
            'shipping_method' => $this->normalizeShippingMethod($this->input('shipping_method')),
        ]);
    }

    private function normalizePaymentMethod(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $normalized = $this->normalizeOptionKey($value);

        return match ($normalized) {
            'banktransfer',
            'bankovprevod',
            'uberweisung',
            'bankuberweisung' => 'bank_transfer',
            'cashondelivery',
            'cashdelivery',
            'cod',
            'nalozhenplatezh',
            'nachnahme' => 'cod',
            'card',
            'stripe',
            'kart',
            'karte' => 'stripe',
            default => $value,
        };
    }

    private function normalizeShippingMethod(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $normalized = $this->normalizeOptionKey($value);

        return match ($normalized) {
            'address',
            'toaddress',
            'addreszadosavka',
            'lieferadresse' => 'address',
            'office',
            'tooffice',
            'doofis',
            'pickupoffice',
            'abholstelle' => 'office',
            'apm',
            'parcellocker',
            'econtomat',
            'ekontomat',
            'automat',
            'paketautomat' => 'apm',
            default => $value,
        };
    }

    private function normalizeOptionKey(string $value): string
    {
        $ascii = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();

        return $ascii;
    }
}
