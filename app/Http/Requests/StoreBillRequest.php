<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_type' => ['required', 'in:existing,new'],
            'products.*.product_id' => ['required_if:products.*.product_type,existing', 'exists:products,id'],
            'products.*.name' => ['required_if:products.*.product_type,new', 'string', 'max:255'],
            'products.*.description' => ['nullable', 'string', 'max:1000'],
            'products.*.image' => ['nullable', 'image', 'max:2048'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.sale_price' => ['required', 'numeric', 'min:0'],
            'extra_charges' => ['nullable', 'array'],
            'extra_charges.*.name' => ['required', 'string', 'max:255'],
            'extra_charges.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'products.required' => 'At least one product is required.',
            'products.*.product_type.required' => 'Product type is required.',
            'products.*.product_id.required_if' => 'Please select a product.',
            'products.*.name.required_if' => 'Product name is required for new products.',
            'products.*.quantity.required' => 'Quantity is required.',
            'products.*.price.required' => 'Purchase price is required.',
            'products.*.sale_price.required' => 'Sale price is required.',
            'extra_charges.*.name.required' => 'Charge name is required.',
            'extra_charges.*.amount.required' => 'Charge amount is required.',
        ];
    }
}
