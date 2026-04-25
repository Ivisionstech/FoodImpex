<?php

namespace App\Providers;

use App\Models\Bank;
use App\Models\Cash;
use App\Models\CustomerBill;
use App\Models\Expense;
use App\Models\Company;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade; // Added for Blade directives

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- Step 1: Super Admin Gate Logic ---
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // --- Step 2: Blade Directives for Role Checks ---
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->role == 'admin';
        });
        
        Blade::if('accountant', function () {
            return auth()->check() && auth()->user()->role == 'accountant';
        });
        
        Blade::if('canApprove', function () {
            return auth()->check() && auth()->user()->role == 'admin';
        });
        
        Blade::if('canDelete', function () {
            return auth()->check() && auth()->user()->role == 'admin';
        });
        
        Blade::if('canEdit', function () {
            return auth()->check() && (auth()->user()->role == 'admin' || auth()->user()->role == 'accountant');
        });

        // Aapka existing logic
        if (!app()->runningInConsole()) {

            // --- NAYA LOGIC: Company Settings Sidebar ke liye ---
            if (Schema::hasTable('companies')) {
                $companySettings = Company::first();
                View::share('companySettings', $companySettings);
            }

            // --- AAPKA PURANA LOGIC ---
            if (Schema::hasTable('banks')) {
                $banks = Bank::all();
                View::share('banks', $banks);
            }

            if (Schema::hasTable('cashes')) {
                $cash = Cash::first();
                View::share('cash', $cash);
            }

            if (Schema::hasTable('customer_bills')) {
                $totalProfit = CustomerBill::sum('profit');
                View::share('totalProfit', $totalProfit);
            }

            if (Schema::hasTable('expenses')) {
                $totalExpenses = Expense::sum('amount');
                View::share('totalExpenses', $totalExpenses);

                $currentProfit = ($totalProfit ?? 0) - $totalExpenses;
                View::share('currentProfit', $currentProfit);
            }
        }
       
    }
}