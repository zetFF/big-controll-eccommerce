<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:bank_transfer,credit_card,e_wallet',
            'shipping_method' => 'required|in:regular,express',
            'notes' => 'nullable|string|max:500',
        ];
    }
} 