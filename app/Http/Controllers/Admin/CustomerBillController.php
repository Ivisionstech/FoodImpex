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
                $bill->customer->increment('balance', $bill->grand_total ?? $bill->total_amount ?? 0);
                
                // Create customer transaction record
                CustomerTransaction::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => $bill->customer->id,
                    'transaction_date' => $bill->bill_date,
                    'amount' => $bill->grand_total ?? $bill->total_amount ?? 0,
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
                $bill->customer->decrement('balance', $bill->grand_total ?? $bill->total_amount ?? 0);
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

    /**
     * Display list of invoices
     */
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
        
        // Get the latest bill number for reference
        $lastBill = CustomerBill::orderBy('id', 'desc')->first();
        $nextBillNumber = $lastBill ? $lastBill->id + 1 : 1;
        
        return view('admin.pages.customers.bills.new_create', compact('customers', 'products', 'nextBillNumber'));
    }

    /**
     * Store a new invoice with products and extra charges
     * Data is saved to: customer_bills, customer_bill_products, customer_bill_extra_charges, customer_transactions
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Log incoming request data for debugging
            Log::info('Store Invoice Request Data:', $request->all());
            
            // Validate request
            $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'bill_date' => 'required|date',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:products,id',
                'products.*.quantity' => 'required|numeric|min:1',
                'products.*.total_weight' => 'nullable|numeric|min:0',
                'products.*.bardana_weight' => 'nullable|numeric|min:0',
                'products.*.net_weight' => 'nullable|numeric|min:0',
                'products.*.rate_per_40kg' => 'nullable|numeric|min:0',
                'products.*.total' => 'required|numeric|min:0',
            ]);
            
            // Calculate totals
            $totalAmount = 0;
            $profit = 0;
            
            // Create the bill in customer_bills table
            $bill = new CustomerBill();
            $bill->customer_id = $request->customer_id;
            $bill->bill_date = $request->bill_date ?? now();
            $bill->payment_terms = $request->payment_terms ?? '100% IN 30 DAYS';
            $bill->type = $request->type ?? 'new bill';
            $bill->status = 'pending';
            $bill->approval_status = 'pending';
            $bill->grand_total = 0;
            $bill->profit = 0;
            $bill->paid_amount = 0;
            $bill->uuid = (string) Str::uuid();
            $bill->save();
            
            Log::info('Bill Created in customer_bills:', [
                'bill_id' => $bill->id, 
                'uuid' => $bill->uuid,
                'grand_total' => $bill->grand_total
            ]);
            
            // Save products to customer_bill_products table
            foreach ($request->products as $index => $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $quantity = (float) ($productData['quantity'] ?? 0);
                $totalWeight = (float) ($productData['total_weight'] ?? 0);
                $bardanaWeight = (float) ($productData['bardana_weight'] ?? 0);
                $netWeight = (float) ($productData['net_weight'] ?? ($totalWeight - $bardanaWeight));
                $ratePer40kg = (float) ($productData['rate_per_40kg'] ?? 0);
                $lineTotal = (float) ($productData['total'] ?? 0);
                $price = (float) ($productData['price'] ?? $product->price ?? 0);
                
                // Calculate rate per 40kg if not provided
                if ($ratePer40kg == 0 && $lineTotal > 0 && $quantity > 0) {
                    $ratePer40kg = ($lineTotal / $quantity);
                }
                
                // Create record in customer_bill_products table
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
                    'price' => $price,
                    'rate_per_40kg' => $ratePer40kg,
                    'total' => $lineTotal,
                ]);
                
                Log::info('Product Saved to customer_bill_products:', [
                    'customer_bill_id' => $bill->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                    'bill_product_id' => $billProduct->id
                ]);
                
                $profit += $lineTotal;
                $totalAmount += $lineTotal;
            }
            
            // Save extra charges to customer_bill_extra_charges table
            $extraChargesTotal = 0;
            if ($request->has('extra_charges') && is_array($request->extra_charges)) {
                foreach ($request->extra_charges as $chargeData) {
                    if (!empty($chargeData['name']) && isset($chargeData['amount']) && $chargeData['amount'] > 0) {
                        $extraCharge = CustomerBillExtraCharge::create([
                            'uuid' => (string) Str::uuid(),
                            'customer_bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => (float) $chargeData['amount'],
                        ]);
                        $extraChargesTotal += (float) $chargeData['amount'];
                        
                        Log::info('Extra Charge Saved to customer_bill_extra_charges:', [
                            'customer_bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => $chargeData['amount']
                        ]);
                    }
                }
            }
            
            // Calculate final total (subtotal + extra charges)
            $finalTotal = $totalAmount + $extraChargesTotal;
            
            // Update bill with totals in customer_bills table
            $bill->update([
                'grand_total' => $finalTotal,
                'profit' => $profit,
            ]);
            
            Log::info('Bill Updated in customer_bills:', [
                'bill_id' => $bill->id,
                'grand_total' => $finalTotal,
                'profit' => $profit
            ]);
            
            // Create transaction record in customer_transactions table if customer exists
            if ($bill->customer_id) {
                $customer = Customer::find($bill->customer_id);
                if ($customer) {
                    $transaction = CustomerTransaction::create([
                        'uuid' => (string) Str::uuid(),
                        'customer_id' => $customer->id,
                        'transaction_date' => $bill->bill_date,
                        'amount' => $finalTotal,
                        'type' => 'bill',
                        'approval_status' => 'pending',
                        'description' => 'Sale Bill #' . $bill->id . ' created (Pending Approval)',
                        'current_balance' => $customer->balance,
                        'customer_bill_id' => $bill->id,
                    ]);
                    
                    Log::info('Transaction Created in customer_transactions:', [
                        'transaction_id' => $transaction->id,
                        'customer_bill_id' => $bill->id,
                        'amount' => $finalTotal
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Invoice Created Successfully:', [
                'bill_id' => $bill->id,
                'products_count' => count($request->products),
                'grand_total' => $finalTotal,
                'products_table' => 'customer_bill_products'
            ]);
            
            // Redirect to the show page with the bill UUID
            return redirect()
                ->route('customers.bills.new.show', $bill->uuid)
                ->with('success', 'Invoice #' . $bill->id . ' created successfully with ' . count($request->products) . ' products! (Pending Approval)');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Invoice Error: ' . $e->getMessage());
            Log::error('Request Data: ' . json_encode($request->all()));
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return redirect()
                ->back()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the invoice details (New Sales view)
     * Data is fetched from: customer_bills, customer_bill_products, products, customer_bill_extra_charges, customer_transactions
     */
    public function newsaleshow(string $uuid)
    {
        try {
            $bill = CustomerBill::with([
                'customer', 
                'billProducts',      // This loads from customer_bill_products table
                'billProducts.product', // This loads the product details from products table
                'extraCharges',      // This loads from customer_bill_extra_charges table
                'transactions'       // This loads from customer_transactions table
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();
            
            // Debug log
            Log::info('Bill Data Retrieved:', [
                'bill_id' => $bill->id,
                'bill_uuid' => $bill->uuid,
                'products_count' => $bill->billProducts->count(),
                'extra_charges_count' => $bill->extraCharges->count(),
                'transactions_count' => $bill->transactions->count(),
                'grand_total' => $bill->grand_total
            ]);
            
            // Log product details for debugging
            if ($bill->billProducts->count() > 0) {
                foreach ($bill->billProducts as $product) {
                    Log::info('Product in bill from customer_bill_products:', [
                        'bill_product_id' => $product->id,
                        'customer_bill_id' => $product->customer_bill_id,
                        'product_id' => $product->product_id,
                        'product_name' => $product->product->name ?? 'Unknown',
                        'quantity' => $product->quantity,
                        'total' => $product->total
                    ]);
                }
            } else {
                Log::warning('No products found in customer_bill_products for bill #' . $bill->id);
            }
            
            return view('admin.pages.customers.bills.new_show', compact('bill'));
            
        } catch (\Exception $e) {
            Log::error('Error showing bill: ' . $e->getMessage());
            return redirect()
                ->route('bills.list')
                ->with('error', 'Bill not found or error loading data: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing invoice
     */
    public function update(Request $request, string $uuid): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $bill = CustomerBill::where('uuid', $uuid)->firstOrFail();

            $oldGrandTotal = $bill->grand_total ?? $bill->total_amount ?? 0;
            $oldCustomerId = $bill->customer_id;
            $oldBillProducts = $bill->billProducts()->with('product')->get();

            // Restore stock for old products if bill was approved
            foreach ($oldBillProducts as $oldProduct) {
                $product = $oldProduct->product;
                if ($bill->approval_status == 'approved') {
                    $product->increment('stock', $oldProduct->quantity);
                }
            }

            // Reverse customer balance if bill was approved
            if ($oldCustomerId && $bill->approval_status == 'approved') {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->decrement('balance', $oldGrandTotal);
                }
            }

            // Delete old products and extra charges
            $bill->billProducts()->delete();
            $bill->extraCharges()->delete();

            // Update bill details
            $bill->update([
                'customer_id' => $request->customer_id ?: null,
                'bill_date' => $request->bill_date,
                'payment_terms' => $request->input('payment_terms'),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'type' => $request->input('type', $bill->type),
                'approval_status' => 'pending', // Reset approval status
            ]);

            $totalAmount = 0;
            $profit = 0;

            // Create new bill products in customer_bill_products table
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
                    'rate_per_40kg' => $productData['rate_per_40kg'] ?? 0,
                    'total' => $lineTotal,
                ]);

                $profit += $lineTotal;
                $totalAmount += $lineTotal;
            }

            // Create extra charges in customer_bill_extra_charges table
            $extraChargesTotal = 0;
            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    if (!empty($chargeData['name']) && isset($chargeData['amount']) && $chargeData['amount'] > 0) {
                        CustomerBillExtraCharge::create([
                            'uuid' => (string) Str::uuid(),
                            'customer_bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => (float) $chargeData['amount'],
                        ]);
                        $extraChargesTotal += (float) $chargeData['amount'];
                    }
                }
            }

            // Calculate final total
            $finalTotal = $totalAmount + $extraChargesTotal;

            // Update bill total in customer_bills table
            $bill->update([
                'grand_total' => $finalTotal,
                'profit' => $profit
            ]);

            DB::commit();

            return redirect()
                ->route('bills.list')
                ->with('success', 'Bill updated successfully and requires re-approval.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update bill: ' . $e->getMessage());
        }
    }

    /**
     * Show invoice details (Main view)
     * Data is fetched from: customer_bills, customer_bill_products, products, customer_bill_extra_charges, customer_transactions
     */
    public function show(string $uuid)
    {
        try {
            $bill = CustomerBill::with([
                'customer', 
                'billProducts',      // This loads from customer_bill_products table
                'billProducts.product', // This loads the product details from products table
                'extraCharges',      // This loads from customer_bill_extra_charges table
                'transactions'       // This loads from customer_transactions table
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();
            
            // Debug: Check if products exist in customer_bill_products
            if ($bill->billProducts->count() > 0) {
                Log::info('Products found in customer_bill_products for bill #' . $bill->id . ': ' . $bill->billProducts->count());
            } else {
                Log::warning('No products found in customer_bill_products for bill #' . $bill->id);
            }
            
            return view('admin.pages.customers.bills.show', compact('bill'));
            
        } catch (\Exception $e) {
            Log::error('Error showing bill: ' . $e->getMessage());
            return redirect()
                ->route('bills.list')
                ->with('error', 'Bill not found: ' . $e->getMessage());
        }
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(string $uuid)
    {
        $bill = CustomerBill::with(['billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = PDF::loadView('admin.pages.customers.bills.pdf', compact('bill'));
        return $pdf->download('customer-bill-' . $bill->uuid . '.pdf');
    }

    /**
     * Download new invoice as PDF
     */
    public function downloadNewPdf(string $uuid)
    {
        $bill = CustomerBill::with(['billProducts.product', 'extraCharges', 'transactions'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $pdf = PDF::loadView('admin.pages.customers.bills.newpdf', compact('bill'));
        return $pdf->download('customer-bill-new-' . $bill->uuid . '.pdf');
    }

    /**
     * Show receive payment form
     */
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

    /**
     * Store received payment
     */
    public function storeReceivePayment(Request $request, string $uuid)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $amount = floatval($request->amount);
            
            // Get current customer balance
            $oldBalance = $customer->balance;
            
            // Calculate new balance (payment DECREASES what customer owes)
            $newBalance = $oldBalance + $amount;
            
            // Create payment with PENDING status but UPDATE BALANCE IMMEDIATELY
            $customerTransaction = CustomerTransaction::create([
                'uuid' => (string) Str::uuid(),
                'customer_id' => $customer->id,
                'transaction_date' => $request->transaction_date,
                'amount' => $amount,
                'type' => 'payment',
                'approval_status' => 'pending',
                'receive_via' => $request->receive_via,
                'bank_id' => $request->bank_id,
                'description' => 'Payment received from ' . $customer->name . ' (Pending Approval)',
                'current_balance' => $newBalance,
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
                    'balance' => $bank->account_balance,
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
                    'balance' => $cash->balance,
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

    /**
     * Show receive payment details
     */
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
            
            // Get ONLY customer transactions
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
     * Display Bank Statement as HTML Page
     */
    public function bankStatementHtml(string $uuid, Request $request)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            // Get ONLY customer transactions
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
     * Download Bank Statement as PDF
     */
    public function bankStatementPdf(string $uuid, Request $request)
    {
        try {
            $customer = Customer::where('uuid', $uuid)->firstOrFail();
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            
            // Get ONLY customer transactions
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