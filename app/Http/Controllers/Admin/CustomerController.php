<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreRequest;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Bank;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Daybook;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;


class CustomerController extends Controller
{

    public function paymentsList(Request $request)
    {
        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;

            $query = CustomerTransaction::with('customer')
                ->where('type', 'payment')
                ->orderBy('transaction_date', 'DESC');

            // Date Filter logic
            if ($from_date && $to_date) {
                $query->whereBetween('transaction_date', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);
            }

            $payments = $query->paginate(20);

            return view('admin.pages.customers.payments_list', compact('payments', 'from_date', 'to_date'));
        } catch (\Exception $e) {
            Log::error('Failed to list payments: ' . $e->getMessage());
            return redirect()->back()->with(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function receivePaymentGeneral()
    {
        try {
            $customers = Customer::where('active', 1)->orderBy('name', 'ASC')->get();
            $banks = Bank::all();
            return view('admin.pages.customers.receive_payment', compact('customers', 'banks'));
        } catch (\Exception $e) {
            return redirect()->back()->with(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function storeReceivePaymentGeneral(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,uuid',
            'date' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'receive_via' => 'required|in:bank,cash',
            'bank_id' => 'required_if:receive_via,bank',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::where('uuid', $request->customer_id)->firstOrFail();
            $amount = (float)$request->amount;

            $customer->decrement('balance', $amount);

            $imagePaths = [];
            if ($request->hasFile('receipt_images')) {
                foreach ($request->file('receipt_images') as $image) {
                    $path = $image->store('payments/customers', 'public');
                    $imagePaths[] = $path;
                }
            }

            CustomerTransaction::create([
                'uuid' => Str::uuid(),
                'customer_id' => $customer->id,
                'transaction_date' => $request->date,
                'amount' => $amount,
                'type' => 'payment',
                'description' => $request->description ?? 'General payment received via ' . $request->receive_via,
                'current_balance' => $customer->balance,
                'customer_bill_id' => null,
                'method' => $request->receive_via,
                'bank_id' => $request->receive_via == 'bank' ? $request->bank_id : null,
                'attachments' => json_encode($imagePaths),
            ]);

            if ($request->receive_via == 'bank') {
                $bank = Bank::find($request->bank_id);
                if ($bank) {
                    $bank->increment('account_balance', $amount);
                }
            }

            if ($request->receive_via == 'cash') {
                $cash = Cash::first();
                if ($cash) {
                    $cash->increment('balance', $amount);

                    CashTransaction::create([
                        'cash_id' => $cash->id,
                        'transaction_type' => 'credit',
                        'amount' => $amount,
                        'balance' => $cash->balance,
                        'description' => $request->description ?? ('Payment received from ' . $customer->name),
                    ]);

                    Daybook::create([
                        'transaction_date' => $request->date ?? now(),
                        'amount' => $amount,
                        'type' => 'transaction',
                        'description' => "Customer payment received ({$customer->name})",
                        'customer_transaction_id' => null,
                        'vendor_transaction_id' => null,
                        'expense_id' => null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment received successfully and balance updated.',
                'redirect' => route('customers.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function list()
    {
        try {
            $customers = Customer::where('active', 1)->latest()->paginate(10);
            return view('admin.pages.customers.list', compact('customers'));
        } catch (\Exception $e) {
            Log::error('Failed to list customers: ' . $e->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to list customers: ' . $e->getMessage(),
            ]);
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $customer = Customer::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'person_name' => $request->person_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'balance' => $request->balance,
                'notes' => $request->notes,
                'address' => $request->address,
            ]);
            CustomerTransaction::create([
                'uuid' => Str::uuid(),
                'customer_id' => $customer->id,
                'transaction_date' => $request->open_balance_date ?? now(),
                'amount' => $request->balance,
                'type' => 'balance',
                'description' => 'Initial Balance',
                'current_balance' => $request->balance,
                'customer_bill_id' => null,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Customer created successfully.',
                'redirect' => route('customers.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage(),
            ]);
        }
    }

    public function edit(string $uuid)
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        return view('admin.pages.customers.edit', compact('customer'));
    }

    public function view(Request $request, string $uuid)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();

            $bill_from = $request->bill_from;
            $bill_to = $request->bill_to;
            $trans_from = $request->trans_from;
            $trans_to = $request->trans_to;

            // Get regular transactions (bills, payments, balance)
            $transactionsQuery = $customer->customerTransactions();
            
            // Get general entries from daybooks
            $generalEntries = collect([]);
            
            if (Schema::hasTable('daybooks')) {
                $generalEntriesQuery = DB::table('daybooks')
                    ->where('type', 'transaction')
                    ->whereNotNull('customer_transaction_id')
                    ->whereIn('customer_transaction_id', function($query) use ($customer) {
                        $query->select('id')
                            ->from('customer_transactions')
                            ->where('customer_id', $customer->id);
                    });
                
                // Apply date filters to general entries
                if ($trans_from && $trans_to) {
                    $generalEntriesQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
                } elseif ($trans_from) {
                    $generalEntriesQuery->where('transaction_date', '>=', $trans_from);
                } elseif ($trans_to) {
                    $generalEntriesQuery->where('transaction_date', '<=', $trans_to);
                }
                
                $generalEntryData = $generalEntriesQuery->orderBy('transaction_date', 'DESC')->get();
                
                foreach ($generalEntryData as $item) {
                    $customerTransaction = CustomerTransaction::find($item->customer_transaction_id);
                    if ($customerTransaction) {
                        $generalEntries->push((object)[
                            'id' => $item->id,
                            'uuid' => 'daybook_' . $item->id,
                            'transaction_date' => $item->transaction_date,
                            'amount' => $item->amount,
                            'type' => $customerTransaction->type == 'bill' ? 'general_debit' : 'general_credit',
                            'description' => $item->description,
                            'current_balance' => $customerTransaction->current_balance,
                            'bill' => $customerTransaction->bill,
                            'method' => $customerTransaction->receive_via,
                            'entry_type' => $customerTransaction->type == 'bill' ? 'Debit Entry' : 'Credit Entry',
                        ]);
                    }
                }
            }
            
            // Apply date filters to regular transactions
            if ($trans_from && $trans_to) {
                $transactionsQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $transactionsQuery->where('transaction_date', '>=', $trans_from);
            } elseif ($trans_to) {
                $transactionsQuery->where('transaction_date', '<=', $trans_to);
            }
            
            $regularTransactions = $transactionsQuery->orderBy('transaction_date', 'DESC')->get();
            
            // Merge and sort all transactions by date (newest first)
            $allTransactions = $regularTransactions->concat($generalEntries);
            $customerTransactions = $allTransactions->sortByDesc('transaction_date')->values();
            
            // Get bills with filters
            $billsQuery = $customer->bills();
            if ($bill_from && $bill_to) {
                $billsQuery->whereBetween('bill_date', [$bill_from, $bill_to]);
            } elseif ($bill_from) {
                $billsQuery->where('bill_date', '>=', $bill_from);
            } elseif ($bill_to) {
                $billsQuery->where('bill_date', '<=', $bill_to);
            }
            $customerBills = $billsQuery->orderBy('bill_date', 'DESC')->get();

            return view('admin.pages.customers.view', compact(
                'customer',
                'customerBills',
                'customerTransactions',
                'bill_from',
                'bill_to',
                'trans_from',
                'trans_to'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to view customer: ' . $e->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to view customer: ' . $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'person_name' => 'nullable|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.required' => 'Customer name is required.',
            'phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email address is already registered.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        try {
            DB::beginTransaction();
            $customer = Customer::where('uuid', $request->uuid)->firstOrFail();
            $customer->update([
                'name' => $validated['name'],
                'person_name' => $validated['person_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'address' => $validated['address'],
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Customer updated successfully.',
                'redirect' => route('customers.view', $customer->uuid),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete(string $uuid)
    {
        try {
            DB::beginTransaction();
            $customer = Customer::where('uuid', $uuid)->first();
            $customer->update([
                'active' => 0,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Customer deleted successfully.',
                'redirect' => route('customers.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete customer: ' . $e->getMessage(),
            ]);
        }
    }

    public function editPayment(string $uuid)
    {
        try {
            $payment = CustomerTransaction::with('customer')->where('uuid', $uuid)->firstOrFail();
            $banks = Bank::all();
            return view('admin.pages.customers.edit_payment', compact('payment', 'banks'));
        } catch (\Exception $e) {
            Log::error('Failed to load payment edit form: ' . $e->getMessage());
            return redirect()->back()->with(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updatePayment(Request $request, string $uuid)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:bank,cash',
            'description' => 'nullable|string',
            'bank_id' => 'required_if:method,bank',
        ]);

        try {
            DB::beginTransaction();

            $payment = CustomerTransaction::where('uuid', $uuid)->firstOrFail();
            $customer = $payment->customer;
            $oldAmount = $payment->amount;
            $newAmount = (float)$request->amount;

            // Reverse old balance calculation
            $customer->increment('balance', $oldAmount);

            // Apply new balance calculation
            $customer->decrement('balance', $newAmount);

            // Update old bank balance if payment was via bank
            if ($payment->method == 'bank' && $payment->bank_id) {
                $oldBank = Bank::find($payment->bank_id);
                if ($oldBank) {
                    $oldBank->decrement('account_balance', $oldAmount);
                }
            }

            // Update new bank balance if new method is bank
            if ($request->method == 'bank' && $request->bank_id) {
                $newBank = Bank::find($request->bank_id);
                if ($newBank) {
                    $newBank->increment('account_balance', $newAmount);
                }
            }

            // Update payment record
            $payment->update([
                'amount' => $newAmount,
                'method' => $request->method,
                'bank_id' => $request->method == 'bank' ? $request->bank_id : null,
                'description' => $request->description ?? $payment->description,
                'current_balance' => $customer->balance,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment updated successfully.',
                'redirect' => route('customers.receive-payment.list'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update payment: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function deletePayment(string $uuid)
    {
        try {
            DB::beginTransaction();

            $payment = CustomerTransaction::where('uuid', $uuid)->firstOrFail();
            $customer = $payment->customer;
            $amount = $payment->amount;

            // Reverse the balance calculation
            $customer->increment('balance', $amount);

            // Reverse bank account balance if payment was via bank
            if ($payment->method == 'bank' && $payment->bank_id) {
                $bank = Bank::find($payment->bank_id);
                if ($bank) {
                    $bank->decrement('account_balance', $amount);
                }
            }

            // Delete the payment record
            $payment->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment deleted successfully and balance reversed.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete payment: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        try {
            return view('admin.pages.customers.create');
        } catch (\Exception $e) {
            Log::error('Failed to load customer creation form: ' . $e->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to load form: ' . $e->getMessage(),
            ]);
        }
    }

   /**
 * Generate Bank Statement View - Using Same Logic as view.blade.php
 */
public function bankStatement($uuid)
{
    try {
        $customer = Customer::where('uuid', $uuid)->first();
        
        if (!$customer) {
            return redirect()->route('customers.list')->with('error', 'Customer not found');
        }

        // Get request filters (same as view.blade.php)
        $bill_from = request()->bill_from;
        $bill_to = request()->bill_to;
        $trans_from = request()->trans_from;
        $trans_to = request()->trans_to;

        // Get regular transactions (bills, payments, balance) - SAME LOGIC AS VIEW
        $transactionsQuery = $customer->customerTransactions();
        
        // Get general entries from daybooks - SAME LOGIC AS VIEW
        $generalEntries = collect([]);
        
        if (Schema::hasTable('daybooks')) {
            $generalEntriesQuery = DB::table('daybooks')
                ->where('type', 'transaction')
                ->whereNotNull('customer_transaction_id')
                ->whereIn('customer_transaction_id', function($query) use ($customer) {
                    $query->select('id')
                        ->from('customer_transactions')
                        ->where('customer_id', $customer->id);
                });
            
            // Apply date filters to general entries
            if ($trans_from && $trans_to) {
                $generalEntriesQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $generalEntriesQuery->where('transaction_date', '>=', $trans_from);
            } elseif ($trans_to) {
                $generalEntriesQuery->where('transaction_date', '<=', $trans_to);
            }
            
            $generalEntryData = $generalEntriesQuery->orderBy('transaction_date', 'DESC')->get();
            
            foreach ($generalEntryData as $item) {
                $customerTransaction = CustomerTransaction::find($item->customer_transaction_id);
                if ($customerTransaction) {
                    $generalEntries->push((object)[
                        'id' => $item->id,
                        'uuid' => 'daybook_' . $item->id,
                        'transaction_date' => $item->transaction_date,
                        'amount' => $item->amount,
                        'type' => $customerTransaction->type == 'bill' ? 'general_debit' : 'general_credit',
                        'description' => $item->description,
                        'current_balance' => $customerTransaction->current_balance,
                        'bill' => $customerTransaction->bill ?? null,
                        'method' => $customerTransaction->method ?? null,
                        'entry_type' => $customerTransaction->type == 'bill' ? 'Debit Entry' : 'Credit Entry',
                    ]);
                }
            }
        }
        
        // Apply date filters to regular transactions
        if ($trans_from && $trans_to) {
            $transactionsQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
        } elseif ($trans_from) {
            $transactionsQuery->where('transaction_date', '>=', $trans_from);
        } elseif ($trans_to) {
            $transactionsQuery->where('transaction_date', '<=', $trans_to);
        }
        
        $regularTransactions = $transactionsQuery->orderBy('transaction_date', 'DESC')->get();
        
        // Merge and sort all transactions by date (newest first for display, but we need oldest first for statement)
        $allTransactions = $regularTransactions->concat($generalEntries);
        
        // For bank statement, we need transactions in ASCENDING order (oldest first)
        $allTransactions = $allTransactions->sortBy('transaction_date')->values();

        // Calculate running balance (same logic as view but in forward order)
        $runningBalance = 0;
        foreach ($allTransactions as $transaction) {
            if ($transaction->type == 'bill' || $transaction->type == 'general_debit') {
                $runningBalance += floatval($transaction->amount);
            } elseif ($transaction->type == 'payment' || $transaction->type == 'general_credit') {
                $runningBalance -= floatval($transaction->amount);
            } elseif ($transaction->type == 'balance') {
                $runningBalance = floatval($transaction->amount);
            }
            $transaction->current_balance = $runningBalance;
        }

        // Fetch Company Settings
        $companySettings = null;
        if (Schema::hasTable('companies')) {
            $companySettings = DB::table('companies')->first();
        }
        
        if (!$companySettings) {
            $companySettings = (object)[
                'name' => 'Food Impex',
                'logo' => null,
                'address' => 'Main Road, Sialkot, Pakistan',
                'mobile' => '+92 300 0000000',
            ];
        }

        return view('admin.pages.customers.bank-statement-pdf', compact(
            'customer',
            'allTransactions',
            'companySettings',
            'trans_from',
            'trans_to'
        ));

    } catch (\Exception $e) {
        \Log::error('Bank Statement Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to load statement: ' . $e->getMessage());
    }
}

/**
 * Generate Bank Statement PDF - Using Same Logic as view.blade.php
 */
public function bankStatementPdf($uuid)
{
    try {
        $customer = Customer::where('uuid', $uuid)->first();
        
        if (!$customer) {
            return redirect()->route('customers.list')->with('error', 'Customer not found');
        }

        // Get request filters (same as view.blade.php)
        $bill_from = request()->bill_from;
        $bill_to = request()->bill_to;
        $trans_from = request()->trans_from;
        $trans_to = request()->trans_to;

        // Get regular transactions (bills, payments, balance)
        $transactionsQuery = $customer->customerTransactions();
        
        // Apply date filters to regular transactions
        if ($trans_from && $trans_to) {
            $transactionsQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
        } elseif ($trans_from) {
            $transactionsQuery->where('transaction_date', '>=', $trans_from);
        } elseif ($trans_to) {
            $transactionsQuery->where('transaction_date', '<=', $trans_to);
        }
        
        $regularTransactions = $transactionsQuery->orderBy('transaction_date', 'ASC')->orderBy('id', 'ASC')->get();
        
        // Get general entries from daybooks
        $generalEntries = collect([]);
        
        if (Schema::hasTable('daybooks')) {
            $generalEntriesQuery = DB::table('daybooks')
                ->where('type', 'transaction')
                ->where(function($query) use ($customer) {
                    $query->where('debit_type', 'customer')
                          ->where('debit_id', $customer->id)
                          ->orWhere(function($q) use ($customer) {
                              $q->where('credit_type', 'customer')
                                ->where('credit_id', $customer->id);
                          });
                });
            
            // Apply date filters to general entries
            if ($trans_from && $trans_to) {
                $generalEntriesQuery->whereBetween('transaction_date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $generalEntriesQuery->where('transaction_date', '>=', $trans_from);
            } elseif ($trans_to) {
                $generalEntriesQuery->where('transaction_date', '<=', $trans_to);
            }
            
            $generalEntryData = $generalEntriesQuery->orderBy('transaction_date', 'ASC')->orderBy('id', 'ASC')->get();
            
            foreach ($generalEntryData as $item) {
                // Determine if this is debit or credit for the customer
                $isDebit = ($item->debit_type == 'customer' && $item->debit_id == $customer->id);
                $isCredit = ($item->credit_type == 'customer' && $item->credit_id == $customer->id);
                
                if ($isDebit || $isCredit) {
                    $generalEntries->push((object)[
                        'id' => $item->id,
                        'uuid' => 'daybook_' . $item->id,
                        'transaction_date' => $item->transaction_date,
                        'amount' => $item->amount,
                        'type' => $isDebit ? 'debit' : 'credit',
                        'transaction_type' => $isDebit ? 'debit' : 'credit',
                        'description' => $item->description,
                        'current_balance' => 0, // Will be recalculated
                        'bill' => null,
                        'method' => null,
                        'entry_type' => $isDebit ? 'Debit Entry' : 'Credit Entry',
                        'created_at' => $item->created_at,
                    ]);
                }
            }
        }
        
        // Merge all transactions
        $allTransactions = $regularTransactions->concat($generalEntries);
        
        // Sort by date ASCENDING (oldest first) for proper running balance calculation
        $allTransactions = $allTransactions->sortBy(function($transaction) {
            return $transaction->transaction_date;
        })->values();
        
        // Calculate running balance correctly (FORWARD order)
        $runningBalance = 0;
        foreach ($allTransactions as $transaction) {
            $amount = floatval($transaction->amount);
            $type = $transaction->type ?? '';
            $transactionType = $transaction->transaction_type ?? '';
            
            // Determine if this is a DEBIT or CREDIT
            // DEBIT = Customer owes us (Sales/Bill) - INCREASES balance
            // CREDIT = Customer paid us (Payment) - DECREASES balance
            $isDebit = false;
            $isCredit = false;
            
            if ($type == 'debit' || $transactionType == 'debit') {
                $isDebit = true;
            } elseif ($type == 'credit' || $transactionType == 'credit') {
                $isCredit = true;
            } elseif ($type == 'bill') {
                $isDebit = true;
            } elseif ($type == 'payment') {
                $isCredit = true;
            } elseif ($type == 'balance') {
                // Opening balance - positive means credit (customer has credit), negative means debit (customer owes)
                if ($amount > 0) {
                    $isCredit = true;
                } else {
                    $isDebit = true;
                    $amount = abs($amount);
                }
            }
            
            // Update running balance
            if ($isDebit) {
                $runningBalance += $amount;
            } elseif ($isCredit) {
                $runningBalance -= $amount;
            }
            
            $transaction->current_balance = $runningBalance;
        }
        
        // Fetch Company Settings
        $companySettings = null;
        if (Schema::hasTable('companies')) {
            $companySettings = DB::table('companies')->first();
        }
        
        if (!$companySettings) {
            $companySettings = (object)[
                'name' => 'Food Impex',
                'logo' => null,
                'address' => 'Main Road, Sialkot, Pakistan',
                'mobile' => '+92 300 0000000',
            ];
        }

        $pdf = Pdf::loadView('admin.pages.customers.bank-statement-pdf', compact(
            'customer',
            'allTransactions',
            'companySettings',
            'trans_from',
            'trans_to'
        ));

        return $pdf->download('customer-statement-' . preg_replace('/[^A-Za-z0-9-]/', '', $customer->name) . '.pdf');

    } catch (\Exception $e) {
        \Log::error('Bank Statement PDF Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}
   
}