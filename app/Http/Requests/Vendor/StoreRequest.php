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
            'phone' => 'required|string|max:255',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'required|string|max:255',
            'balance' => 'required|numeric|min:0',
            'open_balance_date' => 'required_unless:balance,0|nullable|date',
        ];
    }
    public function messages(): array
    {
        return [
            'company_name.required' => 'Please enter company name',
            'person_name.required' => 'Please enter person name',
            'email.required' => 'Please enter email',
            'phone.required' => 'Please enter phone',
            'profile.image' => 'Profile must be an image',
            'profile.mimes' => 'Profile must be an image',
            'profile.max' => 'Profile must be less than 2MB',
            'address.required' => 'Please enter address',
            'balance.required' => 'Please enter balance',
            'balance.numeric' => 'Balance must be a number',
            'balance.min' => 'Balance must be greater than 0',
            'open_balance_date.required_unless' => 'Please enter open balance date',
        ];
    }
}
