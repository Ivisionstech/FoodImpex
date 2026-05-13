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
use App\Models\GeneralEntry;
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
     * Approve a pending invoice (Admin only) - Updates Stock & Customer Balance
     */
    public function approveBill($uuid)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can approve invoices.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $bill = CustomerBill::with(['billProducts.product', 'customer'])
                ->where('uuid', $uuid)
                ->firstOrFail();
            
            if ($bill->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice already approved.'
                ], 400);
            }
            
            // Update approval status
            $bill->approval_status = 'approved';
            $bill->save();
            
            // NOW DEDUCT STOCK FOR EACH PRODUCT IN THE INVOICE
            foreach ($bill->billProducts as $billProduct) {
                $product = $billProduct->product;
                $quantity = (float)$billProduct->quantity;
                
                if ($product) {
                    // Check if sufficient stock available
                    if ($product->stock < $quantity) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Required: {$quantity}");
                    }
                    
                    // Deduct stock
                    $product->decrement('stock', $quantity);
                    
                    // Record in Stock History
                    StockHistory::create([
                        'uuid' => (string) Str::uuid(),
                        'date' => $bill->bill_date,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'type' => 'out',
                        'current_stock' => $product->stock,
                        'description' => 'Invoice approved: Bill #' . $bill->id . ' from ' . ($bill->customer->name ?? 'Customer'),
                    ]);
                }
            }
            
            // Update customer balance (increase balance - customer owes money)
            if ($bill->customer) {
                $bill->customer->increment('balance', $bill->total_amount);
                
                // Create customer transaction record
                CustomerTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $bill->customer->id,
                    'transaction_date' => $bill->bill_date,
                    'amount' => $bill->total_amount,
                    'type' => 'bill',
                    'approval_status' => 'approved',
                    'description' => 'Sale Bill #' . $bill->id . ' approved',
                    'current_balance' => $bill->customer->balance,
                    'customer_bill_id' => $bill->id,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice approved successfully. Stock and customer balance updated.'
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
     * Approve a pending payment (Admin only) - Updates Customer Balance & Bank/Cash
     */
   public function approvePayment($uuid)
{
    if (!$this->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Access Denied. Only Admin can approve payments.'
        ], 403);
    }

    try {
        DB::beginTransaction();
        
        $payment = CustomerTransaction::where('uuid', $uuid)->firstOrFail();
        
        if ($payment->approval_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already approved.'
            ], 400);
        }
        
        $payment->approval_status = 'approved';
        $payment->save();
        
        // Balance is already updated when payment was created
        // Just update Bank or Cash balance now
        $customer = $payment->customer;
        
        if ($payment->receive_via == 'bank') {
            $bank = Bank::findOrFail($payment->bank_id);
            $bank->increment('account_balance', $payment->amount);
            
            // Update bank transaction record
            BankTransaction::where('customer_transaction_id', $payment->id)->update([
                'balance' => $bank->account_balance,
                'description' => 'Payment received from ' . ($customer->name ?? 'Customer') . ' (Approved)',
            ]);
        } else {
            $cash = Cash::first();
            $cash->increment('balance', $payment->amount);
            
            // Update cash transaction record
            CashTransaction::where('customer_transaction_id', $payment->id)->update([
                'balance' => $cash->balance,
                'description' => 'Payment received from ' . ($customer->name ?? 'Customer') . ' (Approved)',
            ]);
        }
        
        // Update daybook description
        Daybook::where('customer_transaction_id', $payment->id)->update([
            'description' => 'Payment received from ' . ($customer->name ?? 'Customer') . ' (Approved)',
            'approval_status' => 'approved',
        ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Payment approved successfully.'
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Approve Payment Error: ' . $e->getMessage());
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
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can delete invoices.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $bill = CustomerBill::where('uuid', $uuid)->firstOrFail();
            
            // Restore stock if invoice was approved
            foreach ($bill->billProducts as $billProduct) {
                $product = $billProduct->product;
                if ($product && $bill->approval_status == 'approved') {
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
            
            // Reverse customer balance if invoice was approved
            if ($bill->customer && $bill->approval_status == 'approved') {
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

    /**
     * Delete a payment (Admin only)
     */
    public function deletePayment($uuid)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can delete payments.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $payment = CustomerTransaction::where('uuid', $uuid)->firstOrFail();
            
            // Reverse customer balance if payment was approved
            if ($payment->approval_status == 'approved') {
                $customer = $payment->customer;
                $customer->increment('balance', $payment->amount);
                
                // Reverse bank or cash balance
                if ($payment->receive_via == 'bank') {
                    $bank = Bank::findOrFail($payment->bank_id);
                    $bank->decrement('account_balance', $payment->amount);
                } else {
                    $cash = Cash::first();
                    $cash->decrement('balance', $payment->amount);
                }
            }
            
            // Delete related records
            CustomerTransactionImage::where('customer_transaction_id', $payment->id)->delete();
            Daybook::where('customer_transaction_id', $payment->id)->delete();
            BankTransaction::where('customer_transaction_id', $payment->id)->delete();
            CashTransaction::where('customer_transaction_id', $payment->id)->delete();
            
            $payment->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Payment Error: ' . $e->getMessage());
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
            
            $query = CustomerBill::with('customer')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc');
            
            if ($from_date && $to_date) {
                $query->whereBetween('bill_date', [$from_date, $to_date]);
            } elseif ($from_date) {
                $query->whereDate('bill_date', '>=', $from_date);
            } elseif ($to_date) {
                $query->whereDate('bill_date', '<=', $to_date);
            }
            
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
     * Display list of received payments
     */
    public function paymentsList(Request $request)
    {
        try {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $approvalStatus = $request->approval_status;

            $query = CustomerTransaction::with('customer')
                ->where('type', 'payment')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc');

            if ($fromDate) {
                $query->whereDate('transaction_date', '>=', $fromDate);
            }
            if ($toDate) {
                $query->whereDate('transaction_date', '<=', $toDate);
            }
            if ($approvalStatus && in_array($approvalStatus, ['pending', 'approved'])) {
                $query->where('approval_status', $approvalStatus);
            }

            $payments = $query->paginate(10);
            
            return view('admin.pages.customers.received-payments.list', compact('payments', 'fromDate', 'toDate'));

        } catch (\Exception $e) {
            Log::error('Error in paymentsList: ' . $e->getMessage());
            $payments = collect([]);
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            return view('admin.pages.customers.received-payments.list', compact('payments', 'fromDate', 'toDate'))
                ->with('error', 'Error loading transactions: ' . $e->getMessage());
        }
    }

    /**
     * Show the New Sales Invoice form
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
            $bill->approval_status = 'pending';
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
            }

            if ($oldCustomerId && $bill->approval_status == 'approved') {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->decrement('balance', $oldTotalAmount);
                }
            }

            $bill->billProducts()->delete();
            $bill->extraCharges()->delete();

            $bill->update([
                'customer_id' => $request->customer_id ?: null,
                'bill_date' => $request->bill_date,
                'payment_terms' => $request->input('payment_terms'),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'type' => $request->input('type', $bill->type),
                'approval_status' => 'pending',
            ]);

            $totalAmount = 0;
            $profit = 0;

            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantity = (int) ($productData['quantity'] ?? 0);
                $totalWeight = (float) ($productData['total_weight'] ?? 0);
                $bardanaWeight = (float) ($productData['bardana_weight'] ?? 0);
                $netWeight = max(0, (float) ($productData['net_weight'] ?? ($totalWeight - $bardanaWeight)));
                $lineTotal = isset($productData['total']) ? (float) $productData['total'] : 0;

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
                    'total' => $lineTotal,
                ]);

                $profit += $lineTotal;
                $totalAmount += $lineTotal;
            }

            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    CustomerBillExtraCharge::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_bill_id' => $bill->id,
                        'name' => $chargeData['name'],
                        'amount' => $chargeData['amount'],
                    ]);
                    $totalAmount -= (float) $chargeData['amount'];
                }
            }

            $totalAmount = max(0, $totalAmount);

            $bill->update([
                'total_amount' => $totalAmount,
                'profit' => $profit
            ]);

            if ($bill->customer_id && $bill->approval_status == 'approved') {
                $customer = Customer::findOrFail($bill->customer_id);
                $customer->increment('balance', $totalAmount);
            }

            DB::commit();

            return redirect()
                ->route('bills.list')
                ->with('success', 'Bill updated successfully and requires re-approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update bill: ' . $e->getMessage());
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
            return view('admin.pages.customers.receive-payment', compact('customer', 'banks'));
        } catch (\Throwable $th) {
            Log::error('Failed to receive payment: ' . $th->getMessage());
            return redirect()->back()->with('error', 'Failed to receive payment: ' . $th->getMessage());
        }
    }

    public function storeReceivePayment(Request $request, string $uuid)
{
    try {
        DB::beginTransaction();

        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $amount = floatval($request->amount);
        
        // Get current customer balance
        $oldBalance = $customer->balance;
        
        // Calculate new balance (payment DECREASES what customer owes)
        // If customer owes us (positive balance), payment reduces it
        // If customer has credit (negative balance), payment increases credit (makes it more negative)
        $newBalance = $oldBalance + $amount;
        
        // Create payment with PENDING status but UPDATE BALANCE IMMEDIATELY
        $customerTransaction = CustomerTransaction::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
            'transaction_date' => $request->transaction_date,
            'amount' => $amount,
            'type' => 'payment',
            'approval_status' => 'pending', // Pending approval
            'receive_via' => $request->receive_via,
            'bank_id' => $request->bank_id,
            'description' => 'Payment received from ' . $customer->name . ' (Pending Approval)',
            'current_balance' => $newBalance, // Updated balance
        ]);
        
        // UPDATE CUSTOMER BALANCE IMMEDIATELY
        $customer->update(['balance' => $newBalance]);
        
        // Store bank or cash transaction (but don't update actual bank/cash balance yet)
        if ($request->receive_via == 'bank') {
            $bank = Bank::findOrFail($request->bank_id);
            BankTransaction::create([
                'bank_id' => $bank->id,
                'customer_transaction_id' => $customerTransaction->id,
                'amount' => $amount,
                'balance' => $bank->account_balance, // Current bank balance (not updated yet)
                'transaction_type' => 'credit',
                'description' => 'Payment received from ' . $customer->name . ' (Pending Approval)',
            ]);
        } else {
            $cash = Cash::first();
            CashTransaction::create([
                'cash_id' => $cash->id,
                'customer_transaction_id' => $customerTransaction->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'balance' => $cash->balance, // Current cash balance (not updated yet)
                'description' => 'Payment received from ' . $customer->name . ' (Pending Approval)',
            ]);
        }

        Daybook::create([
            'transaction_date' => $request->transaction_date,
            'description' => 'Payment received from ' . $customer->name . ' (Pending Approval)',
            'amount' => $amount,
            'customer_transaction_id' => $customerTransaction->id,
            'type' => 'transaction',
            'approval_status' => 'pending',
        ]);

        if ($request->hasFile('receipt_images')) {
            foreach ($request->file('receipt_images') as $image) {
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
            'message' => 'Payment recorded successfully! Customer balance updated.',
            'new_balance' => $newBalance
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
            return redirect()->back()->with('error', 'Failed to show receive payment: ' . $th->getMessage());
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
        
        // Get ONLY customer transactions (same as view page)
        $transactionsQuery = CustomerTransaction::where('customer_id', $customer->id);
        
        // Apply date filters
        if ($fromDate && $toDate) {
            $transactionsQuery->whereBetween('transaction_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $transactionsQuery->where('transaction_date', '>=', $fromDate);
        } elseif ($toDate) {
            $transactionsQuery->where('transaction_date', '<=', $toDate);
        }
        
        // Get transactions ordered by date
        $customerTransactions = $transactionsQuery->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        
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
 * Display Bank Statement as HTML Page - Same logic as view page
 */
public function bankStatementHtml(string $uuid, Request $request)
{
    try {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        
        // Get ONLY customer transactions (same as view page)
        $transactionsQuery = CustomerTransaction::where('customer_id', $customer->id);
        
        // Apply date filters (same as view page)
        if ($fromDate && $toDate) {
            $transactionsQuery->whereBetween('transaction_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $transactionsQuery->where('transaction_date', '>=', $fromDate);
        } elseif ($toDate) {
            $transactionsQuery->where('transaction_date', '<=', $toDate);
        }
        
        // Get transactions ordered by date (newest first for display - same as view page)
        $customerTransactions = $transactionsQuery->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        
        // Fetch Company Settings
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
 * Download Bank Statement as PDF - Same logic as view page
 */
public function bankStatementPdf(string $uuid, Request $request)
{
    try {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        
        // Get ONLY customer transactions (same as view page)
        $transactionsQuery = CustomerTransaction::where('customer_id', $customer->id);
        
        // Apply date filters (same as view page)
        if ($fromDate && $toDate) {
            $transactionsQuery->whereBetween('transaction_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $transactionsQuery->where('transaction_date', '>=', $fromDate);
        } elseif ($toDate) {
            $transactionsQuery->where('transaction_date', '<=', $toDate);
        }
        
        // Get transactions ordered by date (newest first for display - same as view page)
        $customerTransactions = $transactionsQuery->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        
        // Fetch Company Settings
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