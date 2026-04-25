<?php

namespace App\Http\Requests\Customer;

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
            'person_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'open_balance_date' => 'required_unless:balance,0|nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter customer name.',
            'person_name.string' => 'Please enter a valid person name.',
            'phone.string' => 'Please enter a valid phone number.',
            'phone.max' => 'Please enter a valid phone number.',
            'phone.min' => 'Please enter a valid phone number.',
            'email.email' => 'Please enter a valid email address.',
            'balance.numeric' => 'Please enter a valid balance.',
            'balance.min' => 'Please enter a valid balance.',
            'open_balance_date.required_unless' => 'Please enter open balance date.',
        ];
    }
}
