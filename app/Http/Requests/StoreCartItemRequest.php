<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
            'variant' => 'nullable|string|max:100',
            'color_slug' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'Product not found.',
            'color_slug.max' => 'Color slug is too long.',
        ];
    }
}
