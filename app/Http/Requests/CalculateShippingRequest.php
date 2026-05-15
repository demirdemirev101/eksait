<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasCheckoutShippingRules;
use Illuminate\Foundation\Http\FormRequest;

class CalculateShippingRequest extends FormRequest
{
    use HasCheckoutShippingRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->checkoutShippingRules(),
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|integer|exists:products,id',
            'items.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ];
    }
}
