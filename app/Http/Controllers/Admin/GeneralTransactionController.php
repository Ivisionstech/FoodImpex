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
use App\Models\Expense;
use App\Models\Vendor;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GeneralTransactionController extends Controller
{
    private function isAdmin()
    {
        return auth()->user()->role == 'admin';
    }

    private function getAccountName($type, $account)
    {
        switch($type) {
            case 'customer': return $account->name ?? 'Customer';
            case 'vendor': return $account->company_name ?? 'Vendor';
            case 'bank': return $account->name ?? 'Bank';
            case 'cash': return 'Cash Account';
            case 'expense': return $account->name ?? 'Expense';
            default: return ucfirst($type) . ' Account';
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
            
            // Order by most recent first
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
            
            // Paginate with 10 entries per page
            $perPage = $request->get('per_page', 10);
            $entries = $query->paginate($perPage);
            
            return view('admin.pages.general.entries-list', compact('entries'));
            
        } catch (\Exception $e) {
            \Log::error('Error in generalEntriesList: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load entries: ' . $e->getMessage());
        }
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
            $expenses = Expense::orderBy('name')->get();
            
            return view('admin.pages.general.general-entry', compact('customers', 'vendors', 'banks', 'cash', 'expenses'));
            
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
        // Log incoming request for debugging
        \Log::info('Store General Entry Request:', $request->all());
        
        $request->validate([
            'transaction_date' => 'required|date',
            'account_ids' => 'required|array|min:1',
            'account_ids.*' => 'required|string',
            'debit_amounts' => 'required|array',
            'debit_amounts.*' => 'numeric|min:0',
            'credit_amounts' => 'required|array',
            'credit_amounts.*' => 'numeric|min:0',
            'descriptions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $date = $request->transaction_date;
            $accountIds = $request->account_ids;
            $debitAmounts = $request->debit_amounts;
            $creditAmounts = $request->credit_amounts;
            $descriptions = $request->descriptions ?? [];
            
            $totalDebit = array_sum(array_map('floatval', $debitAmounts));
            $totalCredit = array_sum(array_map('floatval', $creditAmounts));
            
            // Validate totals balance
            if ($totalDebit != $totalCredit) {
                throw new \Exception("Total Debit (PKR " . number_format($totalDebit, 2) . ") must equal Total Credit (PKR " . number_format($totalCredit, 2) . ")");
            }
            
            if ($totalDebit == 0 && $totalCredit == 0) {
                throw new \Exception("Please add at least one entry with amount");
            }
            
            $validTypes = ['customer', 'vendor', 'bank', 'cash', 'expense'];
            $userRole = auth()->user()->role;
            $isAdmin = ($userRole == 'admin');
            $approvalStatus = $isAdmin ? 'approved' : 'pending';
            
            $entriesCreated = 0;
            
            // Process each row
            for ($i = 0; $i < count($accountIds); $i++) {
                $accountId = $accountIds[$i];
                $debitAmount = floatval($debitAmounts[$i] ?? 0);
                $creditAmount = floatval($creditAmounts[$i] ?? 0);
                $description = $descriptions[$i] ?? '';
                
                if ($debitAmount == 0 && $creditAmount == 0) {
                    continue;
                }
                
                $parts = explode('_', $accountId);
                if (count($parts) != 2) {
                    throw new \Exception("Invalid account format at row " . ($i + 1));
                }
                
                $accountType = $parts[0];
                $accountIdValue = $parts[1];
                
                if (!in_array($accountType, $validTypes)) {
                    throw new \Exception("Invalid account type at row " . ($i + 1));
                }
                
                // Get account for validation
                $account = null;
                switch ($accountType) {
                    case 'customer': $account = Customer::findOrFail($accountIdValue); break;
                    case 'vendor': $account = Vendor::findOrFail($accountIdValue); break;
                    case 'bank': $account = Bank::findOrFail($accountIdValue); break;
                    case 'cash': $account = Cash::findOrFail($accountIdValue); break;
                    case 'expense': $account = Expense::findOrFail($accountIdValue); break;
                }
                
                $isCredit = $creditAmount > 0;
                $amount = $isCredit ? $creditAmount : $debitAmount;
                $accountName = $this->getAccountName($accountType, $account);
                
                // Check balance for credit accounts (excluding expenses) - only if admin (to show error immediately)
                if ($isAdmin && $isCredit && $accountType != 'expense') {
                    $creditBalance = 0;
                    switch ($accountType) {
                        case 'customer': $creditBalance = $account->balance; break;
                        case 'vendor': $creditBalance = $account->balance; break;
                        case 'bank': $creditBalance = $account->account_balance; break;
                        case 'cash': $creditBalance = $account->balance; break;
                    }
                    
                    if ($creditBalance < $amount) {
                        throw new \Exception("Insufficient balance in {$accountName}. Available: PKR " . number_format($creditBalance, 2));
                    }
                }
                
                // Generate full description
                $fullDescription = $description;
                if ($debitAmount > 0) {
                    $fullDescription .= ($fullDescription ? ' - ' : '') . "Debit entry for {$accountName}";
                } else {
                    $fullDescription .= ($fullDescription ? ' - ' : '') . "Credit entry for {$accountName}";
                }
                
                // Create Daybook entry
                if ($debitAmount > 0) {
                    // Debit entry (Money In)
                    Daybook::create([
                        'transaction_date' => $date,
                        'amount' => $amount,
                        'type' => 'transaction',
                        'approval_status' => $approvalStatus,
                        'credit_type' => null,
                        'credit_id' => null,
                        'debit_type' => $accountType,
                        'debit_id' => $accountIdValue,
                        'description' => $fullDescription,
                    ]);
                    $entriesCreated++;
                } else {
                    // Credit entry (Money Out)
                    Daybook::create([
                        'transaction_date' => $date,
                        'amount' => $amount,
                        'type' => 'transaction',
                        'approval_status' => $approvalStatus,
                        'credit_type' => $accountType,
                        'credit_id' => $accountIdValue,
                        'debit_type' => null,
                        'debit_id' => null,
                        'description' => $fullDescription,
                    ]);
                    $entriesCreated++;
                }
                
                // If admin, process immediately
                if ($isAdmin) {
                    if ($debitAmount > 0) {
                        // Process Debit (Money In)
                        switch ($accountType) {
                            case 'customer':
                                $account->increment('balance', $amount);
                                CustomerTransaction::create([
                                    'uuid' => (string) Str::uuid(),
                                    'customer_id' => $account->id,
                                    'transaction_date' => $date,
                                    'amount' => $amount,
                                    'type' => 'bill',
                                    'approval_status' => 'approved',
                                    'description' => $fullDescription,
                                    'current_balance' => $account->balance,
                                    'customer_bill_id' => null,
                                    'method' => 'general_entry',
                                ]);
                                break;
                            case 'vendor':
                                $account->increment('balance', $amount);
                                VendorTransaction::create([
                                    'uuid' => (string) Str::uuid(),
                                    'vendor_id' => $account->id,
                                    'date' => $date,
                                    'amount' => $amount,
                                    'type' => 'bill',
                                    'approval_status' => 'approved',
                                    'description' => $fullDescription,
                                    'current_balance' => $account->balance,
                                ]);
                                break;
                            case 'bank':
                                $account->increment('account_balance', $amount);
                                BankTransaction::create([
                                    'bank_id' => $account->id,
                                    'amount' => $amount,
                                    'balance' => $account->account_balance,
                                    'transaction_type' => 'credit',
                                    'description' => $fullDescription,
                                ]);
                                break;
                            case 'cash':
                                $account->increment('balance', $amount);
                                CashTransaction::create([
                                    'cash_id' => $account->id,
                                    'amount' => $amount,
                                    'balance' => $account->balance,
                                    'transaction_type' => 'credit',
                                    'description' => $fullDescription,
                                ]);
                                break;
                        }
                    } else {
                        // Process Credit (Money Out)
                        switch ($accountType) {
                            case 'customer':
                                $account->decrement('balance', $amount);
                                CustomerTransaction::create([
                                    'uuid' => (string) Str::uuid(),
                                    'customer_id' => $account->id,
                                    'transaction_date' => $date,
                                    'amount' => $amount,
                                    'type' => 'payment',
                                    'approval_status' => 'approved',
                                    'description' => $fullDescription,
                                    'current_balance' => $account->balance,
                                    'customer_bill_id' => null,
                                    'method' => 'general_entry',
                                ]);
                                break;
                            case 'vendor':
                                $account->decrement('balance', $amount);
                                VendorTransaction::create([
                                    'uuid' => (string) Str::uuid(),
                                    'vendor_id' => $account->id,
                                    'date' => $date,
                                    'amount' => $amount,
                                    'type' => 'payment',
                                    'approval_status' => 'approved',
                                    'description' => $fullDescription,
                                    'current_balance' => $account->balance,
                                    'send_via' => 'general_entry',
                                ]);
                                break;
                            case 'bank':
                                $account->decrement('account_balance', $amount);
                                BankTransaction::create([
                                    'bank_id' => $account->id,
                                    'amount' => $amount,
                                    'balance' => $account->account_balance,
                                    'transaction_type' => 'debit',
                                    'description' => $fullDescription,
                                ]);
                                break;
                            case 'cash':
                                $account->decrement('balance', $amount);
                                CashTransaction::create([
                                    'cash_id' => $account->id,
                                    'amount' => $amount,
                                    'balance' => $account->balance,
                                    'transaction_type' => 'debit',
                                    'description' => $fullDescription,
                                ]);
                                break;
                        }
                    }
                }
            }
            
            DB::commit();
            
            \Log::info('Entries created: ' . $entriesCreated);
            
            if ($isAdmin) {
                return redirect()->route('general-transactions.entries-list')
                    ->with('success', $entriesCreated . ' journal entry(s) created and approved successfully!');
            } else {
                return redirect()->route('general-transactions.entries-list')
                    ->with('success', $entriesCreated . ' journal entry(s) created successfully! Awaiting admin approval.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store General Entry Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create journal entry: ' . $e->getMessage());
        }
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
            $description = $entry->description;
            
            // Process Credit Account (Money Out)
            if ($creditType && $creditId) {
                $creditAccount = null;
                switch ($creditType) {
                    case 'customer':
                        $creditAccount = Customer::findOrFail($creditId);
                        $creditAccount->decrement('balance', $amount);
                        CustomerTransaction::create([
                            'uuid' => (string) Str::uuid(),
                            'customer_id' => $creditAccount->id,
                            'transaction_date' => $date,
                            'amount' => $amount,
                            'type' => 'payment',
                            'approval_status' => 'approved',
                            'description' => $description,
                            'current_balance' => $creditAccount->balance,
                            'customer_bill_id' => null,
                            'method' => 'general_entry',
                        ]);
                        break;
                    case 'vendor':
                        $creditAccount = Vendor::findOrFail($creditId);
                        $creditAccount->decrement('balance', $amount);
                        VendorTransaction::create([
                            'uuid' => (string) Str::uuid(),
                            'vendor_id' => $creditAccount->id,
                            'date' => $date,
                            'amount' => $amount,
                            'type' => 'payment',
                            'approval_status' => 'approved',
                            'description' => $description,
                            'current_balance' => $creditAccount->balance,
                            'send_via' => 'general_entry',
                        ]);
                        break;
                    case 'bank':
                        $creditAccount = Bank::findOrFail($creditId);
                        $creditAccount->decrement('account_balance', $amount);
                        BankTransaction::create([
                            'bank_id' => $creditAccount->id,
                            'amount' => $amount,
                            'balance' => $creditAccount->account_balance,
                            'transaction_type' => 'debit',
                            'description' => $description,
                        ]);
                        break;
                    case 'cash':
                        $creditAccount = Cash::findOrFail($creditId);
                        $creditAccount->decrement('balance', $amount);
                        CashTransaction::create([
                            'cash_id' => $creditAccount->id,
                            'amount' => $amount,
                            'balance' => $creditAccount->balance,
                            'transaction_type' => 'debit',
                            'description' => $description,
                        ]);
                        break;
                    case 'expense':
                        // Expenses don't have balance to update
                        break;
                }
            }
            
            // Process Debit Account (Money In)
            if ($debitType && $debitId) {
                $debitAccount = null;
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
                            'approval_status' => 'approved',
                            'description' => $description,
                            'current_balance' => $debitAccount->balance,
                            'customer_bill_id' => null,
                            'method' => 'general_entry',
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
                            'approval_status' => 'approved',
                            'description' => $description,
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
                            'description' => $description,
                        ]);
                        break;
                    case 'cash':
                        $debitAccount = Cash::findOrFail($debitId);
                        $debitAccount->increment('balance', $amount);
                        CashTransaction::create([
                            'cash_id' => $debitAccount->id,
                            'amount' => $amount,
                            'balance' => $debitAccount->balance,
                            'transaction_type' => 'credit',
                            'description' => $description,
                        ]);
                        break;
                    case 'expense':
                        // Expenses don't have balance to update
                        break;
                }
            }
            
            // Update entry status to approved
            $entry->approval_status = 'approved';
            $entry->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Entry approved and transactions processed successfully.'
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
     * Get entry details for modal view
     */
    public function getEntryDetails($id)
    {
        try {
            $entry = Daybook::findOrFail($id);
            
            // Get account names
            $creditAccountName = '-';
            $debitAccountName = '-';
            
            if ($entry->credit_type && $entry->credit_id) {
                switch ($entry->credit_type) {
                    case 'customer':
                        $account = Customer::find($entry->credit_id);
                        $creditAccountName = $account ? $account->name . ' (Customer)' : '-';
                        break;
                    case 'vendor':
                        $account = Vendor::find($entry->credit_id);
                        $creditAccountName = $account ? $account->company_name . ' (Vendor)' : '-';
                        break;
                    case 'bank':
                        $account = Bank::find($entry->credit_id);
                        $creditAccountName = $account ? $account->name . ' (Bank)' : '-';
                        break;
                    case 'cash':
                        $creditAccountName = 'Cash Account';
                        break;
                    case 'expense':
                        $account = Expense::find($entry->credit_id);
                        $creditAccountName = $account ? $account->name . ' (Expense)' : '-';
                        break;
                }
            }
            
            if ($entry->debit_type && $entry->debit_id) {
                switch ($entry->debit_type) {
                    case 'customer':
                        $account = Customer::find($entry->debit_id);
                        $debitAccountName = $account ? $account->name . ' (Customer)' : '-';
                        break;
                    case 'vendor':
                        $account = Vendor::find($entry->debit_id);
                        $debitAccountName = $account ? $account->company_name . ' (Vendor)' : '-';
                        break;
                    case 'bank':
                        $account = Bank::find($entry->debit_id);
                        $debitAccountName = $account ? $account->name . ' (Bank)' : '-';
                        break;
                    case 'cash':
                        $debitAccountName = 'Cash Account';
                        break;
                    case 'expense':
                        $account = Expense::find($entry->debit_id);
                        $debitAccountName = $account ? $account->name . ' (Expense)' : '-';
                        break;
                }
            }
            
            $html = '
                <div class="p-3">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bx bx-transfer-alt fs-2 me-2 text-info"></i>
                        <h6 class="mb-0">Entry Details</h6>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Entry ID:</strong> 
                            <span class="badge bg-label-info">#' . $entry->id . '</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date & Time:</strong> 
                            ' . date('d-M-Y h:i A', strtotime($entry->transaction_date)) . '
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Amount:</strong> 
                            <span class="fw-bold">PKR ' . number_format($entry->amount, 2) . '</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong> 
                            ' . ($entry->approval_status == 'approved' 
                                ? '<span class="badge bg-success">Approved</span>' 
                                : '<span class="badge bg-warning">Pending</span>') . '
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Credit Account:</strong> 
                            ' . $creditAccountName . '
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Debit Account:</strong> 
                            ' . $debitAccountName . '
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Description:</strong> 
                            <p class="mt-1 mb-0">' . nl2br(e($entry->description)) . '</p>
                        </div>
                        <div class="col-12">
                            <strong>Created At:</strong> 
                            ' . date('d-M-Y h:i A', strtotime($entry->created_at)) . '
                        </div>
                    </div>
                </div>
            ';
            
            return response()->json(['html' => $html]);
            
        } catch (\Exception $e) {
            return response()->json(['html' => '<div class="alert alert-danger">Failed to load details: ' . $e->getMessage() . '</div>'], 500);
        }
    }
}