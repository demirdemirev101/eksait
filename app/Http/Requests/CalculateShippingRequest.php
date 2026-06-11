<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasCheckoutShippingRules;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateShippingRequest extends FormRequest
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
        $paymentMethods = ['bank_transfer', 'cod'];

        if (Setting::current()->stripe_enabled) {
            $paymentMethods[] = 'stripe';
        }

        return [
            ...$this->checkoutShippingRules(),
            'payment_method' => ['required', Rule::in($paymentMethods)],
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ];
    }
}
