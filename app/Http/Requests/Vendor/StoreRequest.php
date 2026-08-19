<?php

namespace App\Http\Requests\Vendor;

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
            'company_name' => 'required|string|max:255',
            'person_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'required|string|max:500',
            // REMOVED: 'min:0' to allow negative values
            // REMOVED: 'required' - made nullable so it's optional
            'balance' => 'nullable|numeric',
            'open_balance_date' => 'nullable|date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Please enter company name',
            'person_name.required' => 'Please enter person name',
            'phone.required' => 'Please enter phone number',
            'phone.max' => 'Phone number must not exceed 20 characters',
            'address.required' => 'Please enter address',
            'address.max' => 'Address must not exceed 500 characters',
            'profile.image' => 'Profile must be an image file',
            'profile.mimes' => 'Profile must be a JPG, PNG, GIF, or SVG file',
            'profile.max' => 'Profile image must be less than 2MB',
            'balance.numeric' => 'Balance must be a valid number',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email must not exceed 255 characters',
            'open_balance_date.date' => 'Please enter a valid date',
        ];
    }
}