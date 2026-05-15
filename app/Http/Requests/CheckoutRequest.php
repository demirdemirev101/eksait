<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasCheckoutShippingRules;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    use HasCheckoutShippingRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $customerRules = $this->user()
            ? [
                'customer_name' => 'nullable|string',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'nullable|string',
            ]
            : [
                'customer_name' => 'required|string',
                'customer_email' => 'required|email',
                'customer_phone' => 'nullable|string',
            ];

        return [
            ...$this->checkoutShippingRules(),
            ...$customerRules,
            'payment_method' => 'required|in:bank_transfer,cod,stripe',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
