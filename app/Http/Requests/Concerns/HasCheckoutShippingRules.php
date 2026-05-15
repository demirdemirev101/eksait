<?php

namespace App\Http\Requests\Concerns;

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
            'payment_method' => 'nullable|string',
            'session_id' => 'sometimes|string',
        ];
    }
}
