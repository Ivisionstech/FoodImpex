<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Daybook;
use App\Models\Vendor;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GeneralTransactionController extends Controller
{
    /**
     * Display the main general transactions page
     */
    public function index()
    {
        return view('admin.pages.general.index');
    }

    /**
     * Get formatted account name for description
     */
    private function getAccountName($type, $account)
    {
        switch($type) {
            case 'customer':
                return $account->name ?? 'Customer';
            case 'vendor':
                return $account->company_name ?? 'Vendor';
            case 'bank':
                return $account->name ?? 'Bank';
            default:
                return ucfirst($type) . ' Account';
        }
    }

    /**
     * Generate a clean description for the transaction
     */
    private function generateDescription($prefix, $creditType, $creditAccount, $debitType, $debitAccount, $amount = null)
    {
        $creditName = $this->getAccountName($creditType, $creditAccount);
        $debitName = $this->getAccountName($debitType, $debitAccount);
        
        $description = "Transfer from {$creditName} to {$debitName}";
        
        if ($prefix && !empty(trim($prefix))) {
            $description = $prefix . ' - ' . $description;
        }
        
        if ($amount) {
            $description .= ' (PKR ' . number_format($amount, 2) . ')';
        }
        
        return $description;
    }

    /**
     * Display the general entry form
     */
    public function generalEntry()
    {
        try {
            // Fetch all active customers
            $customers = Customer::where('active', 1)
                ->orderBy('name')
                ->get();
            
            // Fetch all active vendors
            $vendors = Vendor::where('active', 1)
                ->orderBy('company_name')
                ->get();
            
            // Fetch all banks
            $banks = Bank::orderBy('name')
                ->get();
            
            // Fetch cash record
            $cash = Cash::first();
            
            return view('admin.pages.general.general-entry', compact('customers', 'vendors', 'banks', 'cash'));
            
        } catch (\Exception $e) {
            \Log::error('Error in generalEntry: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load form: ' . $e->getMessage());
        }
    }

    /**
     * Store a new general entry
     */
    public function storeGeneralEntry(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'credit_id' => 'required|string',
            'debit_id' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $date = $request->transaction_date;
            $amount = $request->amount;
            $userDescription = $request->description ?? '';

            // Parse credit account (format: "type_id" e.g., "customer_1")
            $creditParts = explode('_', $request->credit_id);
            if (count($creditParts) != 2) {
                throw new \Exception("Invalid credit account format");
            }
            $creditType = $creditParts[0];
            $creditId = $creditParts[1];

            // Parse debit account (format: "type_id" e.g., "vendor_2")
            $debitParts = explode('_', $request->debit_id);
            if (count($debitParts) != 2) {
                throw new \Exception("Invalid debit account format");
            }
            $debitType = $debitParts[0];
            $debitId = $debitParts[1];

            // Validate account types
            $validTypes = ['customer', 'vendor', 'bank'];
            if (!in_array($creditType, $validTypes) || !in_array($debitType, $validTypes)) {
                throw new \Exception("Invalid account type. Must be customer, vendor, or bank.");
            }

            // Initialize account variables
            $creditAccount = null;
            $debitAccount = null;

            // Process Credit Account (Money Out - Decrease balance)
            switch ($creditType) {
                case 'customer':
                    $creditAccount = Customer::findOrFail($creditId);
                    if ($creditAccount->balance < $amount) {
                        throw new \Exception("Customer {$creditAccount->name} has insufficient balance. Available: PKR " . number_format($creditAccount->balance, 2));
                    }
                    $creditAccount->decrement('balance', $amount);
                    
                    CustomerTransaction::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_id' => $creditAccount->id,
                        'transaction_date' => $date,
                        'amount' => $amount,
                        'type' => 'payment',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                        'current_balance' => $creditAccount->balance,
                    ]);
                    break;

                case 'vendor':
                    $creditAccount = Vendor::findOrFail($creditId);
                    if ($creditAccount->balance < $amount) {
                        throw new \Exception("Vendor {$creditAccount->company_name} has insufficient balance. Available: PKR " . number_format($creditAccount->balance, 2));
                    }
                    $creditAccount->decrement('balance', $amount);
                    
                    VendorTransaction::create([
                        'uuid' => (string) Str::uuid(),
                        'vendor_id' => $creditAccount->id,
                        'date' => $date,
                        'amount' => $amount,
                        'type' => 'payment',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                        'current_balance' => $creditAccount->balance,
                    ]);
                    break;

                case 'bank':
                    $creditAccount = Bank::findOrFail($creditId);
                    if ($creditAccount->account_balance < $amount) {
                        throw new \Exception("Bank {$creditAccount->name} has insufficient balance. Available: PKR " . number_format($creditAccount->account_balance, 2));
                    }
                    $creditAccount->decrement('account_balance', $amount);
                    
                    BankTransaction::create([
                        'bank_id' => $creditAccount->id,
                        'amount' => $amount,
                        'balance' => $creditAccount->account_balance,
                        'transaction_type' => 'debit',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                    ]);
                    break;
            }

            // Process Debit Account (Money In - Increase balance)
            switch ($debitType) {
                case 'customer':
                    $debitAccount = Customer::findOrFail($debitId);
                    $debitAccount->increment('balance', $amount);
                    
                    CustomerTransaction::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_id' => $debitAccount->id,
                        'transaction_date' => $date,
                        'amount' => $amount,
                        'type' => 'bill',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                        'current_balance' => $debitAccount->balance,
                    ]);
                    break;

                case 'vendor':
                    $debitAccount = Vendor::findOrFail($debitId);
                    $debitAccount->increment('balance', $amount);
                    
                    VendorTransaction::create([
                        'uuid' => (string) Str::uuid(),
                        'vendor_id' => $debitAccount->id,
                        'date' => $date,
                        'amount' => $amount,
                        'type' => 'bill',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                        'current_balance' => $debitAccount->balance,
                    ]);
                    break;

                case 'bank':
                    $debitAccount = Bank::findOrFail($debitId);
                    $debitAccount->increment('account_balance', $amount);
                    
                    BankTransaction::create([
                        'bank_id' => $debitAccount->id,
                        'amount' => $amount,
                        'balance' => $debitAccount->account_balance,
                        'transaction_type' => 'credit',
                        'description' => $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount),
                    ]);
                    break;
            }

            // Create daybook entry with clean description
            $daybookDescription = $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount);
            
            Daybook::create([
                'transaction_date' => $date,
                'amount' => $amount,
                'type' => 'transaction',
                'description' => $daybookDescription,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.general-entry')
                ->with('success', 'General entry created successfully. Description: ' . $daybookDescription);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create general entry: ' . $e->getMessage());
        }
    }

    /**
     * Get accounts for AJAX request
     */
    public function getAccounts(Request $request)
    {
        $type = $request->type;
        $accounts = [];
        
        switch($type) {
            case 'customer':
                $accounts = Customer::select('id', 'name', 'phone', 'email', 'address', 'balance')
                    ->where('active', 1)
                    ->orderBy('name')
                    ->get()
                    ->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'display_name' => $customer->name . ($customer->phone ? ' - ' . $customer->phone : ''),
                            'balance' => $customer->balance,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'address' => $customer->address,
                            'type' => 'customer'
                        ];
                    });
                break;
                
            case 'vendor':
                $accounts = Vendor::select('id', 'company_name', 'person_name', 'phone', 'email', 'address', 'balance')
                    ->where('active', 1)
                    ->orderBy('company_name')
                    ->get()
                    ->map(function($vendor) {
                        return [
                            'id' => $vendor->id,
                            'name' => $vendor->company_name,
                            'display_name' => $vendor->company_name . ($vendor->person_name ? ' (' . $vendor->person_name . ')' : ''),
                            'balance' => $vendor->balance,
                            'phone' => $vendor->phone,
                            'email' => $vendor->email,
                            'address' => $vendor->address,
                            'type' => 'vendor'
                        ];
                    });
                break;
                
            case 'bank':
                $accounts = Bank::select('id', 'name', 'account_title', 'account_number', 'account_balance as balance')
                    ->orderBy('name')
                    ->get()
                    ->map(function($bank) {
                        return [
                            'id' => $bank->id,
                            'name' => $bank->name,
                            'display_name' => $bank->name . ' - ' . $bank->account_title . ' (A/C: ' . $bank->account_number . ')',
                            'balance' => $bank->balance,
                            'account_title' => $bank->account_title,
                            'account_number' => $bank->account_number,
                            'type' => 'bank'
                        ];
                    });
                break;
                
            case 'cash':
                $cash = Cash::first();
                $accounts = [
                    [
                        'id' => 1,
                        'name' => 'Main Cash',
                        'display_name' => 'Cash Account - Main Cash',
                        'balance' => $cash ? $cash->balance : 0,
                        'type' => 'cash'
                    ]
                ];
                break;
        }
        
        return response()->json($accounts);
    }

    /**
     * Display list of general entries
     */
    public function generalEntriesList()
    {
        try {
            $entries = Daybook::where('type', 'transaction')
                ->orderBy('transaction_date', 'desc')
                ->paginate(20);
            
            return view('admin.pages.general.entries-list', compact('entries'));
            
        } catch (\Exception $e) {
            \Log::error('Error in generalEntriesList: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load entries: ' . $e->getMessage());
        }
    }

    public function customerToVendorForm()
    {
        $customers = Customer::all();
        $vendors = Vendor::all();
        return view('admin.pages.general.customer-to-vendor', compact('customers', 'vendors'));
    }

    /**
     * Process customer to vendor transfer
     */
    public function customerToVendorTransfer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);
            $vendor = Vendor::findOrFail($request->vendor_id);

            // Check if customer has sufficient balance
            if ($customer->balance < $request->amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Customer does not have sufficient balance. Available: PKR ' . number_format($customer->balance, 2));
            }

            // Decrease customer balance
            $customer->decrement('balance', $request->amount);

            // Decrease vendor balance
            $vendor->decrement('balance', $request->amount);

            $description = $request->description 
                ? $request->description . ' - Transfer from ' . $customer->name . ' to ' . $vendor->company_name
                : 'Transfer from ' . $customer->name . ' to ' . $vendor->company_name;

            // Create customer transaction
            CustomerTransaction::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'payment',
                'description' => $description,
                'current_balance' => $customer->balance,
            ]);

            // Create vendor transaction
            VendorTransaction::create([
                'uuid' => (string) Str::uuid(),
                'vendor_id' => $vendor->id,
                'date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'payment',
                'description' => $description,
                'current_balance' => $vendor->balance,
            ]);

            // Create daybook entry
            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Transfer completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Show bank to bank transfer form
     */
    public function bankToBankForm()
    {
        $banks = Bank::all();
        return view('admin.pages.general.bank-to-bank', compact('banks'));
    }

    /**
     * Process bank to bank transfer
     */
    public function bankToBankTransfer(Request $request)
    {
        $request->validate([
            'from_bank_id' => 'required|exists:banks,id',
            'to_bank_id' => 'required|exists:banks,id|different:from_bank_id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $fromBank = Bank::findOrFail($request->from_bank_id);
            $toBank = Bank::findOrFail($request->to_bank_id);

            if ($fromBank->account_balance < $request->amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient balance in source bank. Available: PKR ' . number_format($fromBank->account_balance, 2));
            }

            $fromBank->decrement('account_balance', $request->amount);
            $toBank->increment('account_balance', $request->amount);

            $description = $request->description 
                ? $request->description . ' - Bank transfer from ' . $fromBank->name . ' to ' . $toBank->name
                : 'Bank transfer from ' . $fromBank->name . ' to ' . $toBank->name;

            BankTransaction::create([
                'bank_id' => $fromBank->id,
                'amount' => $request->amount,
                'balance' => $fromBank->account_balance,
                'transaction_type' => 'debit',
                'description' => $description,
            ]);

            BankTransaction::create([
                'bank_id' => $toBank->id,
                'amount' => $request->amount,
                'balance' => $toBank->account_balance,
                'transaction_type' => 'credit',
                'description' => $description,
            ]);

            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Bank transfer completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Show bank withdrawal form
     */
    public function bankWithdrawForm()
    {
        $banks = Bank::all();
        $cash = Cash::first();
        return view('admin.pages.general.bank-withdraw', compact('banks', 'cash'));
    }

    /**
     * Process bank withdrawal
     */
    public function bankWithdraw(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $bank = Bank::findOrFail($request->bank_id);
            $cash = Cash::first();

            if (!$cash) {
                $cash = Cash::create([
                    'uuid' => (string) Str::uuid(),
                    'balance' => 0,
                ]);
            }

            if ($bank->account_balance < $request->amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient balance in bank. Available: PKR ' . number_format($bank->account_balance, 2));
            }

            $bank->decrement('account_balance', $request->amount);
            $cash->increment('balance', $request->amount);

            $description = $request->description 
                ? $request->description . ' - Cash withdrawal from ' . $bank->name
                : 'Cash withdrawal from ' . $bank->name;

            BankTransaction::create([
                'bank_id' => $bank->id,
                'amount' => $request->amount,
                'balance' => $bank->account_balance,
                'transaction_type' => 'debit',
                'description' => $description,
            ]);

            CashTransaction::create([
                'cash_id' => $cash->id,
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'transaction_type' => 'credit',
                'description' => $description,
            ]);

            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Withdrawal completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }

    /**
     * Show bank deposit form
     */
    public function bankDepositForm()
    {
        $banks = Bank::all();
        $cash = Cash::first();
        return view('admin.pages.general.bank-deposit', compact('banks', 'cash'));
    }

    /**
     * Process bank deposit
     */
    public function bankDeposit(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $bank = Bank::findOrFail($request->bank_id);
            $cash = Cash::first();

            if (!$cash || $cash->balance < $request->amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient cash balance. Available: PKR ' . number_format($cash ? $cash->balance : 0, 2));
            }

            $cash->decrement('balance', $request->amount);
            $bank->increment('account_balance', $request->amount);

            $description = $request->description 
                ? $request->description . ' - Cash deposit to ' . $bank->name
                : 'Cash deposit to ' . $bank->name;

            CashTransaction::create([
                'cash_id' => $cash->id,
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'transaction_type' => 'debit',
                'description' => $description,
            ]);

            BankTransaction::create([
                'bank_id' => $bank->id,
                'amount' => $request->amount,
                'balance' => $bank->account_balance,
                'transaction_type' => 'credit',
                'description' => $description,
            ]);

            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Deposit completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Deposit failed: ' . $e->getMessage());
        }
    }
}