<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasCheckoutShippingRules;
use App\Models\Setting;
use App\Support\LocalizedContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    use HasCheckoutShippingRules;

    protected function prepareForValidation(): void
    {
        $this->normalizeCheckoutOptionValues();
    }

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

        $paymentMethods = ['bank_transfer', 'cod'];

        if (Setting::current()->stripe_enabled) {
            $paymentMethods[] = 'stripe';
        }

        return [
            ...$this->checkoutShippingRules(),
            ...$customerRules,
            'locale' => ['nullable', Rule::in(LocalizedContent::supportedLocales())],
            'payment_method' => ['required', Rule::in($paymentMethods)],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
