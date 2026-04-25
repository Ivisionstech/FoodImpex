<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'purchase_price' => 'nullable|numeric',
            'sale_price' => 'required|numeric',
            'stock' => 'required_if:type,raw|nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'raw_materials' => 'required_if:type,finished|array|min:1',
            'raw_materials.*.product_id' => 'required|exists:products,id',
            'raw_materials.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter product name',
            'purchase_price.numeric' => 'Purchase price must be a valid number',
            'sale_price.required' => 'Please enter sale price',
            'sale_price.numeric' => 'Sale price must be a valid number',
            'stock.required_if' => 'Stock is required for raw materials',
            'stock.numeric' => 'Stock must be a valid number',
            'stock.min' => 'Stock cannot be negative',
            'image.image' => 'Please upload a valid image',
            'image.mimes' => 'Image must be jpeg, png, jpg, gif, or svg',
            'image.max' => 'Image size cannot exceed 2MB',
            'description.string' => 'Please enter a valid description',
            'raw_materials.required_if' => 'Raw materials are required for finished products',
            'raw_materials.array' => 'Raw materials must be an array',
            'raw_materials.min' => 'At least one raw material is required for finished products',
            'raw_materials.*.product_id.required' => 'Please select a raw material',
            'raw_materials.*.product_id.exists' => 'Selected raw material does not exist',
            'raw_materials.*.quantity.required' => 'Please enter quantity for raw material',
            'raw_materials.*.quantity.numeric' => 'Quantity must be a valid number',
            'raw_materials.*.quantity.min' => 'Quantity must be greater than 0',
        ];
    }
}
