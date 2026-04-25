<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class SendPaymentRequest extends FormRequest
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
            'amount' => 'required|min:1',
            'date' => 'required|date',
            'receipt_images' => 'nullable',
            'receipt_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter amount',
            'amount.min' => 'Amount must be greater than 0',
            'date.required' => 'Please enter date',
            'date.date' => 'Date must be a valid date',
            'receipt_images.array' => 'Receipt images must be an array',
            'receipt_images.*.image' => 'Receipt images must be an image',
            'receipt_images.*.mimes' => 'Receipt images must be an image',
            'receipt_images.*.max' => 'Receipt images must be less than 2MB',
        ];
    }
}
