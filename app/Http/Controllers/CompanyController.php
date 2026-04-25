<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Fetch the single company record and display the settings page.
     */
    public function index()
    {
        // Always retrieve the first record
        $company = Company::first();
        return view('admin.Company.index', compact('company'));
    }

    /**
     * Create or Update the company information.
     */
public function update(Request $request)
{
    // 1. Validation
    $request->validate([
        'name'            => 'required|string|max:255',
        'email'           => 'nullable|email|max:255',
        'mobile'          => 'nullable|string|max:20',
        'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'address'         => 'nullable|string',
    ]);

    try {
        $company = Company::first() ?? new Company();

        if ($request->hasFile('logo')) {
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $company->logo = $request->file('logo')->store('company_assets', 'public');
        }

        $company->name            = $request->name;
        $company->email           = $request->email;
        $company->mobile          = $request->mobile;
        $company->address         = $request->address;
        $company->save();

        // SUCCESS RESPONSE
        return response()->json([
            'success'  => true, // Boolean for easier JS check
            'status'   => 'success', // For SweetAlert or Toastr
            'message'  => 'Company information has been successfully updated.',
            'redirect' => route('company.index') // Redirect URL
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status'  => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}
}
