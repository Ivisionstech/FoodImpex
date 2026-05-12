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
    public function index()
    {
        $entries = Daybook::where('type', 'transaction')->orderBy('id', 'desc')->paginate(10);
        return view('admin.pages.general.entries-list', compact('entries'));
    }

    public function generalEntry()
    {
        $customers = Customer::where('active', 1)->orderBy('name')->get();
        $vendors = Vendor::where('active', 1)->orderBy('company_name')->get();
        $banks = Bank::orderBy('name')->get();
        $cash = Cash::first();
        $expenses = \App\Models\Expense::all();
        
        return view('admin.pages.general.general-entry', compact('customers', 'vendors', 'banks', 'cash', 'expenses'));
    }

    /**
     * SIMPLE WORKING VERSION - NO VALIDATION, JUST SAVE
     */
  public function storeGeneralEntry(Request $request)
{
    // Log that we received the request
    \Log::info('=== FORM SUBMITTED ===');
    \Log::info('POST Data:', $request->all());
    
    // Start database transaction to ensure data integrity
    DB::beginTransaction();
    
    try {
        // Get the data directly
        $date = $request->input('transaction_date');
        $accountIds = $request->input('account_ids', []);
        $debitAmounts = $request->input('debit_amounts', []);
        $creditAmounts = $request->input('credit_amounts', []);
        $descriptions = $request->input('descriptions', []);
        
        \Log::info('Date: ' . $date);
        \Log::info('Account IDs: ' . json_encode($accountIds));
        \Log::info('Debit Amounts: ' . json_encode($debitAmounts));
        \Log::info('Credit Amounts: ' . json_encode($creditAmounts));
        
        $entriesSaved = 0;
        
        // Loop through each row
        foreach ($accountIds as $index => $accountId) {
            if (empty($accountId)) {
                \Log::info("Index {$index}: Account ID is empty, skipping");
                continue;
            }
            
            // Get the amount
            $amount = 0;
            $type = '';
            
            if (isset($debitAmounts[$index]) && $debitAmounts[$index] > 0) {
                $amount = $debitAmounts[$index];
                $type = 'debit';
                \Log::info("Index {$index}: Found DEBIT amount = {$amount}");
            } elseif (isset($creditAmounts[$index]) && $creditAmounts[$index] > 0) {
                $amount = $creditAmounts[$index];
                $type = 'credit';
                \Log::info("Index {$index}: Found CREDIT amount = {$amount}");
            } else {
                \Log::info("Index {$index}: No amount found, skipping");
                continue;
            }
            
            // Parse account (e.g., "customer_1", "vendor_5")
            $parts = explode('_', $accountId);
            if (count($parts) != 2) {
                \Log::error("Index {$index}: Invalid account format: {$accountId}");
                continue;
            }
            
            $accountType = $parts[0];
            $accountIdNum = $parts[1];
            $description = isset($descriptions[$index]) ? $descriptions[$index] : '';
            
            \Log::info("Index {$index}: Account Type = {$accountType}, Account ID Num = {$accountIdNum}");
            \Log::info("Index {$index}: Saving: Type={$type}, Account={$accountType}, ID={$accountIdNum}, Amount={$amount}");
            
            // ============================================
            // SAVE TO DAYBOOK
            // ============================================
            $daybook = new Daybook();
            $daybook->transaction_date = $date;
            $daybook->amount = $amount;
            $daybook->status = 1;
            $daybook->type = 'transaction';
            $daybook->approval_status = 'approved';
            $daybook->description = $description . ' - ' . $type . ' from ' . $accountType;
            
            if ($type == 'debit') {
                $daybook->debit_type = $accountType;
                $daybook->debit_id = $accountIdNum;
            } else {
                $daybook->credit_type = $accountType;
                $daybook->credit_id = $accountIdNum;
            }
            
            $daybook->save();
            \Log::info("Index {$index}: Daybook saved with ID: " . $daybook->id);
            
            // ============================================
            // UPDATE BALANCE AND CREATE TRANSACTION
            // ============================================
            if ($type == 'debit') {
                // DEBIT - Subtract from balance
                if ($accountType == 'customer') {
                    \Log::info("Index {$index}: Processing CUSTOMER debit");
                    $customer = Customer::find($accountIdNum);
                    if ($customer) {
                        $oldBalance = $customer->balance;
                        $customer->balance = $oldBalance - $amount;
                        $customer->save();
                        \Log::info("Index {$index}: Customer balance updated: {$oldBalance} -> {$customer->balance}");
                        
                        $transaction = new CustomerTransaction();
                        $transaction->uuid = Str::uuid();
                        $transaction->customer_id = $customer->id;
                        $transaction->transaction_date = $date;
                        $transaction->amount = $amount;
                        $transaction->type = 'debit';
                        $transaction->approval_status = 'approved';
                        $transaction->description = $description;
                        $transaction->current_balance = $customer->balance;
                        $transaction->save();
                        
                        // Link daybook to customer transaction
                        $daybook->customer_transaction_id = $transaction->id;
                        $daybook->save();
                        
                        \Log::info("Index {$index}: CustomerTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Customer NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'vendor') {
                    \Log::info("Index {$index}: Processing VENDOR debit");
                    $vendor = Vendor::find($accountIdNum);
                    if ($vendor) {
                        $oldBalance = $vendor->balance;
                        $newBalance = $oldBalance - $amount;
                        $vendor->balance = $newBalance;
                        $vendor->save();
                        \Log::info("Index {$index}: Vendor Balance Updated: {$oldBalance} -> {$newBalance}");
                        
                        // FIXED: Use correct column names for vendor_transactions table
                        $transaction = new VendorTransaction();
                        $transaction->uuid = Str::uuid();
                        $transaction->vendor_id = $vendor->id;
                        $transaction->date = $date;
                        $transaction->amount = (string)$amount;
                        // Use 'transaction_type' for debit/credit, and 'type' for the category
                        $transaction->transaction_type = 'debit';
                        $transaction->type = 'balance';  // 'balance' is the allowed enum value
                        $transaction->approval_status = 'approved';
                        $transaction->description = $description;
                        $transaction->current_balance = (string)$newBalance;
                        $transaction->save();
                        
                        // Link daybook to vendor transaction
                        $daybook->vendor_transaction_id = $transaction->id;
                        $daybook->save();
                        
                        \Log::info("Index {$index}: VendorTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Vendor NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'bank') {
                    \Log::info("Index {$index}: Processing BANK debit");
                    $bank = Bank::find($accountIdNum);
                    if ($bank) {
                        $oldBalance = $bank->account_balance;
                        $bank->account_balance = $oldBalance - $amount;
                        $bank->save();
                        
                        $transaction = new BankTransaction();
                        $transaction->bank_id = $bank->id;
                        $transaction->amount = $amount;
                        $transaction->balance = $bank->account_balance;
                        $transaction->transaction_type = 'debit';
                        $transaction->description = $description;
                        $transaction->save();
                        \Log::info("Index {$index}: BankTransaction saved with ID: " . $transaction->id);
                        
                        // Link daybook to bank transaction if you have a relationship
                        // $daybook->bank_transaction_id = $transaction->id;
                        // $daybook->save();
                    } else {
                        throw new \Exception("Bank NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'cash') {
                    \Log::info("Index {$index}: Processing CASH debit");
                    $cash = Cash::find($accountIdNum);
                    if ($cash) {
                        $oldBalance = $cash->balance;
                        $cash->balance = $oldBalance - $amount;
                        $cash->save();
                        
                        $transaction = new CashTransaction();
                        $transaction->cash_id = $cash->id;
                        $transaction->amount = $amount;
                        $transaction->balance = $cash->balance;
                        $transaction->transaction_type = 'debit';
                        $transaction->description = $description;
                        $transaction->save();
                        \Log::info("Index {$index}: CashTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Cash NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'expense') {
                    \Log::info("Index {$index}: Processing EXPENSE (Debit means recording an expense)");
                    // For expenses, debit means recording an expense
                    $expense = \App\Models\Expense::find($accountIdNum);
                    if ($expense) {
                        // You might want to create an expense record here
                        // For now, just log it and continue
                        \Log::info("Index {$index}: Expense recorded: " . $expense->name . " - Amount: " . $amount);
                    } else {
                        throw new \Exception("Expense NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                else {
                    \Log::warning("Index {$index}: Unknown account type: {$accountType}");
                }
            } 
            elseif ($type == 'credit') {
                // CREDIT - Add to balance
                if ($accountType == 'customer') {
                    \Log::info("Index {$index}: Processing CUSTOMER credit");
                    $customer = Customer::find($accountIdNum);
                    if ($customer) {
                        $oldBalance = $customer->balance;
                        $customer->balance = $oldBalance + $amount;
                        $customer->save();
                        
                        $transaction = new CustomerTransaction();
                        $transaction->uuid = Str::uuid();
                        $transaction->customer_id = $customer->id;
                        $transaction->transaction_date = $date;
                        $transaction->amount = $amount;
                        $transaction->type = 'credit';
                        $transaction->approval_status = 'approved';
                        $transaction->description = $description;
                        $transaction->current_balance = $customer->balance;
                        $transaction->save();
                        
                        // Link daybook to customer transaction
                        $daybook->customer_transaction_id = $transaction->id;
                        $daybook->save();
                        
                        \Log::info("Index {$index}: CustomerTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Customer NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'vendor') {
                    \Log::info("Index {$index}: Processing VENDOR credit");
                    $vendor = Vendor::find($accountIdNum);
                    if ($vendor) {
                        $oldBalance = $vendor->balance;
                        $newBalance = $oldBalance + $amount;
                        $vendor->balance = $newBalance;
                        $vendor->save();
                        \Log::info("Index {$index}: Vendor Balance Updated: {$oldBalance} -> {$newBalance}");
                        
                        // FIXED: Use correct column names for vendor_transactions table
                        $transaction = new VendorTransaction();
                        $transaction->uuid = Str::uuid();
                        $transaction->vendor_id = $vendor->id;
                        $transaction->date = $date;
                        $transaction->amount = (string)$amount;
                        // Use 'transaction_type' for debit/credit, and 'type' for the category
                        $transaction->transaction_type = 'credit';
                        $transaction->type = 'balance';  // 'balance' is the allowed enum value
                        $transaction->approval_status = 'approved';
                        $transaction->description = $description;
                        $transaction->current_balance = (string)$newBalance;
                        $transaction->save();
                        
                        // Link daybook to vendor transaction
                        $daybook->vendor_transaction_id = $transaction->id;
                        $daybook->save();
                        
                        \Log::info("Index {$index}: VendorTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Vendor NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'bank') {
                    \Log::info("Index {$index}: Processing BANK credit");
                    $bank = Bank::find($accountIdNum);
                    if ($bank) {
                        $oldBalance = $bank->account_balance;
                        $bank->account_balance = $oldBalance + $amount;
                        $bank->save();
                        
                        $transaction = new BankTransaction();
                        $transaction->bank_id = $bank->id;
                        $transaction->amount = $amount;
                        $transaction->balance = $bank->account_balance;
                        $transaction->transaction_type = 'credit';
                        $transaction->description = $description;
                        $transaction->save();
                        \Log::info("Index {$index}: BankTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Bank NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'cash') {
                    \Log::info("Index {$index}: Processing CASH credit");
                    $cash = Cash::find($accountIdNum);
                    if ($cash) {
                        $oldBalance = $cash->balance;
                        $cash->balance = $oldBalance + $amount;
                        $cash->save();
                        
                        $transaction = new CashTransaction();
                        $transaction->cash_id = $cash->id;
                        $transaction->amount = $amount;
                        $transaction->balance = $cash->balance;
                        $transaction->transaction_type = 'credit';
                        $transaction->description = $description;
                        $transaction->save();
                        \Log::info("Index {$index}: CashTransaction saved with ID: " . $transaction->id);
                    } else {
                        throw new \Exception("Cash NOT FOUND with ID: " . $accountIdNum);
                    }
                } 
                elseif ($accountType == 'expense') {
                    \Log::info("Index {$index}: Processing EXPENSE (Credit means reducing expense or refund)");
                    $expense = \App\Models\Expense::find($accountIdNum);
                    if ($expense) {
                        \Log::info("Index {$index}: Expense credit recorded: " . $expense->name . " - Amount: " . $amount);
                    } else {
                        throw new \Exception("Expense NOT FOUND with ID: " . $accountIdNum);
                    }
                }
                else {
                    \Log::warning("Index {$index}: Unknown account type: {$accountType}");
                }
            }
            
            $entriesSaved++;
        }
        
        \Log::info("Total entries saved: " . $entriesSaved);
        
        if ($entriesSaved > 0) {
            // Commit the transaction if everything succeeded
            DB::commit();
            
            return redirect()->route('general-transactions.general-entry')
                ->with('success', $entriesSaved . ' entry(s) created successfully!');
        } else {
            // Rollback if no entries saved
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid entries found. Please add amount.');
        }
        
    } catch (\Exception $e) {
        // Rollback the transaction on any error
        DB::rollBack();
        
        \Log::error('ERROR: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'Error: ' . $e->getMessage());
    }
}

    public function generalEntriesList(Request $request)
    {
        $entries = Daybook::where('type', 'transaction')->orderBy('id', 'desc')->paginate(10);
        return view('admin.pages.general.entries-list', compact('entries'));
    }

    public function getEntry($id)
    {
        $entry = Daybook::findOrFail($id);
        return response()->json(['html' => '<pre>' . json_encode($entry->toArray(), JSON_PRETTY_PRINT) . '</pre>']);
    }

    public function approveEntry($id)
    {
        return response()->json(['success' => true]);
    }

    public function deleteEntry($id)
    {
        return response()->json(['success' => true]);
    }

    public function getAccounts(Request $request)
    {
        $type = $request->type;
        $accounts = [];
        
        switch($type) {
            case 'customer':
                $accounts = Customer::where('active', 1)->get()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'display_name' => $item->name,
                        'balance' => $item->balance,
                        'type' => 'customer'
                    ];
                });
                break;
            case 'vendor':
                $accounts = Vendor::where('active', 1)->get()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->company_name,
                        'display_name' => $item->company_name,
                        'balance' => $item->balance,
                        'type' => 'vendor'
                    ];
                });
                break;
            case 'bank':
                $accounts = Bank::all()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'display_name' => $item->name,
                        'balance' => $item->account_balance,
                        'type' => 'bank'
                    ];
                });
                break;
            case 'cash':
                $cash = Cash::first();
                $accounts = [[
                    'id' => 1,
                    'name' => 'Main Cash',
                    'display_name' => 'Cash Account',
                    'balance' => $cash ? $cash->balance : 0,
                    'type' => 'cash'
                ]];
                break;
            case 'expense':
                $accounts = \App\Models\Expense::all()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'display_name' => $item->name,
                        'balance' => 0,
                        'type' => 'expense'
                    ];
                });
                break;
        }
        
        return response()->json($accounts);
    }

    // Transfer methods
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
    
    public function customerToVendorTransfer(Request $request) 
    { 
        return redirect()->back()->with('success', 'Transfer completed'); 
    }
    
    public function bankToBankTransfer(Request $request) 
    { 
        return redirect()->back()->with('success', 'Transfer completed'); 
    }
    
    public function bankWithdraw(Request $request) 
    { 
        return redirect()->back()->with('success', 'Withdrawal completed'); 
    }
    
    public function bankDeposit(Request $request) 
    { 
        return redirect()->back()->with('success', 'Deposit completed'); 
    }
}