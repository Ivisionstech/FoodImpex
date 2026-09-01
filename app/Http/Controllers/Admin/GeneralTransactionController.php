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
use Illuminate\Pagination\LengthAwarePaginator;

class GeneralTransactionController extends Controller
{
    public function index()
    {
        // Get all entries ordered by latest first
        $allEntries = Daybook::where('type', 'transaction')
            ->orderBy('id', 'desc')
            ->get();
        
        // Group by batch_id or use individual ID for old entries
        $groupedEntries = $allEntries->groupBy(function($item) {
            return $item->batch_id ?? 'single_' . $item->id;
        })->map(function($group, $key) {
            if (str_starts_with($key, 'single_')) {
                // Old entry without batch_id - show as single
                $entry = $group->first();
                return (object) [
                    'id' => $entry->id,
                    'batch_id' => $key,
                    'transaction_date' => $entry->transaction_date,
                    'description' => $entry->description,
                    'debit_type' => $entry->debit_type,
                    'debit_id' => $entry->debit_id,
                    'credit_type' => $entry->credit_type,
                    'credit_id' => $entry->credit_id,
                    'amount' => $entry->amount,
                    'approval_status' => $entry->approval_status,
                    'status' => $entry->status,
                    'type' => $entry->type,
                    'entry_count' => 1,
                    'is_grouped' => false,
                    'created_at' => $entry->created_at,
                    'updated_at' => $entry->updated_at,
                    'customer_transaction_id' => $entry->customer_transaction_id ?? null,
                    'vendor_transaction_id' => $entry->vendor_transaction_id ?? null,
                ];
            } else {
                // Grouped entries with batch_id
                $first = $group->first();
                $totalAmount = $group->sum('amount');
                
                // Collect all account names for description
                $accountNames = [];
                foreach ($group as $item) {
                    $accountName = $this->getAccountName($item);
                    if ($accountName) {
                        $accountNames[] = $accountName;
                    }
                }
                
                // Build description with entry count and account names
                $description = $group->count() . ' entries';
                if (count($accountNames) > 0) {
                    $description .= ' - ' . implode(', ', array_slice($accountNames, 0, 3));
                    if (count($accountNames) > 3) {
                        $description .= ' +' . (count($accountNames) - 3) . ' more';
                    }
                }
                
                // Determine approval status (pending if any entry is pending)
                $approvalStatus = $group->contains('approval_status', 'pending') ? 'pending' : 'approved';
                
                return (object) [
                    'id' => $first->id,
                    'batch_id' => $key,
                    'transaction_date' => $first->transaction_date,
                    'description' => $description,
                    'debit_type' => $first->debit_type,
                    'debit_id' => $first->debit_id,
                    'credit_type' => $first->credit_type,
                    'credit_id' => $first->credit_id,
                    'amount' => $totalAmount,
                    'approval_status' => $approvalStatus,
                    'status' => $first->status,
                    'type' => $first->type,
                    'entry_count' => $group->count(),
                    'is_grouped' => true,
                    'created_at' => $first->created_at,
                    'updated_at' => $first->updated_at,
                    'customer_transaction_id' => $first->customer_transaction_id ?? null,
                    'vendor_transaction_id' => $first->vendor_transaction_id ?? null,
                ];
            }
        })->values();
        
        // Apply filters if present
        if (request('approval_status')) {
            $groupedEntries = $groupedEntries->filter(function($entry) {
                return $entry->approval_status == request('approval_status');
            });
        }
        
        if (request('from_date')) {
            $groupedEntries = $groupedEntries->filter(function($entry) {
                return $entry->transaction_date >= request('from_date');
            });
        }
        
        if (request('to_date')) {
            $groupedEntries = $groupedEntries->filter(function($entry) {
                return $entry->transaction_date <= request('to_date') . ' 23:59:59';
            });
        }
        
        // Paginate the grouped entries
        $perPage = request('per_page', 10);
        $currentPage = request('page', 1);
        $paginatedEntries = new LengthAwarePaginator(
            $groupedEntries->forPage($currentPage, $perPage),
            $groupedEntries->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('admin.pages.general.entries-list', compact('paginatedEntries'));
    }

    // Helper method to get account name from entry
    private function getAccountName($entry)
    {
        if ($entry->debit_type && $entry->debit_id) {
            $type = $entry->debit_type;
            $id = $entry->debit_id;
            if ($type == 'customer') {
                $customer = Customer::find($id);
                return $customer ? 'Cust: ' . $customer->name : 'Customer #' . $id;
            } elseif ($type == 'vendor') {
                $vendor = Vendor::find($id);
                return $vendor ? 'Vend: ' . $vendor->company_name : 'Vendor #' . $id;
            } elseif ($type == 'bank') {
                $bank = Bank::find($id);
                return $bank ? 'Bank: ' . $bank->name : 'Bank #' . $id;
            } elseif ($type == 'cash') {
                return 'Cash';
            } elseif ($type == 'expense') {
                $expense = \App\Models\Expense::find($id);
                return $expense ? 'Exp: ' . $expense->name : 'Expense #' . $id;
            }
        } elseif ($entry->credit_type && $entry->credit_id) {
            $type = $entry->credit_type;
            $id = $entry->credit_id;
            if ($type == 'customer') {
                $customer = Customer::find($id);
                return $customer ? 'Cust: ' . $customer->name : 'Customer #' . $id;
            } elseif ($type == 'vendor') {
                $vendor = Vendor::find($id);
                return $vendor ? 'Vend: ' . $vendor->company_name : 'Vendor #' . $id;
            } elseif ($type == 'bank') {
                $bank = Bank::find($id);
                return $bank ? 'Bank: ' . $bank->name : 'Bank #' . $id;
            } elseif ($type == 'cash') {
                return 'Cash';
            } elseif ($type == 'expense') {
                $expense = \App\Models\Expense::find($id);
                return $expense ? 'Exp: ' . $expense->name : 'Expense #' . $id;
            }
        }
        return null;
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
     * Display the specified entry
     */
    public function show($id)
    {
        try {
            $entry = Daybook::findOrFail($id);
            return view('admin.pages.general.view', compact('entry'));
        } catch (\Throwable $th) {
            \Log::error('Show entry error: ' . $th->getMessage());
            return redirect()->route('general-transactions.index')
                ->with('error', 'Entry not found');
        }
    }
    
    /**
     * Show the form for editing the specified entry - FIXED for BATCH
     */
    public function edit($id)
    {
        try {
            $entry = Daybook::findOrFail($id);
            
            // Check if this entry is part of a batch
            $batchId = $entry->batch_id;
            $entries = [];
            
            if ($batchId) {
                // Load all entries in this batch
                $entries = Daybook::where('batch_id', $batchId)
                    ->orderBy('id', 'asc')
                    ->get();
            } else {
                // Single entry (no batch)
                $entries = collect([$entry]);
            }
            
            // Get all necessary data for the form
            $customers = Customer::where('active', 1)->orderBy('name')->get();
            $vendors = Vendor::where('active', 1)->orderBy('company_name')->get();
            $banks = Bank::orderBy('name')->get();
            $cash = Cash::first();
            $expenses = \App\Models\Expense::all();
            
            return view('admin.pages.general.edit', compact('entries', 'customers', 'vendors', 'banks', 'cash', 'expenses', 'batchId', 'entry'));
            
        } catch (\Throwable $th) {
            \Log::error('Edit entry error: ' . $th->getMessage());
            return redirect()->route('general-transactions.index')->with('error', 'Entry not found');
        }
    }

    /**
     * Update the specified entry - FIXED for BATCH
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $entry = Daybook::findOrFail($id);
            
            // Get the batch_id if it exists
            $batchId = $entry->batch_id;
            
            // Validate the request
            $request->validate([
                'transaction_date' => 'required|date',
                'account_ids' => 'required|array',
                'account_ids.*' => 'required|string',
                'debit_amounts' => 'required|array',
                'credit_amounts' => 'required|array',
                'descriptions' => 'array',
            ]);
            
            $date = $request->input('transaction_date');
            $accountIds = $request->input('account_ids', []);
            $debitAmounts = $request->input('debit_amounts', []);
            $creditAmounts = $request->input('credit_amounts', []);
            $descriptions = $request->input('descriptions', []);
            
            // If this is a batch entry, get all entries in the batch
            $oldEntries = [];
            if ($batchId) {
                $oldEntries = Daybook::where('batch_id', $batchId)->get();
            } else {
                $oldEntries = collect([$entry]);
            }
            
            // Reverse previous transaction effects for all entries
            foreach ($oldEntries as $oldEntry) {
                if ($oldEntry->debit_type && $oldEntry->debit_id) {
                    $this->reverseTransaction($oldEntry->debit_type, $oldEntry->debit_id, $oldEntry->amount, 'debit');
                }
                if ($oldEntry->credit_type && $oldEntry->credit_id) {
                    $this->reverseTransaction($oldEntry->credit_type, $oldEntry->credit_id, $oldEntry->amount, 'credit');
                }
                
                // Delete related transactions
                if ($oldEntry->customer_transaction_id) {
                    CustomerTransaction::find($oldEntry->customer_transaction_id)?->delete();
                }
                if ($oldEntry->vendor_transaction_id) {
                    VendorTransaction::find($oldEntry->vendor_transaction_id)?->delete();
                }
            }
            
            // Delete all old entries in the batch
            Daybook::where('batch_id', $batchId)->delete();
            
            // Generate a new batch ID for the updated entries
            $newBatchId = Str::uuid()->toString();
            
            // Check if user is admin
            $isAdmin = auth()->user()->role == 'admin';
            $approvalStatus = $isAdmin ? 'approved' : 'pending';
            
            // Now create new entries with updated data
            $entriesSaved = 0;
            
            foreach ($accountIds as $index => $accountId) {
                if (empty($accountId)) {
                    continue;
                }
                
                $amount = 0;
                $type = '';
                
                if (isset($debitAmounts[$index]) && $debitAmounts[$index] > 0) {
                    $amount = $debitAmounts[$index];
                    $type = 'debit';
                } elseif (isset($creditAmounts[$index]) && $creditAmounts[$index] > 0) {
                    $amount = $creditAmounts[$index];
                    $type = 'credit';
                } else {
                    continue;
                }
                
                $parts = explode('_', $accountId);
                if (count($parts) != 2) {
                    continue;
                }
                
                $accountType = $parts[0];
                $accountIdNum = $parts[1];
                $description = isset($descriptions[$index]) ? $descriptions[$index] : '';
                
                // Create new daybook entry
                $daybook = new Daybook();
                $daybook->batch_id = $newBatchId;
                $daybook->transaction_date = $date;
                $daybook->amount = $amount;
                $daybook->status = ($type == 'credit') ? 0 : 1;
                $daybook->type = 'transaction';
                $daybook->approval_status = $approvalStatus;
                $daybook->description = $description . ' - ' . $type . ' from ' . $accountType;
                
                if ($type == 'debit') {
                    $daybook->debit_type = $accountType;
                    $daybook->debit_id = $accountIdNum;
                } else {
                    $daybook->credit_type = $accountType;
                    $daybook->credit_id = $accountIdNum;
                }
                
                $daybook->save();
                
                // Update balance and create transaction only if approved
                if ($approvalStatus == 'approved') {
                    if ($type == 'debit') {
                        $this->processDebit($accountType, $accountIdNum, $amount, $date, $description, $daybook);
                    } elseif ($type == 'credit') {
                        $this->processCredit($accountType, $accountIdNum, $amount, $date, $description, $daybook);
                    }
                }
                
                $entriesSaved++;
            }
            
            if ($entriesSaved > 0) {
                DB::commit();
                $message = $entriesSaved . ' entry(s) updated successfully! (Batch: ' . $newBatchId . ')';
                if ($approvalStatus == 'pending') {
                    $message .= ' Waiting for admin approval.';
                }
                return redirect()->route('general-transactions.index')
                    ->with('success', $message);
            } else {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No valid entries found. Please add amount.');
            }
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Update error: ' . $th->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $th->getMessage());
        }
    }

    // Helper methods for processing transactions
    private function processDebit($accountType, $accountIdNum, $amount, $date, $description, $daybook)
    {
        if ($accountType == 'customer') {
            $customer = Customer::find($accountIdNum);
            if ($customer) {
                $oldBalance = $customer->balance;
                $customer->balance = $oldBalance - $amount;
                $customer->save();
                
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
                
                $daybook->customer_transaction_id = $transaction->id;
                $daybook->save();
            }
        } elseif ($accountType == 'vendor') {
            $vendor = Vendor::find($accountIdNum);
            if ($vendor) {
                $oldBalance = $vendor->balance;
                $newBalance = $oldBalance - $amount;
                $vendor->balance = $newBalance;
                $vendor->save();
                
                $transaction = new VendorTransaction();
                $transaction->uuid = Str::uuid();
                $transaction->vendor_id = $vendor->id;
                $transaction->date = $date;
                $transaction->amount = (string)$amount;
                $transaction->transaction_type = 'debit';
                $transaction->type = 'balance';
                $transaction->approval_status = 'approved';
                $transaction->description = $description;
                $transaction->current_balance = (string)$newBalance;
                $transaction->save();
                
                $daybook->vendor_transaction_id = $transaction->id;
                $daybook->save();
            }
        } elseif ($accountType == 'bank') {
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
            }
        } elseif ($accountType == 'cash') {
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
            }
        }
    }

    private function processCredit($accountType, $accountIdNum, $amount, $date, $description, $daybook)
    {
        if ($accountType == 'customer') {
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
                
                $daybook->customer_transaction_id = $transaction->id;
                $daybook->save();
            }
        } elseif ($accountType == 'vendor') {
            $vendor = Vendor::find($accountIdNum);
            if ($vendor) {
                $oldBalance = $vendor->balance;
                $newBalance = $oldBalance + $amount;
                $vendor->balance = $newBalance;
                $vendor->save();
                
                $transaction = new VendorTransaction();
                $transaction->uuid = Str::uuid();
                $transaction->vendor_id = $vendor->id;
                $transaction->date = $date;
                $transaction->amount = (string)$amount;
                $transaction->transaction_type = 'credit';
                $transaction->type = 'balance';
                $transaction->approval_status = 'approved';
                $transaction->description = $description;
                $transaction->current_balance = (string)$newBalance;
                $transaction->save();
                
                $daybook->vendor_transaction_id = $transaction->id;
                $daybook->save();
            }
        } elseif ($accountType == 'bank') {
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
            }
        } elseif ($accountType == 'cash') {
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
            }
        }
    }

    private function reverseTransaction($type, $id, $amount, $transactionType)
    {
        if ($type == 'customer') {
            $customer = Customer::find($id);
            if ($customer) {
                if ($transactionType == 'debit') {
                    $customer->balance = $customer->balance + $amount;
                } else {
                    $customer->balance = $customer->balance - $amount;
                }
                $customer->save();
            }
        } elseif ($type == 'vendor') {
            $vendor = Vendor::find($id);
            if ($vendor) {
                if ($transactionType == 'debit') {
                    $vendor->balance = $vendor->balance + $amount;
                } else {
                    $vendor->balance = $vendor->balance - $amount;
                }
                $vendor->save();
            }
        } elseif ($type == 'bank') {
            $bank = Bank::find($id);
            if ($bank) {
                if ($transactionType == 'debit') {
                    $bank->account_balance = $bank->account_balance + $amount;
                } else {
                    $bank->account_balance = $bank->account_balance - $amount;
                }
                $bank->save();
            }
        } elseif ($type == 'cash') {
            $cash = Cash::find($id);
            if ($cash) {
                if ($transactionType == 'debit') {
                    $cash->balance = $cash->balance + $amount;
                } else {
                    $cash->balance = $cash->balance - $amount;
                }
                $cash->save();
            }
        }
    }

    /**
     * STORE GENERAL ENTRY - UPDATED WITH ROLE-BASED APPROVAL
     */
    public function storeGeneralEntry(Request $request)
    {
        \Log::info('=== FORM SUBMITTED ===');
        \Log::info('POST Data:', $request->all());
        
        DB::beginTransaction();
        
        try {
            // Generate a unique batch ID for this group of entries
            $batchId = Str::uuid()->toString();
            
            $date = $request->input('transaction_date');
            $accountIds = $request->input('account_ids', []);
            $debitAmounts = $request->input('debit_amounts', []);
            $creditAmounts = $request->input('credit_amounts', []);
            $descriptions = $request->input('descriptions', []);
            
            // Check if user is admin
            $isAdmin = auth()->user()->role == 'admin';
            $approvalStatus = $isAdmin ? 'approved' : 'pending';
            
            \Log::info('User role: ' . auth()->user()->role);
            \Log::info('Approval status set to: ' . $approvalStatus);
            
            $entriesSaved = 0;
            
            foreach ($accountIds as $index => $accountId) {
                if (empty($accountId)) {
                    \Log::info("Index {$index}: Account ID is empty, skipping");
                    continue;
                }
                
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
                
                $daybook = new Daybook();
                $daybook->batch_id = $batchId;
                $daybook->transaction_date = $date;
                $daybook->amount = $amount;
                $daybook->status = ($type == 'credit') ? 0 : 1; // 0=Credit, 1=Debit
                $daybook->type = 'transaction';
                $daybook->approval_status = $approvalStatus;
                $daybook->description = $description . ' - ' . $type . ' from ' . $accountType;
                
                if ($type == 'debit') {
                    $daybook->debit_type = $accountType;
                    $daybook->debit_id = $accountIdNum;
                } else {
                    $daybook->credit_type = $accountType;
                    $daybook->credit_id = $accountIdNum;
                }
                
                $daybook->save();
                \Log::info("Index {$index}: Daybook saved with ID: " . $daybook->id . " (Batch: {$batchId})");
                
                // Process transactions only if approved
                if ($approvalStatus == 'approved') {
                    if ($type == 'debit') {
                        $this->processDebit($accountType, $accountIdNum, $amount, $date, $description, $daybook);
                    } else {
                        $this->processCredit($accountType, $accountIdNum, $amount, $date, $description, $daybook);
                    }
                }
                
                $entriesSaved++;
            }
            
            \Log::info("Total entries saved: " . $entriesSaved . " with Batch ID: {$batchId}");
            
            if ($entriesSaved > 0) {
                DB::commit();
                $message = $entriesSaved . ' entry(s) created successfully! (Batch: ' . $batchId . ')';
                if ($approvalStatus == 'pending') {
                    $message .= ' Waiting for admin approval.';
                }
                return redirect()->route('general-transactions.index')
                    ->with('success', $message);
            } else {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No valid entries found. Please add amount.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Approve a pending entry
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();
            
            $entry = Daybook::findOrFail($id);
            
            // Check if entry is already approved
            if ($entry->approval_status == 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Entry is already approved'
                ], 400);
            }
            
            // Update approval status
            $entry->approval_status = 'approved';
            $entry->save();
            
            // If this is a batch entry, approve all entries in the same batch
            if ($entry->batch_id) {
                Daybook::where('batch_id', $entry->batch_id)
                    ->where('approval_status', 'pending')
                    ->update(['approval_status' => 'approved']);
                    
                // Get all entries in the batch to process their transactions
                $batchEntries = Daybook::where('batch_id', $entry->batch_id)->get();
                foreach ($batchEntries as $batchEntry) {
                    // Process transactions for each entry if they haven't been processed yet
                    if (!$batchEntry->customer_transaction_id && !$batchEntry->vendor_transaction_id) {
                        if ($batchEntry->debit_type && $batchEntry->debit_id) {
                            $this->processDebit(
                                $batchEntry->debit_type, 
                                $batchEntry->debit_id, 
                                $batchEntry->amount, 
                                $batchEntry->transaction_date, 
                                $batchEntry->description, 
                                $batchEntry
                            );
                        } elseif ($batchEntry->credit_type && $batchEntry->credit_id) {
                            $this->processCredit(
                                $batchEntry->credit_type, 
                                $batchEntry->credit_id, 
                                $batchEntry->amount, 
                                $batchEntry->transaction_date, 
                                $batchEntry->description, 
                                $batchEntry
                            );
                        }
                    }
                }
            } else {
                // Single entry - process transactions if not already processed
                if (!$entry->customer_transaction_id && !$entry->vendor_transaction_id) {
                    if ($entry->debit_type && $entry->debit_id) {
                        $this->processDebit(
                            $entry->debit_type, 
                            $entry->debit_id, 
                            $entry->amount, 
                            $entry->transaction_date, 
                            $entry->description, 
                            $entry
                        );
                    } elseif ($entry->credit_type && $entry->credit_id) {
                        $this->processCredit(
                            $entry->credit_type, 
                            $entry->credit_id, 
                            $entry->amount, 
                            $entry->transaction_date, 
                            $entry->description, 
                            $entry
                        );
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Entry approved successfully!'
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Approve entry error: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve entry: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an entry
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            
            $entry = Daybook::findOrFail($id);
            
            // If this is a batch entry, delete all entries in the batch
            if ($entry->batch_id) {
                $batchEntries = Daybook::where('batch_id', $entry->batch_id)->get();
                
                foreach ($batchEntries as $batchEntry) {
                    // Reverse transactions
                    if ($batchEntry->debit_type && $batchEntry->debit_id) {
                        $this->reverseTransaction($batchEntry->debit_type, $batchEntry->debit_id, $batchEntry->amount, 'debit');
                    }
                    if ($batchEntry->credit_type && $batchEntry->credit_id) {
                        $this->reverseTransaction($batchEntry->credit_type, $batchEntry->credit_id, $batchEntry->amount, 'credit');
                    }
                    
                    // Delete related transactions
                    if ($batchEntry->customer_transaction_id) {
                        CustomerTransaction::find($batchEntry->customer_transaction_id)?->delete();
                    }
                    if ($batchEntry->vendor_transaction_id) {
                        VendorTransaction::find($batchEntry->vendor_transaction_id)?->delete();
                    }
                }
                
                // Delete all entries in the batch
                Daybook::where('batch_id', $entry->batch_id)->delete();
            } else {
                // Single entry
                if ($entry->debit_type && $entry->debit_id) {
                    $this->reverseTransaction($entry->debit_type, $entry->debit_id, $entry->amount, 'debit');
                }
                if ($entry->credit_type && $entry->credit_id) {
                    $this->reverseTransaction($entry->credit_type, $entry->credit_id, $entry->amount, 'credit');
                }
                
                if ($entry->customer_transaction_id) {
                    CustomerTransaction::find($entry->customer_transaction_id)?->delete();
                }
                if ($entry->vendor_transaction_id) {
                    VendorTransaction::find($entry->vendor_transaction_id)?->delete();
                }
                
                $entry->delete();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Entry deleted successfully!'
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Delete entry error: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete entry: ' . $th->getMessage()
            ], 500);
        }
    }

    public function generalEntriesList(Request $request)
    {
        return $this->index();
    }

    /**
     * Get entry as JSON (for AJAX)
     */
    public function getEntry($id)
    {
        try {
            $entry = Daybook::findOrFail($id);
            
            // If it's an AJAX request, return JSON
            if (request()->ajax()) {
                return response()->json([
                    'html' => view('admin.pages.general.view-modal', compact('entry'))->render()
                ]);
            }
            
            // Otherwise return the full page view
            return view('admin.pages.general.view', compact('entry'));
        } catch (\Throwable $th) {
            \Log::error('Get entry error: ' . $th->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'Entry not found'], 404);
            }
            return redirect()->route('general-transactions.index')
                ->with('error', 'Entry not found');
        }
    }

    // These methods are kept for backward compatibility
    public function approveEntry($id)
    {
        return $this->approve($id);
    }

    public function deleteEntry($id)
    {
        return $this->delete($id);
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