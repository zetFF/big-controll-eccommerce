<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'metadata' => 'nullable|array',
        ];

        if ($this->isMethod('POST')) {
            $rules['images'] = 'required|array|min:1';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg|max:2048';
        }

        if ($this->isMethod('PUT')) {
            $rules['images'] = 'nullable|array';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }
} 