<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\CustomerBill;
use App\Models\CustomerBillExtraCharge;
use App\Models\CustomerBillProduct;
use App\Models\CustomerTransaction;
use App\Models\CustomerTransactionImage;
use App\Models\Daybook;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CustomerBillController extends Controller
{
    /**
     * Check if current user is Admin
     */
    private function isAdmin()
    {
        return auth()->user()->role == 'admin';
    }

    /**
     * Approve a pending invoice (Admin only)
     */
    public function approveBill($uuid)
    {
        // Only admin can approve
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can approve invoices.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $bill = CustomerBill::where('uuid', $uuid)->firstOrFail();
            
            if ($bill->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already approved.'
                ], 400);
            }
            
            $bill->approval_status = 'approved';
            $bill->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice approved successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve Invoice Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an invoice (Admin only)
     */
    public function deleteInvoice($uuid)
    {
        // Only admin can delete
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can delete invoices.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $bill = CustomerBill::where('uuid', $uuid)->firstOrFail();
            
            // First, get all products to restore stock (if approved)
            foreach ($bill->billProducts as $billProduct) {
                $product = $billProduct->product;
                if ($product && $bill->approval_status == 'approved') {
                    // Restore stock
                    $product->increment('stock', $billProduct->quantity);
                }
                $billProduct->delete();
            }
            
            // Delete extra charges
            $bill->extraCharges()->delete();
            
            // Delete related customer transaction
            if ($bill->customerTransaction) {
                $bill->customerTransaction()->delete();
            }
            
            // Update customer balance (reverse the amount)
            if ($bill->customer) {
                $bill->customer->decrement('balance', $bill->total_amount);
            }
            
            // Delete the bill
            $bill->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Invoice Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $approval_status = $request->approval_status;
            
            // Build query with eager loading - order by latest first
            $query = CustomerBill::with('customer')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc');
            
            // Apply date filters if provided
            if ($from_date && $to_date) {
                $query->whereBetween('bill_date', [$from_date, $to_date]);
            } elseif ($from_date) {
                $query->whereDate('bill_date', '>=', $from_date);
            } elseif ($to_date) {
                $query->whereDate('bill_date', '<=', $to_date);
            }
            
            // Apply approval status filter
            if ($approval_status && in_array($approval_status, ['pending', 'approved'])) {
                $query->where('approval_status', $approval_status);
            }
            
            $bills = $query->paginate(10);
            
            return view('admin.pages.customers.bills.list', compact('bills', 'from_date', 'to_date'));
            
        } catch (\Exception $e) {
            \Log::error('Failed to list bills: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load bills: ' . $e->getMessage());
        }
    }

    /**
     * Display list of received payments and general entries
     */
    public function paymentsList(Request $request)
    {
        try {
            // Get filter parameters
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $type = $request->type;

            // DEFAULT: Only show data from 2026 onwards if no dates are specified
            if (!$fromDate && !$toDate) {
                $fromDate = '2026-01-01';
            }

            // Fetch customer payments (type = 'payment')
            $paymentsQuery = CustomerTransaction::with('customer')
                ->where('type', 'payment');

            if ($fromDate) {
                $paymentsQuery->whereDate('transaction_date', '>=', $fromDate);
            }
            if ($toDate) {
                $paymentsQuery->whereDate('transaction_date', '<=', $toDate);
            }

            $payments = $paymentsQuery->orderBy('transaction_date', 'desc')->get();

            // Initialize general entries collection
            $generalEntries = collect([]);
            
            // Only fetch general entries if not filtering by 'payments' only
            if ($type !== 'payments') {
                
                // SOURCE 1: Customer Bills (type = 'bill') - Sales
                $billEntries = CustomerTransaction::with('customer')
                    ->where('type', 'bill')
                    ->when($fromDate, function($q) use ($fromDate) {
                        return $q->whereDate('transaction_date', '>=', $fromDate);
                    })
                    ->when($toDate, function($q) use ($toDate) {
                        return $q->whereDate('transaction_date', '<=', $toDate);
                    })
                    ->orderBy('transaction_date', 'desc')
                    ->get()
                    ->map(function($item) {
                        return (object)[
                            'uuid' => $item->uuid,
                            'id' => $item->id,
                            'date' => $item->transaction_date,
                            'description' => $item->description ?? 'Sale to ' . ($item->customer->name ?? 'Customer'),
                            'amount' => $item->amount,
                            'type' => 'bill',
                            'type_label' => 'Sale Bill',
                            'type_badge' => 'success',
                            'reference' => $item->customer ? $item->customer->name : 'Customer',
                            'method' => 'Credit',
                            'is_payment' => false,
                            'source' => 'customer_bill',
                            'amount_class' => 'text-success',
                        ];
                    });
                
                $generalEntries = $generalEntries->concat($billEntries);
                
                // SOURCE 2: Customer Balance Entries (type = 'balance')
                $balanceEntries = CustomerTransaction::with('customer')
                    ->where('type', 'balance')
                    ->when($fromDate, function($q) use ($fromDate) {
                        return $q->whereDate('transaction_date', '>=', $fromDate);
                    })
                    ->when($toDate, function($q) use ($toDate) {
                        return $q->whereDate('transaction_date', '<=', $toDate);
                    })
                    ->orderBy('transaction_date', 'desc')
                    ->get()
                    ->map(function($item) {
                        return (object)[
                            'uuid' => $item->uuid,
                            'id' => $item->id,
                            'date' => $item->transaction_date,
                            'description' => $item->description ?? 'Balance Adjustment',
                            'amount' => $item->amount,
                            'type' => 'balance',
                            'type_label' => 'Opening Balance',
                            'type_badge' => 'warning',
                            'reference' => $item->customer ? $item->customer->name : 'Customer',
                            'method' => 'Adjustment',
                            'is_payment' => false,
                            'source' => 'customer_balance',
                            'amount_class' => 'text-info',
                        ];
                    });
                
                $generalEntries = $generalEntries->concat($balanceEntries);
                
                // SOURCE 3: Daybooks (General Entries)
                if (Schema::hasTable('daybooks')) {
                    $daybookEntries = DB::table('daybooks')
                        ->where('type', 'transaction')
                        ->when($fromDate, function($q) use ($fromDate) {
                            return $q->whereDate('transaction_date', '>=', $fromDate);
                        })
                        ->when($toDate, function($q) use ($toDate) {
                            return $q->whereDate('transaction_date', '<=', $toDate);
                        })
                        ->orderBy('transaction_date', 'desc')
                        ->get()
                        ->map(function($item) {
                            return (object)[
                                'uuid' => 'daybook_' . $item->id,
                                'id' => $item->id,
                                'date' => $item->transaction_date,
                                'description' => $item->description ?? 'General Entry',
                                'amount' => $item->amount,
                                'type' => 'transaction',
                                'type_label' => 'General Entry',
                                'type_badge' => 'info',
                                'reference' => 'System Entry',
                                'method' => 'Transfer',
                                'is_payment' => false,
                                'source' => 'daybook',
                                'amount_class' => 'text-primary',
                            ];
                        });
                    
                    $generalEntries = $generalEntries->concat($daybookEntries);
                }
            }

            // Sort all entries by date (newest first)
            $generalEntries = $generalEntries->sortByDesc('date')->values();

            return view('admin.pages.customers.received-payments.list', compact('payments', 'generalEntries', 'fromDate', 'toDate'));

        } catch (\Exception $e) {
            Log::error('Error in paymentsList: ' . $e->getMessage());
            
            $payments = collect([]);
            $generalEntries = collect([]);
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            return view('admin.pages.customers.received-payments.list', compact('payments', 'generalEntries', 'fromDate', 'toDate'))
                ->with('error', 'Error loading transactions: ' . $e->getMessage());
        }
    }

    /**
     * Show the New Sales Invoice form (newsalecreate).
     */
    public function newsalecreate()
    {
        $customers = Customer::all();
        $products = Product::where('stock', '>', 0)->get();
        return view('admin.pages.customers.bills.new_create', compact('customers', 'products'));
    }

    public function store(Request $request)
{
    try {
        DB::beginTransaction();
        
        $bill = new CustomerBill();
        $bill->customer_id = $request->customer_id;
        $bill->bill_date = $request->bill_date ?? now();
        $bill->payment_terms = $request->payment_terms ?? '100% IN 30 DAYS';
        $bill->total_amount = $request->grand_total ?? 0;
        $bill->type = $request->type ?? 'new bill';
        $bill->status = 'pending';
        $bill->approval_status = 'pending'; // IMPORTANT: Set to pending
        $bill->uuid = Str::uuid();
        $bill->save();
        
        DB::commit();
        
        return redirect()->route('bills.list')->with('success', 'Invoice #' . $bill->id . ' created successfully! (Pending Approval)');
        
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to create invoice: ' . $e->getMessage())->withInput();
    }
}
    public function newsaleshow(string $uuid)
    {
        $bill = CustomerBill::with(['customer', 'billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.pages.customers.bills.new_show', compact('bill'));
    }

    public function update(Request $request, string $uuid): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $bill = CustomerBill::where('uuid', $uuid)->firstOrFail();

            $oldTotalAmount = $bill->total_amount;
            $oldCustomerId = $bill->customer_id;
            $oldBillProducts = $bill->billProducts()->with('product')->get();

            foreach ($oldBillProducts as $oldProduct) {
                $product = $oldProduct->product;
                if ($bill->approval_status == 'approved') {
                    $product->increment('stock', $oldProduct->quantity);
                }

                StockHistory::where('product_id', $product->id)
                    ->where('type', 'out')
                    ->where('quantity', $oldProduct->quantity)
                    ->where('description', 'like', '%' . ($bill->customer->name ?? $bill->customer_name) . '%')
                    ->latest()
                    ->first()
                    ?->delete();
            }

            if ($oldCustomerId) {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->decrement('balance', $oldTotalAmount);

                    CustomerTransaction::where('customer_bill_id', $bill->id)
                        ->where('type', 'bill')
                        ->delete();
                }
            }

            $bill->billProducts()->delete();
            $bill->extraCharges()->delete();

            $request->validate([
                'payment_terms' => 'nullable|string',
            ]);

            $bill->update([
                'customer_id' => $request->customer_id ?: null,
                'bill_date' => $request->bill_date,
                'payment_terms' => $request->input('payment_terms'),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'type' => $request->input('type', $bill->type),
                'approval_status' => 'pending', // Reset to pending on edit
            ]);

            $totalAmount = 0;
            $profit = 0;

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);

                $quantity = (int) ($productData['quantity'] ?? 0);
                $totalWeight = (float) ($productData['total_weight'] ?? 0);
                $bardanaWeight = (float) ($productData['bardana_weight'] ?? 0);
                $netWeight = max(0, (float) ($productData['net_weight'] ?? ($totalWeight - $bardanaWeight)));

                $ratePer40 = isset($productData['rate_per_40kg']) ? (float) $productData['rate_per_40kg'] : null;
                $pricePerKg = isset($productData['price']) ? (float) $productData['price'] : null;

                if ($ratePer40 !== null && $ratePer40 > 0) {
                    $pricePerKg = $ratePer40 / 40;
                }

                $lineTotal = isset($productData['total_raw'])
                    ? (float) str_replace(',', '', (string) $productData['total_raw'])
                    : ($netWeight * (float) ($pricePerKg ?? 0));

                if ($product->stock < $quantity && $bill->approval_status == 'approved') {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $billProduct = CustomerBillProduct::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_bill_id' => $bill->id,
                    'product_id' => $product->id,
                    'description' => $productData['description'] ?? null,
                    'quantity' => $quantity,
                    'packing' => $productData['packing'] ?? null,
                    'total_weight' => $totalWeight,
                    'bardana_weight' => $bardanaWeight,
                    'net_weight' => $netWeight,
                    'price' => $pricePerKg,
                    'rate_per_40kg' => $ratePer40,
                    'total' => $lineTotal,
                ]);

                $profit += $quantity * (((float) ($pricePerKg ?? 0)) - (float) $product->purchase_price);

                if ($bill->approval_status == 'approved') {
                    StockHistory::create([
                        'uuid' => (string) Str::uuid(),
                        'date' => $request->bill_date,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'type' => 'out',
                        'current_stock' => $product->stock - $quantity,
                        'description' => 'Sold to ' . ($bill->customer->name ?? $bill->customer_name) . ' (Updated bill)',
                    ]);

                    $product->decrement('stock', $quantity);
                }

                $totalAmount += $billProduct->total;
            }

            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    $charge = CustomerBillExtraCharge::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_bill_id' => $bill->id,
                        'name' => $chargeData['name'],
                        'amount' => $chargeData['amount'],
                    ]);
                    $totalAmount -= $charge->amount;
                }
            }

            $totalAmount = max(0, $totalAmount);

            $bill->update([
                'total_amount' => $totalAmount,
                'profit' => $profit
            ]);

            if ($bill->customer_id) {
                $customer = Customer::findOrFail($bill->customer_id);
                $customer->increment('balance', $totalAmount);

                CustomerTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $customer->id,
                    'transaction_date' => $request->bill_date,
                    'amount' => $totalAmount,
                    'type' => 'bill',
                    'description' => 'Bill updated',
                    'current_balance' => $customer->balance,
                    'customer_bill_id' => $bill->id,
                ]);
            }

            DB::commit();

            if (($bill->type ?? null) === 'new bill') {
                return redirect()
                    ->route('new.bills.show', $bill->uuid)
                    ->with('success', 'Bill updated successfully.');
            }

            return redirect()
                ->route('customers.bills.show', $bill->uuid)
                ->with('success', 'Bill updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update bill: ' . $e->getMessage());
        }
    }

    public function show(string $uuid)
    {
        $bill = CustomerBill::with(['customer', 'billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('admin.pages.customers.bills.show', compact('bill'));
    }

    public function downloadPdf(string $uuid)
    {
        $bill = CustomerBill::with(['billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = PDF::loadView('admin.pages.customers.bills.pdf', compact('bill'));

        return $pdf->download('customer-bill-' . $bill->uuid . '.pdf');
    }

    public function downloadNewPdf(string $uuid)
    {
        $bill = CustomerBill::with(['billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = PDF::loadView('admin.pages.customers.bills.newpdf', compact('bill'));

        return $pdf->download('customer-bill-new-' . $bill->uuid . '.pdf');
    }

    public function receivePayment(string $uuid)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $banks = Bank::all();
            if ($customer) {
                return view('admin.pages.customers.receive-payment', compact('customer', 'banks'));
            } else {
                return redirect()->back()->with([
                    'status' => false,
                    'message' => 'Customer not found',
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('Failed to receive payment: ' . $th->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to receive payment: ' . $th->getMessage(),
            ]);
        }
    }

    public function storeReceivePayment(Request $request, string $uuid)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::where('uuid', $uuid)->firstOrFail();

            $customer->decrement('balance', $request->amount);

            $customerTransaction = CustomerTransaction::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'transaction_date' => $request->transaction_date,
                'amount' => $request->amount,
                'type' => 'payment',
                'receive_via' => $request->receive_via,
                'bank_id' => $request->bank_id,
                'description' => 'Payment received from ' . $customer->name,
                'current_balance' => $customer->balance,
            ]);

            if ($request->receive_via == 'bank') {
                $bank = Bank::findOrFail($request->bank_id);
                $bank->increment('account_balance', $request->amount);

                BankTransaction::create([
                    'bank_id' => $bank->id,
                    'customer_transaction_id' => $customerTransaction->id,
                    'amount' => $request->amount,
                    'balance' => $bank->account_balance,
                    'transaction_type' => 'credit',
                    'description' => 'Payment received from ' . $customer->name,
                ]);
            } else {
                $cash = Cash::first();
                $cash->increment('balance', $request->amount);

                CashTransaction::create([
                    'cash_id' => $cash->id,
                    'customer_transaction_id' => $customerTransaction->id,
                    'transaction_type' => 'credit',
                    'amount' => $request->amount,
                    'balance' => $cash->balance,
                    'description' => 'Payment received from ' . $customer->name,
                ]);
            }

            Daybook::create([
                'transaction_date' => $request->transaction_date,
                'description' => 'Payment received from ' . $customer->name,
                'amount' => $request->amount,
                'customer_transaction_id' => $customerTransaction->id,
                'type' => 'transaction',
            ]);

            if ($request->hasFile('receipt_images')) {
                $images = $request->file('receipt_images');
                foreach ($images as $image) {
                    $imagePath = $image->store('customer_transactions_payments', 'public');
                    CustomerTransactionImage::create([
                        'customer_transaction_id' => $customerTransaction->id,
                        'image' => $imagePath,
                        'date' => $request->transaction_date,
                        'customer_id' => $customer->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment received successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to receive payment: ' . $th->getMessage(),
            ]);
        }
    }

    public function showReceivePayment($uuid)
    {
        try {
            $transaction = CustomerTransaction::where('uuid', $uuid)->firstOrFail();
            return view('admin.pages.customers.receive-details', compact('transaction'));
        } catch (\Throwable $th) {
            Log::error('Failed to show receive payment: ' . $th->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to show receive payment: ' . $th->getMessage(),
            ]);
        }
    }

    /**
     * Generate Bank Statement PDF Report for Customer
     */
    public function bankStatementReport(string $uuid, Request $request)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            $query = $customer->customerTransactions()
                ->with(['bill.billProducts.product']);
            
            if ($fromDate && $toDate) {
                $query->whereBetween('transaction_date', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->whereDate('transaction_date', '>=', $fromDate);
            } elseif ($toDate) {
                $query->whereDate('transaction_date', '<=', $toDate);
            }
            
            $customerTransactions = $query->orderBy('transaction_date', 'DESC')->get();
            
            $companySettings = null;
            if (Schema::hasTable('settings')) {
                $companySettings = DB::table('settings')->first();
            }
            
            if (!$companySettings) {
                $companySettings = (object)[
                    'name' => 'Food Impex',
                    'logo' => null,
                    'address' => 'Main Road, Sialkot, Pakistan',
                    'mobile' => '+92 300 0000000',
                ];
            }
            
            $pdf = PDF::loadView('admin.pages.customers.bank-statement-pdf', compact(
                'customer', 
                'customerTransactions', 
                'fromDate', 
                'toDate', 
                'companySettings'
            ));
            
            return $pdf->download("bank-statement-" . preg_replace('/[^A-Za-z0-9-]/', '', $customer->name) . ".pdf");
            
        } catch (\Exception $e) {
            \Log::error('Bank statement PDF error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Display Bank Statement as HTML Page
     */
    public function bankStatementHtml(string $uuid, Request $request)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            $regularTransactions = $customer->customerTransactions()
                ->with(['bill.billProducts.product'])
                ->orderBy('transaction_date', 'DESC')
                ->get();
            
            $generalEntries = collect([]);
            
            if (Schema::hasTable('daybooks')) {
                $daybookQuery = DB::table('daybooks')
                    ->where('type', 'transaction')
                    ->whereNotNull('customer_transaction_id')
                    ->whereIn('customer_transaction_id', function($query) use ($customer) {
                        $query->select('id')
                            ->from('customer_transactions')
                            ->where('customer_id', $customer->id);
                    });
                
                if ($fromDate && $toDate) {
                    $daybookQuery->whereBetween('transaction_date', [$fromDate, $toDate]);
                } elseif ($fromDate) {
                    $daybookQuery->whereDate('transaction_date', '>=', $fromDate);
                } elseif ($toDate) {
                    $daybookQuery->whereDate('transaction_date', '<=', $toDate);
                }
                
                $daybookEntries = $daybookQuery->orderBy('transaction_date', 'DESC')->get();
                
                foreach ($daybookEntries as $item) {
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
                            'reference' => $item->reference_no ?? null,
                            'remarks' => $item->remarks ?? null,
                        ]);
                    }
                }
            }
            
            $allTransactions = $regularTransactions->concat($generalEntries);
            $customerTransactions = $allTransactions->sortByDesc(function($item) {
                return $item->transaction_date;
            })->values();
            
            $companySettings = null;
            if (Schema::hasTable('settings')) {
                $companySettings = DB::table('settings')->first();
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
                'customerTransactions', 
                'fromDate', 
                'toDate', 
                'companySettings'
            ));
            
        } catch (\Exception $e) {
            \Log::error('bankStatementHtml error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load statement: ' . $e->getMessage());
        }
    }

    /**
     * Download Bank Statement as PDF
     */
    public function bankStatementPdf(string $uuid, Request $request)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            $regularTransactions = $customer->customerTransactions()
                ->with(['bill.billProducts.product'])
                ->orderBy('transaction_date', 'DESC')
                ->get();
            
            $generalEntries = collect([]);
            
            if (Schema::hasTable('daybooks')) {
                $daybookQuery = DB::table('daybooks')
                    ->where('type', 'transaction')
                    ->whereNotNull('customer_transaction_id')
                    ->whereIn('customer_transaction_id', function($query) use ($customer) {
                        $query->select('id')
                            ->from('customer_transactions')
                            ->where('customer_id', $customer->id);
                    });
                
                if ($fromDate && $toDate) {
                    $daybookQuery->whereBetween('transaction_date', [$fromDate, $toDate]);
                } elseif ($fromDate) {
                    $daybookQuery->whereDate('transaction_date', '>=', $fromDate);
                } elseif ($toDate) {
                    $daybookQuery->whereDate('transaction_date', '<=', $toDate);
                }
                
                $daybookEntries = $daybookQuery->orderBy('transaction_date', 'DESC')->get();
                
                foreach ($daybookEntries as $item) {
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
                            'reference' => $item->reference_no ?? null,
                        ]);
                    }
                }
            }
            
            $allTransactions = $regularTransactions->concat($generalEntries);
            $customerTransactions = $allTransactions->sortByDesc(function($item) {
                return $item->transaction_date;
            })->values();
            
            $companySettings = null;
            if (Schema::hasTable('settings')) {
                $companySettings = DB::table('settings')->first();
            }
            
            if (!$companySettings) {
                $companySettings = (object)[
                    'name' => 'Food Impex',
                    'logo' => null,
                    'address' => 'Main Road, Sialkot, Pakistan',
                    'mobile' => '+92 300 0000000',
                ];
            }
            
            $pdf = PDF::loadView('admin.pages.customers.bank-statement-pdf', compact(
                'customer', 
                'customerTransactions', 
                'fromDate', 
                'toDate', 
                'companySettings'
            ));
            
            return $pdf->download("bank-statement-{$customer->name}.pdf");
            
        } catch (\Exception $e) {
            \Log::error('bankStatementPdf error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}