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
     * Check if current user is Admin
     */
    private function isAdmin()
    {
        return auth()->user()->role == 'admin';
    }

    /**
     * Approve a pending general entry (Admin only)
     */
    public function approveEntry($id)
{
    if (!$this->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Access Denied. Only Admin can approve entries.'
        ], 403);
    }

    try {
        DB::beginTransaction();
        
        $entry = Daybook::findOrFail($id);
        
        if ($entry->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Entry already approved.'
            ], 400);
        }
        
        $amount = $entry->amount;
        $date = $entry->transaction_date;
        $creditType = $entry->credit_type;
        $creditId = $entry->credit_id;
        $debitType = $entry->debit_type;
        $debitId = $entry->debit_id;
        
        // Get credit account (Money Out)
        $creditAccount = null;
        switch ($creditType) {
            case 'customer':
                $creditAccount = Customer::findOrFail($creditId);
                break;
            case 'vendor':
                $creditAccount = Vendor::findOrFail($creditId);
                break;
            case 'bank':
                $creditAccount = Bank::findOrFail($creditId);
                break;
        }
        
        // Get debit account (Money In)
        $debitAccount = null;
        switch ($debitType) {
            case 'customer':
                $debitAccount = Customer::findOrFail($debitId);
                break;
            case 'vendor':
                $debitAccount = Vendor::findOrFail($debitId);
                break;
            case 'bank':
                $debitAccount = Bank::findOrFail($debitId);
                break;
        }
        
        // Check credit account balance
        $creditBalance = 0;
        switch ($creditType) {
            case 'customer':
                $creditBalance = $creditAccount->balance;
                break;
            case 'vendor':
                $creditBalance = $creditAccount->balance;
                break;
            case 'bank':
                $creditBalance = $creditAccount->account_balance;
                break;
        }
        
        if ($creditBalance < $amount) {
            throw new \Exception("Insufficient balance in credit account. Available: PKR " . number_format($creditBalance, 2));
        }
        
        // Process Credit Account (Money Out)
        switch ($creditType) {
            case 'customer':
                $creditAccount->decrement('balance', $amount);
                CustomerTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $creditAccount->id,
                    'transaction_date' => $date,
                    'amount' => $amount,
                    'type' => 'payment',
                    'approval_status' => 'approved',
                    'description' => $entry->description,
                    'current_balance' => $creditAccount->balance,
                ]);
                break;
                
            case 'vendor':
                $creditAccount->decrement('balance', $amount);
                VendorTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'vendor_id' => $creditAccount->id,
                    'date' => $date,
                    'amount' => $amount,
                    'type' => 'payment',
                    'approval_status' => 'approved',
                    'description' => $entry->description,
                    'current_balance' => $creditAccount->balance,
                ]);
                break;
                
            case 'bank':
                $creditAccount->decrement('account_balance', $amount);
                BankTransaction::create([
                    'bank_id' => $creditAccount->id,
                    'amount' => $amount,
                    'balance' => $creditAccount->account_balance,
                    'transaction_type' => 'debit',
                    'description' => $entry->description,
                ]);
                break;
        }
        
        // Process Debit Account (Money In)
        switch ($debitType) {
            case 'customer':
                $debitAccount->increment('balance', $amount);
                CustomerTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $debitAccount->id,
                    'transaction_date' => $date,
                    'amount' => $amount,
                    'type' => 'bill',
                    'approval_status' => 'approved',
                    'description' => $entry->description,
                    'current_balance' => $debitAccount->balance,
                ]);
                break;
                
            case 'vendor':
                $debitAccount->increment('balance', $amount);
                VendorTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'vendor_id' => $debitAccount->id,
                    'date' => $date,
                    'amount' => $amount,
                    'type' => 'bill',
                    'approval_status' => 'approved',
                    'description' => $entry->description,
                    'current_balance' => $debitAccount->balance,
                ]);
                break;
                
            case 'bank':
                $debitAccount->increment('account_balance', $amount);
                BankTransaction::create([
                    'bank_id' => $debitAccount->id,
                    'amount' => $amount,
                    'balance' => $debitAccount->account_balance,
                    'transaction_type' => 'credit',
                    'description' => $entry->description,
                ]);
                break;
        }
        
        // Update entry status to approved
        $entry->approval_status = 'approved';
        $entry->save();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Entry approved and transfer completed successfully.'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Approve Entry Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Approval failed: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Display the main general transactions page
     */
    public function index()
    {
        $pendingCount = Daybook::where('type', 'transaction')
            ->where('approval_status', 'pending')
            ->count();
            
        return view('admin.pages.general.index', compact('pendingCount'));
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
            $customers = Customer::where('active', 1)->orderBy('name')->get();
            $vendors = Vendor::where('active', 1)->orderBy('company_name')->get();
            $banks = Bank::orderBy('name')->get();
            $cash = Cash::first();
            
            return view('admin.pages.general.general-entry', compact('customers', 'vendors', 'banks', 'cash'));
            
        } catch (\Exception $e) {
            \Log::error('Error in generalEntry: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load form: ' . $e->getMessage());
        }
    }

    /**
     * Store a new general entry (PENDING APPROVAL - No balance updates)
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

        // Parse credit account
        $creditParts = explode('_', $request->credit_id);
        if (count($creditParts) != 2) {
            throw new \Exception("Invalid credit account format");
        }
        $creditType = $creditParts[0];
        $creditId = $creditParts[1];

        // Parse debit account
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

        // Get accounts for validation only (NO BALANCE UPDATE YET)
        $creditAccount = null;
        $debitAccount = null;

        switch ($creditType) {
            case 'customer':
                $creditAccount = Customer::findOrFail($creditId);
                break;
            case 'vendor':
                $creditAccount = Vendor::findOrFail($creditId);
                break;
            case 'bank':
                $creditAccount = Bank::findOrFail($creditId);
                break;
        }

        switch ($debitType) {
            case 'customer':
                $debitAccount = Customer::findOrFail($debitId);
                break;
            case 'vendor':
                $debitAccount = Vendor::findOrFail($debitId);
                break;
            case 'bank':
                $debitAccount = Bank::findOrFail($debitId);
                break;
        }

        // Check if credit account has sufficient balance
        $creditBalance = 0;
        switch ($creditType) {
            case 'customer':
                $creditBalance = $creditAccount->balance;
                break;
            case 'vendor':
                $creditBalance = $creditAccount->balance;
                break;
            case 'bank':
                $creditBalance = $creditAccount->account_balance;
                break;
        }
        
        if ($creditBalance < $amount) {
            throw new \Exception("Insufficient balance in credit account. Available: PKR " . number_format($creditBalance, 2));
        }

        // Generate description
        $daybookDescription = $this->generateDescription($userDescription, $creditType, $creditAccount, $debitType, $debitAccount, $amount);

        // Create daybook entry with PENDING status and store account details
        Daybook::create([
            'transaction_date' => $date,
            'amount' => $amount,
            'type' => 'transaction',
            'approval_status' => 'pending',
            'credit_type' => $creditType,
            'credit_id' => $creditId,
            'debit_type' => $debitType,
            'debit_id' => $debitId,
            'description' => $daybookDescription,
        ]);

        DB::commit();

        return redirect()->route('general-transactions.general-entry')
            ->with('success', 'General entry created successfully! Awaiting admin approval.');

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
     * Display list of general entries with status filter
     */
    public function generalEntriesList(Request $request)
{
    try {
        $query = Daybook::where('type', 'transaction');
        
        // Apply date filters
        if ($request->from_date) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        
        // Apply approval status filter
        if ($request->approval_status && in_array($request->approval_status, ['pending', 'approved'])) {
            $query->where('approval_status', $request->approval_status);
        }
        
        $entries = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);
        
        return view('admin.pages.general.entries-list', compact('entries'));
        
    } catch (\Exception $e) {
        \Log::error('Error in generalEntriesList: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to load entries: ' . $e->getMessage());
    }
}
    /**
     * Process customer to vendor transfer (PENDING APPROVAL)
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

            $description = $request->description 
                ? $request->description . ' - Transfer from ' . $customer->name . ' to ' . $vendor->company_name
                : 'Transfer from ' . $customer->name . ' to ' . $vendor->company_name;

            // Create pending daybook entry (NO BALANCE UPDATES YET)
            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'approval_status' => 'pending',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Transfer request created successfully! Awaiting admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Process bank to bank transfer (PENDING APPROVAL)
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

            $description = $request->description 
                ? $request->description . ' - Bank transfer from ' . $fromBank->name . ' to ' . $toBank->name
                : 'Bank transfer from ' . $fromBank->name . ' to ' . $toBank->name;

            // Create pending daybook entry (NO BALANCE UPDATES YET)
            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'approval_status' => 'pending',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Bank transfer request created successfully! Awaiting admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Process bank withdrawal (PENDING APPROVAL)
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

            $description = $request->description 
                ? $request->description . ' - Cash withdrawal from ' . $bank->name
                : 'Cash withdrawal from ' . $bank->name;

            // Create pending daybook entry (NO BALANCE UPDATES YET)
            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'approval_status' => 'pending',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Withdrawal request created successfully! Awaiting admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }

    /**
     * Process bank deposit (PENDING APPROVAL)
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

            $description = $request->description 
                ? $request->description . ' - Cash deposit to ' . $bank->name
                : 'Cash deposit to ' . $bank->name;

            // Create pending daybook entry (NO BALANCE UPDATES YET)
            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'transaction',
                'approval_status' => 'pending',
                'description' => $description,
            ]);

            DB::commit();

            return redirect()->route('general-transactions.index')
                ->with('success', 'Deposit request created successfully! Awaiting admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Deposit failed: ' . $e->getMessage());
        }
    }

    // Keep the form methods as they are (customerToVendorForm, bankToBankForm, bankWithdrawForm, bankDepositForm)
    public function customerToVendorForm()
    {
        $customers = Customer::all();
        $vendors = Vendor::all();
        return view('admin.pages.general.customer-to-vendor', compact('customers', 'vendors'));
    }

    public function bankToBankForm()
    {
        $banks = Bank::all();
        return view('admin.pages.general.bank-to-bank', compact('banks'));
    }

    public function bankWithdrawForm()
    {
        $banks = Bank::all();
        $cash = Cash::first();
        return view('admin.pages.general.bank-withdraw', compact('banks', 'cash'));
    }

    public function bankDepositForm()
    {
        $banks = Bank::all();
        $cash = Cash::first();
        return view('admin.pages.general.bank-deposit', compact('banks', 'cash'));
    }
}