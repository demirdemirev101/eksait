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
        return [
            ...$this->checkoutShippingRules(),
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'payment_method' => 'required|in:bank_transfer,cod,stripe',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
