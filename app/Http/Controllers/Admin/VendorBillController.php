<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\Vendor\SendPaymentRequest;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\BillExtraCharge;
use App\Models\BillAdditionalCharge;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Daybook;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Vendor;
use App\Models\VendorTransaction;
use App\Models\VendorTransactionImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VendorBillController extends Controller
{
    private function isAdmin()
    {
        return auth()->user()->role == 'admin';
    }

    /**
     * Display the general bill creation form with Vendors and Products.
     */
    public function generalCreate()
    {
        $vendors = Vendor::orderBy('company_name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('admin.pages.vendors.bills.create_general', compact('vendors', 'products'));
    }

    /**
     * Display the second general bill creation form.
     */
    public function generalCreate2()
    {
        $vendors = Vendor::orderBy('company_name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('admin.pages.vendors.bills.create_general_2', compact('vendors', 'products'));
    }

    /**
     * Approve a pending bill and update stock (Admin only)
     */
    public function approveBill($uuid)
    {
        try {
            DB::beginTransaction();
            
            $bill = Bill::where('uuid', $uuid)
                ->with(['billProducts.product', 'vendor'])
                ->firstOrFail();
            
            if ($bill->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill already approved.'
                ], 400);
            }
            
            $bill->approval_status = 'approved';
            $bill->save();
            
            // ADD STOCK AND UPDATE PRODUCT DETAILS FOR EACH PRODUCT IN THE BILL
            foreach ($bill->billProducts as $billProduct) {
                $product = $billProduct->product;
                $quantity = (float)$billProduct->quantity;
                
                if ($product) {
                    // Get values from bill product
                    $netWeight = $billProduct->net_weight ?? 0;
                    $ratePer40kg = $billProduct->price ?? 0;
                    $packing = $billProduct->packing ?? 0;
                    $totalWeight = $billProduct->total_weight ?? 0;
                    $bardanaWeight = $billProduct->bardana_weight ?? 0;
                    
                    // Update product with Net Weight and Rate
                    $product->update([
                        'net_weight' => $netWeight,
                        'price_40kg' => $ratePer40kg,
                        'purchase_price' => $ratePer40kg,
                        'packing' => $packing,
                        'total_weight' => $totalWeight,
                        'bardana_weight' => $bardanaWeight
                    ]);
                    
                    // Increment stock by quantity
                    $product->increment('stock', $quantity);
                    
                    // Record in Stock History
                    StockHistory::create([
                        'uuid' => (string) Str::uuid(),
                        'date' => $bill->date,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'type' => 'in',
                        'current_stock' => $product->stock,
                        'description' => 'Purchase approved: Bill #' . $bill->id . ' from ' . ($bill->vendor->company_name ?? 'Vendor') . ' | Net Wt: ' . $netWeight . ' | Rate: ' . $ratePer40kg,
                    ]);
                }
            }
            
            // Update vendor transaction description
            $vendorTransaction = VendorTransaction::where('bill_id', $bill->id)
                ->where('type', 'bill')
                ->first();
            
            if ($vendorTransaction) {
                $vendorTransaction->update([
                    'description' => 'Purchase Bill Approved: ' . $bill->billProducts->count() . ' items from ' . ($bill->vendor->company_name ?? 'Vendor'),
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bill approved successfully. Stock, Net Weight & Rate have been updated.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Approve Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NEW METHOD: Approve a pending vendor payment (Admin only)
     */
    public function approveVendorPayment($uuid)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can approve payments.'
            ], 403);
        }

        try {
            DB::beginTransaction();
            
            $payment = VendorTransaction::where('uuid', $uuid)->firstOrFail();
            
            if ($payment->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already approved.'
                ], 400);
            }
            
            $payment->approval_status = 'approved';
            $payment->save();
            
            // Update vendor balance (decrease balance - paid to vendor)
            $vendor = $payment->vendor;
            $vendor->decrement('balance', $payment->amount);
            $payment->current_balance = $vendor->balance;
            $payment->save();
            
            // Update bank or cash balance
            if ($payment->send_via == 'bank') {
                $bank = Bank::find($payment->bank_id);
                if ($bank) {
                    $bank->decrement('account_balance', $payment->amount);
                    
                    BankTransaction::where('vendor_transaction_id', $payment->id)->update([
                        'balance' => $bank->account_balance,
                        'description' => 'Payment to ' . ($vendor->company_name ?? 'Vendor') . ' (Approved)',
                    ]);
                }
            } else {
                $cash = Cash::first();
                if ($cash) {
                    $cash->decrement('balance', $payment->amount);
                    
                    CashTransaction::where('vendor_transaction_id', $payment->id)->update([
                        'balance' => $cash->balance,
                        'description' => 'Payment to ' . ($vendor->company_name ?? 'Vendor') . ' (Approved)',
                    ]);
                }
            }
            
            // Update daybook entry
            Daybook::where('vendor_transaction_id', $payment->id)->update([
                'description' => 'Payment to ' . ($vendor->company_name ?? 'Vendor') . ' (Approved)',
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully. Vendor balance updated.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve Vendor Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a pending bill (Delete it)
     */
    public function rejectBill($uuid)
    {
        try {
            DB::beginTransaction();
            
            $bill = Bill::where('uuid', $uuid)->firstOrFail();
            
            if ($bill->approval_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject an approved bill.'
                ], 400);
            }
            
            // Delete bill products
            $bill->billProducts()->delete();
            
            // Delete extra charges
            $bill->extraCharges()->delete();
            
            // Delete additional charges
            $bill->additionalCharges()->delete();
            
            // Delete vendor transaction
            if ($bill->vendorTransaction) {
                $bill->vendorTransaction()->delete();
            }
            
            // Update vendor balance (reverse the liability)
            $vendor = $bill->vendor;
            $vendor->decrement('balance', $bill->total_amount);
            
            // Delete the bill
            $bill->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bill rejected and deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject Bill Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Rejection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalized General Store Function - NO STOCK UPDATE
     */
    public function generalStore(Request $request): RedirectResponse
    {
        \Log::info('===== generalStore() STARTED - This method should NOT update stock or product =====');
        
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'date' => 'required',
            'payment_terms' => 'nullable|string',
            'type' => 'required|in:bill,product',
            'products' => 'required|array|min:1',
            'products.*.product_type' => 'required|in:existing,new',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.name' => 'required_if:products.*.product_type,new',
            'products.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'products.*.product_id' => 'required_if:products.*.product_type,existing|nullable|exists:products,id',
        ]);

        try {
            DB::beginTransaction();

            $vendor = Vendor::findOrFail($request->vendor_id);
            $billType = $request->input('type', 'product');

            // Create bill with pending approval
            $bill = Bill::create([
                'uuid' => (string) Str::uuid(),
                'vendor_id' => $vendor->id,
                'date' => $request->date,
                'payment_terms' => $request->input('payment_terms'),
                'status' => 'pending',
                'approval_status' => 'pending',
                'total_amount' => 0,
            ]);

            \Log::info('Bill created with ID: ' . $bill->id . ' - Status: pending');

            $grandTotalAmount = 0;

            foreach ($request->products as $index => $productData) {
                $productId = null;
                $quantity = (float)$productData['quantity'];
                $price = (float)$productData['price'];
                $description = $productData['description'] ?? null;
                $packing = isset($productData['packing']) ? (float)$productData['packing'] : null;
                $totalWeight = isset($productData['total_weight']) ? (float)$productData['total_weight'] : null;
                $bardanaWeight = isset($productData['bardana_weight']) ? (float)$productData['bardana_weight'] : null;
                $netWeight = isset($productData['net_weight']) ? (float)$productData['net_weight'] : null;
                $totalPrice = isset($productData['total_price']) ? (float)$productData['total_price'] : ($quantity * $price);

                if ($productData['product_type'] === 'new') {
                    $imagePath = null;
                    if ($request->hasFile("products.$index.image")) {
                        $imagePath = $request->file("products.$index.image")->store('products', 'public');
                    }

                    // Create new product with ZERO stock (values will be updated on approval)
                    $product = Product::create([
                        'uuid' => (string) Str::uuid(),
                        'name' => $productData['name'],
                        'vendor_id' => $vendor->id,
                        'purchase_price' => 0,
                        'net_weight' => 0,
                        'price_40kg' => 0,
                        'stock' => 0,
                        'description' => $description,
                        'image' => $imagePath,
                        'sale_price' => 0,
                    ]);
                    $productId = $product->id;
                    \Log::info('New product created: ' . $product->name . ' - Stock: 0 (pending approval)');
                } else {
                    $productId = $productData['product_id'];
                    // DO NOT UPDATE PRODUCT HERE - Keep existing values
                    $product = Product::findOrFail($productId);
                    \Log::info('Existing product used: ' . $product->name . ' - Current stock: ' . $product->stock);
                }

                // Save bill product (WITHOUT updating stock or product)
                $bill->billProducts()->create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'packing' => $packing,
                    'total_weight' => $totalWeight,
                    'bardana_weight' => $bardanaWeight,
                    'net_weight' => $netWeight,
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'type' => $billType,
                    'description' => $description,
                ]);

                $grandTotalAmount += $totalPrice;
            }

            // Process extra charges (subtract)
            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    if (!empty($chargeData['amount']) && $chargeData['amount'] > 0) {
                        BillExtraCharge::create([
                            'bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => $chargeData['amount'],
                        ]);
                        $grandTotalAmount -= (float)$chargeData['amount'];
                    }
                }
            }

            // Process additional charges (add)
            if ($request->has('additional_charges')) {
                foreach ($request->additional_charges as $chargeData) {
                    if (!empty($chargeData['amount']) && $chargeData['amount'] > 0) {
                        BillAdditionalCharge::create([
                            'bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => $chargeData['amount'],
                        ]);
                        $grandTotalAmount += (float)$chargeData['amount'];
                    }
                }
            }

            // Update bill total
            $bill->update(['total_amount' => $grandTotalAmount]);

            // Update vendor balance (liability increases)
            $vendor->increment('balance', $grandTotalAmount);

            // Record vendor transaction
            VendorTransaction::create([
                'uuid' => (string) Str::uuid(),
                'date' => $request->date,
                'amount' => $grandTotalAmount,
                'description' => 'Purchase Bill (Pending Approval): ' . count($request->products) . ' items from ' . $vendor->company_name,
                'type' => 'bill',
                'transaction_type' => 'credit',
                'current_balance' => $vendor->balance,
                'bill_id' => $bill->id,
                'vendor_id' => $vendor->id,
            ]);

            DB::commit();

            \Log::info('===== generalStore() COMPLETED - Stock and Product NOT updated for Bill ID: ' . $bill->id . ' =====');

            return redirect()
                ->route('vendors.view', $vendor->uuid)
                ->with('success', 'Bill created successfully and sent for Admin approval. Stock will be updated after approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('generalStore Error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for "Create Bill 2" (general_2 bills only)
     */
    public function generalEdit2(string $uuid)
    {
        $bill = Bill::where('uuid', $uuid)
            ->with(['vendor', 'billProducts.product', 'extraCharges', 'additionalCharges'])
            ->firstOrFail();

        $firstProduct = $bill->billProducts()->first();
        if (!$firstProduct || $firstProduct->type !== 'product') {
            return redirect()->route('vendors.bills.edit', $bill->uuid)
                ->with('error', 'This bill cannot be edited with the general_2 form.');
        }

        $vendor = $bill->vendor;
        $products = Product::all();

        return view('admin.pages.vendors.bills.edit_general_2', compact('bill', 'vendor', 'products'));
    }

    /**
     * Update a "Create Bill 2" (general_2) bill
     */
    public function generalUpdate2(Request $request, string $uuid): RedirectResponse
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'date' => 'required',
            'payment_terms' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $bill = Bill::where('uuid', $uuid)
                ->with(['billProducts', 'extraCharges', 'additionalCharges'])
                ->firstOrFail();
            $vendor = $bill->vendor;

            $grandTotalAmount = 0;

            // --- DELETE PRODUCTS NOT IN CURRENT REQUEST ---
            $existingBillProductIds = $bill->billProducts->pluck('id')->toArray();
            $submittedBillProductIds = collect($request->products)->pluck('bill_product_id')->filter()->toArray();
            $toDelete = array_diff($existingBillProductIds, $submittedBillProductIds);

            foreach ($toDelete as $id) {
                $billProduct = BillProduct::find($id);
                if ($billProduct) {
                    $product = Product::find($billProduct->product_id);
                    if ($product && $bill->approval_status == 'approved') {
                        $product->decrement('stock', $billProduct->quantity);
                    }
                    $billProduct->delete();
                }
            }

            // --- PROCESS PRODUCTS ---
            foreach ($request->products as $index => $productData) {
                $quantity = (float)$productData['quantity'];
                $price = (float)$productData['price'];
                $description = $productData['description'] ?? null;
                $packing = isset($productData['packing']) ? (float)$productData['packing'] : null;
                $totalWeight = isset($productData['total_weight']) ? (float)$productData['total_weight'] : null;
                $bardanaWeight = isset($productData['bardana_weight']) ? (float)$productData['bardana_weight'] : null;
                $netWeight = isset($productData['net_weight']) ? (float)$productData['net_weight'] : null;
                $totalPrice = isset($productData['total_price']) ? (float)$productData['total_price'] : ($quantity * $price);

                if (!empty($productData['bill_product_id'])) {
                    $billProduct = BillProduct::find($productData['bill_product_id']);
                    if ($billProduct) {
                        $oldQty = (float)$billProduct->quantity;
                        $diff = $quantity - $oldQty;

                        $product = Product::find($productData['product_id']);
                        if ($product) {
                            $product->update([
                                'purchase_price' => $price,
                                'net_weight' => $netWeight,
                                'price_40kg' => $price,
                            ]);

                            if ($diff !== 0 && $bill->approval_status == 'approved') {
                                if ($diff > 0) {
                                    $product->increment('stock', $diff);
                                } else {
                                    $product->decrement('stock', abs($diff));
                                }
                            }
                        }

                        $billProduct->update([
                            'product_id' => $productData['product_id'],
                            'quantity' => $quantity,
                            'packing' => $packing,
                            'total_weight' => $totalWeight,
                            'bardana_weight' => $bardanaWeight,
                            'net_weight' => $netWeight,
                            'price' => $price,
                            'total_price' => $totalPrice,
                            'type' => 'product',
                            'description' => $description,
                        ]);
                    }
                } else {
                    $product = Product::findOrFail($productData['product_id']);
                    if ($bill->approval_status == 'approved') {
                        $product->increment('stock', $quantity);
                    }

                    $bill->billProducts()->create([
                        'uuid' => (string) Str::uuid(),
                        'product_id' => $productData['product_id'],
                        'quantity' => $quantity,
                        'packing' => $packing,
                        'total_weight' => $totalWeight,
                        'bardana_weight' => $bardanaWeight,
                        'net_weight' => $netWeight,
                        'price' => $price,
                        'total_price' => $totalPrice,
                        'type' => 'product',
                        'description' => $description,
                    ]);
                }

                $grandTotalAmount += $totalPrice;
            }

            // --- DELETE EXTRA CHARGES NOT IN CURRENT REQUEST ---
            $existingExtraIds = $bill->extraCharges->pluck('id')->toArray();
            $submittedExtraIds = collect($request->extra_charges ?? [])->pluck('id')->filter()->toArray();
            $toDeleteExtra = array_diff($existingExtraIds, $submittedExtraIds);
            BillExtraCharge::whereIn('id', $toDeleteExtra)->delete();

            // --- PROCESS EXTRA CHARGES ---
            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    if (!empty($chargeData['amount']) && $chargeData['amount'] > 0) {
                        if (!empty($chargeData['id'])) {
                            $charge = BillExtraCharge::find($chargeData['id']);
                            if ($charge) {
                                $charge->update([
                                    'name' => $chargeData['name'],
                                    'amount' => $chargeData['amount'],
                                ]);
                            }
                        } else {
                            BillExtraCharge::create([
                                'bill_id' => $bill->id,
                                'name' => $chargeData['name'],
                                'amount' => $chargeData['amount'],
                            ]);
                        }
                        $grandTotalAmount -= (float)$chargeData['amount'];
                    }
                }
            }

            // --- DELETE ADDITIONAL CHARGES NOT IN CURRENT REQUEST ---
            $existingAdditionalIds = $bill->additionalCharges->pluck('id')->toArray();
            $submittedAdditionalIds = collect($request->additional_charges ?? [])->pluck('id')->filter()->toArray();
            $toDeleteAdditional = array_diff($existingAdditionalIds, $submittedAdditionalIds);
            BillAdditionalCharge::whereIn('id', $toDeleteAdditional)->delete();

            // --- PROCESS ADDITIONAL CHARGES ---
            if ($request->has('additional_charges')) {
                foreach ($request->additional_charges as $chargeData) {
                    if (!empty($chargeData['amount']) && $chargeData['amount'] > 0) {
                        if (!empty($chargeData['id'])) {
                            $charge = BillAdditionalCharge::find($chargeData['id']);
                            if ($charge) {
                                $charge->update([
                                    'name' => $chargeData['name'],
                                    'amount' => $chargeData['amount'],
                                ]);
                            }
                        } else {
                            BillAdditionalCharge::create([
                                'bill_id' => $bill->id,
                                'name' => $chargeData['name'],
                                'amount' => $chargeData['amount'],
                            ]);
                        }
                        $grandTotalAmount += (float)$chargeData['amount'];
                    }
                }
            }

            // --- UPDATE BILL AND VENDOR BALANCE ---
            $oldAmount = $bill->total_amount;
            $amountDifference = $grandTotalAmount - $oldAmount;

            $bill->update([
                'date' => $request->date,
                'payment_terms' => $request->input('payment_terms'),
                'total_amount' => $grandTotalAmount,
                'approval_status' => 'pending',
            ]);

            $vendor->increment('balance', $amountDifference);

            // --- UPDATE VENDOR TRANSACTION ---
            $vendorTransaction = VendorTransaction::where('bill_id', $bill->id)
                ->where('type', 'bill')
                ->first();

            if ($vendorTransaction) {
                $vendorTransaction->update([
                    'date' => $request->date,
                    'amount' => $grandTotalAmount,
                    'description' => 'Purchase Bill Updated: ' . count($request->products) . ' items from ' . $vendor->company_name,
                    'current_balance' => $vendor->fresh()->balance,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('vendors.view', $vendor->uuid)
                ->with('success', 'Bill successfully updated and requires re-approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Show details for a "Create Bill 2" (general_2) bill
     */
    public function generalShow2(string $uuid)
    {
        $bill = Bill::where('uuid', $uuid)
            ->with(['vendor', 'billProducts.product', 'extraCharges', 'additionalCharges', 'vendorTransaction'])
            ->firstOrFail();

        $firstProduct = $bill->billProducts()->first();
        if (!$firstProduct || $firstProduct->type !== 'product') {
            return redirect()->route('vendors.bills.show', $bill->uuid)
                ->with('error', 'This bill cannot be viewed with the general_2 view.');
        }

        return view('admin.pages.vendors.bills.show_general_2', compact('bill'));
    }

    /**
     * Generate PDF for General 2 Bills
     */
    public function generalPdf2(string $uuid)
    {
        $bill = Bill::where('uuid', $uuid)
            ->with(['vendor', 'billProducts.product', 'extraCharges', 'additionalCharges', 'vendorTransaction'])
            ->firstOrFail();

        $firstProduct = $bill->billProducts()->first();
        if (!$firstProduct || $firstProduct->type !== 'product') {
            return redirect()->route('vendors.bills.show', $bill->uuid)
                ->with('error', 'This bill cannot be viewed as PDF with the general_2 format.');
        }

        $pdf = PDF::loadView('admin.pages.vendors.bills.pdf_general_2', compact('bill'));
        return $pdf->stream('purchase-bill-' . $bill->uuid . '.pdf');
    }

    /**
     * Display a list of all payments
     */
    public function paymentList(Request $request)
    {
        try {
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $type = $request->type;
            $approvalStatus = $request->approval_status;

            if (!$fromDate && !$toDate) {
                $fromDate = '2026-01-01';
            }

            $paymentsQuery = VendorTransaction::with('vendor')
                ->where('type', 'payment');

            if ($fromDate) {
                $paymentsQuery->whereDate('date', '>=', $fromDate);
            }
            if ($toDate) {
                $paymentsQuery->whereDate('date', '<=', $toDate);
            }
            if ($approvalStatus && in_array($approvalStatus, ['pending', 'approved'])) {
                $paymentsQuery->where('approval_status', $approvalStatus);
            }

            $payments = $paymentsQuery->orderBy('date', 'desc')->get();
            $generalEntries = collect([]);
            
            if ($type !== 'payments') {
                $billEntries = VendorTransaction::with('vendor')
                    ->where('type', 'bill')
                    ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
                    ->when($toDate, fn($q) => $q->whereDate('date', '<=', $toDate))
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(fn($item) => (object)[
                        'uuid' => $item->uuid,
                        'id' => $item->id,
                        'date' => $item->date,
                        'description' => $item->description ?? 'Purchase from ' . ($item->vendor->company_name ?? 'Vendor'),
                        'amount' => $item->amount,
                        'type' => 'bill',
                        'type_label' => 'Purchase Bill',
                        'type_badge' => 'primary',
                        'reference' => $item->vendor ? $item->vendor->company_name : 'Vendor',
                        'method' => 'Credit',
                        'is_payment' => false,
                        'source' => 'vendor_bill',
                        'amount_class' => 'text-danger',
                    ]);
                
                $generalEntries = $generalEntries->concat($billEntries);
                
                $balanceEntries = VendorTransaction::with('vendor')
                    ->where('type', 'balance')
                    ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
                    ->when($toDate, fn($q) => $q->whereDate('date', '<=', $toDate))
                    ->orderBy('date', 'desc')
                    ->get()
                    ->map(fn($item) => (object)[
                        'uuid' => $item->uuid,
                        'id' => $item->id,
                        'date' => $item->date,
                        'description' => $item->description ?? 'Balance Adjustment for ' . ($item->vendor->company_name ?? 'Vendor'),
                        'amount' => $item->amount,
                        'type' => 'balance',
                        'type_label' => 'Opening Balance',
                        'type_badge' => 'warning',
                        'reference' => $item->vendor ? $item->vendor->company_name : 'Vendor',
                        'method' => 'Adjustment',
                        'is_payment' => false,
                        'source' => 'vendor_balance',
                        'amount_class' => 'text-info',
                    ]);
                
                $generalEntries = $generalEntries->concat($balanceEntries);
                
                if (Schema::hasTable('daybooks')) {
                    $daybookEntries = DB::table('daybooks')
                        ->where('type', 'transaction')
                        ->when($fromDate, fn($q) => $q->whereDate('transaction_date', '>=', $fromDate))
                        ->when($toDate, fn($q) => $q->whereDate('transaction_date', '<=', $toDate))
                        ->when($approvalStatus, fn($q) => $q->where('approval_status', $approvalStatus))
                        ->orderBy('transaction_date', 'desc')
                        ->get()
                        ->map(fn($item) => (object)[
                            'uuid' => 'daybook_' . $item->id,
                            'id' => $item->id,
                            'date' => $item->transaction_date,
                            'description' => $item->description ?? 'General Entry',
                            'amount' => $item->amount,
                            'type' => 'transaction',
                            'type_label' => 'General Entry',
                            'type_badge' => 'info',
                            'reference' => $item->description ?? 'General Entry',
                            'method' => 'Transfer',
                            'is_payment' => false,
                            'source' => 'daybook',
                            'amount_class' => 'text-primary',
                            'approval_status' => $item->approval_status ?? 'pending',
                        ]);
                    
                    $generalEntries = $generalEntries->concat($daybookEntries);
                }
            }

            $generalEntries = $generalEntries->sortByDesc('date')->values();

            return view('admin.pages.vendors.payments.list', compact('payments', 'generalEntries'));

        } catch (\Exception $e) {
            Log::error('Error in paymentList: ' . $e->getMessage());
            $payments = collect([]);
            $generalEntries = collect([]);
            return view('admin.pages.vendors.payments.list', compact('payments', 'generalEntries'))
                ->with('error', 'Error loading transactions: ' . $e->getMessage());
        }
    }

    /**
     * Delete a payment
     */
    public function paymentDelete($uuid)
    {
        try {
            DB::beginTransaction();

            $transaction = VendorTransaction::where('uuid', $uuid)
                ->where('type', 'payment')
                ->firstOrFail();

            $vendor = Vendor::findOrFail($transaction->vendor_id);
            $amount = $transaction->amount;

            if ($transaction->send_via === 'bank') {
                $bankTxn = BankTransaction::where('vendor_transaction_id', $transaction->id)->first();
                if ($bankTxn) {
                    $bank = Bank::find($bankTxn->bank_id);
                    if ($bank) {
                        $bank->increment('account_balance', $amount);
                    }
                    $bankTxn->delete();
                }
            } elseif ($transaction->send_via === 'cash') {
                $cashTxn = CashTransaction::where('vendor_transaction_id', $transaction->id)->first();
                if ($cashTxn) {
                    $cash = Cash::find($cashTxn->cash_id);
                    if ($cash) {
                        $cash->increment('balance', $amount);
                    }
                    $cashTxn->delete();
                }
            }

            VendorTransactionImage::where('vendor_transaction_id', $transaction->id)->delete();
            Daybook::where('vendor_transaction_id', $transaction->id)->delete();
            $transaction->delete();

            $allTxns = VendorTransaction::where('vendor_id', $vendor->id)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = 0;
            foreach ($allTxns as $t) {
                $type = strtolower($t->type);
                if (in_array($type, ['bill', 'balance', 'opening_balance'])) {
                    $runningBalance += $t->amount;
                } elseif (in_array($type, ['payment', 'return'])) {
                    $runningBalance -= $t->amount;
                }
                $t->update(['current_balance' => $runningBalance]);
            }

            $vendor->update(['balance' => $runningBalance]);

            DB::commit();
            return redirect()->route('vendors.payments.list')->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deletion failed: ' . $e->getMessage());
        }
    }
    
    public function paymentShow($uuid)
    {
        try {
            $payment = VendorTransaction::where('uuid', $uuid)
                ->where('type', 'payment')
                ->with(['vendor', 'vendorTransactionImages'])
                ->firstOrFail();

            $bankTransaction = null;
            if ($payment->send_via === 'bank') {
                $bankTransaction = BankTransaction::where('vendor_transaction_id', $payment->id)
                    ->with('bank')
                    ->first();
            }

            return view('admin.pages.vendors.payments.show', compact('payment', 'bankTransaction'));
        } catch (\Exception $e) {
            Log::error('Error in paymentShow: ' . $e->getMessage());
            return redirect()->route('vendors.payments.list')
                ->with('error', 'Payment not found.');
        }
    }

    public function paymentEdit($uuid)
    {
        try {
            $payment = VendorTransaction::where('uuid', $uuid)->firstOrFail();
            $vendors = Vendor::all();
            $banks = Bank::all();
            return view('admin.pages.vendors.payments.edit', compact('payment', 'vendors', 'banks'));
        } catch (\Exception $e) {
            Log::error('Error in paymentEdit: ' . $e->getMessage());
            return redirect()->route('vendors.payments.list')
                ->with('error', 'Payment not found.');
        }
    }

    public function paymentUpdate(Request $request, $uuid)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required',
            'send_via' => 'required|in:cash,bank',
            'bank_id' => 'required_if:send_via,bank',
        ]);

        try {
            DB::beginTransaction();

            $transaction = VendorTransaction::where('uuid', $uuid)->firstOrFail();
            $vendor = Vendor::findOrFail($transaction->vendor_id);

            if ($transaction->send_via === 'bank') {
                $oldBankTxn = BankTransaction::where('vendor_transaction_id', $transaction->id)->first();
                if ($oldBankTxn) {
                    Bank::where('id', $oldBankTxn->bank_id)->increment('account_balance', $transaction->amount);
                    $oldBankTxn->delete();
                }
            } elseif ($transaction->send_via === 'cash') {
                Cash::first()->increment('balance', $transaction->amount);
                CashTransaction::where('vendor_transaction_id', $transaction->id)->delete();
            }

            $newAmount = $request->amount;

            if ($request->send_via === 'bank') {
                $bank = Bank::findOrFail($request->bank_id);
                if ($bank->account_balance < $newAmount) throw new \Exception('Insufficient Bank Balance');

                $bank->decrement('account_balance', $newAmount);
                BankTransaction::create([
                    'vendor_transaction_id' => $transaction->id,
                    'bank_id' => $bank->id,
                    'amount' => $newAmount,
                    'balance' => $bank->account_balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Updated)',
                    'transaction_type' => 'debit',
                ]);
            } elseif ($request->send_via === 'cash') {
                $cash = Cash::first();
                if ($cash->balance < $newAmount) throw new \Exception('Insufficient Cash Balance');

                $cash->decrement('balance', $newAmount);
                CashTransaction::create([
                    'vendor_transaction_id' => $transaction->id,
                    'cash_id' => $cash->id,
                    'amount' => $newAmount,
                    'balance' => $cash->balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Updated)',
                    'transaction_type' => 'debit',
                ]);
            }

            $transaction->update([
                'date' => $request->date,
                'amount' => $newAmount,
                'send_via' => $request->send_via,
                'description' => $request->description ?? 'Payment to ' . $vendor->company_name,
            ]);

            if ($request->hasFile('receipt_images')) {
                VendorTransactionImage::where('vendor_transaction_id', $transaction->id)->delete();
                foreach ($request->file('receipt_images') as $image) {
                    $path = $image->store('vendor_transactions_payments', 'public');
                    VendorTransactionImage::create([
                        'vendor_transaction_id' => $transaction->id,
                        'image' => $path,
                        'date' => $request->date,
                        'vendor_id' => $vendor->id,
                    ]);
                }
            }

            $allTxns = VendorTransaction::where('vendor_id', $vendor->id)->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
            $runningBalance = 0;
            foreach ($allTxns as $t) {
                $type = strtolower($t->type);
                if (in_array($type, ['bill', 'balance', 'opening_balance'])) $runningBalance += $t->amount;
                elseif (in_array($type, ['payment', 'return'])) $runningBalance -= $t->amount;
                $t->update(['current_balance' => $runningBalance]);
            }
            $vendor->update(['balance' => $runningBalance]);

            DB::commit();
            return redirect()->route('vendors.payments.list')->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function create(string $uuid)
    {
        $vendor = Vendor::where('uuid', $uuid)->firstOrFail();
        $products = Product::where('vendor_id', $vendor->id)->get();
        return view('admin.pages.vendors.bills.create', compact('vendor', 'products'));
    }
    
    /**
     * FIXED: Store method - NO STOCK UPDATE for pending bills
     */
    public function store(Request $request, $uuid): RedirectResponse
    {
        \Log::info('===== store() called =====');

        try {
            DB::beginTransaction();
            $vendor = Vendor::where('uuid', $uuid)->firstOrFail();
            
            $bill = Bill::create([
                'uuid' => (string) Str::uuid(),
                'vendor_id' => $vendor->id,
                'date' => $request->date,
                'status' => 'pending',
                'approval_status' => 'pending',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->products as $productData) {
                if ($productData['product_type'] === 'new') {
                    $product = $vendor->products()->create([
                        'uuid' => (string) Str::uuid(),
                        'name' => $productData['name'],
                        'description' => $productData['description'] ?? null,
                        'vendor_id' => $vendor->id,
                        'purchase_price' => $productData['price'],
                        'sale_price' => $productData['sale_price'] ?? 0,
                        'stock' => 0,
                    ]);

                    if (isset($productData['image']) && $productData['image']->isValid()) {
                        $path = $productData['image']->store('products', 'public');
                        $product->update(['image' => $path]);
                    }

                    $productId = $product->id;
                } else {
                    $productId = $productData['product_id'];
                    $product = Product::where('id', $productId)->first();
                }

                $bill->billProducts()->create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $productId,
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'sale_price' => $productData['sale_price'] ?? 0,
                ]);

                $totalAmount += ($productData['quantity'] * $productData['price']);
            }

            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    BillExtraCharge::create([
                        'bill_id' => $bill->id,
                        'name' => $chargeData['name'],
                        'amount' => $chargeData['amount'],
                    ]);
                    $totalAmount += $chargeData['amount'];
                }
            }

            $bill->update(['total_amount' => $totalAmount]);

            $vendor->increment('balance', $totalAmount);
            VendorTransaction::create([
                'uuid' => (string) Str::uuid(),
                'date' => $request->date,
                'amount' => $totalAmount,
                'description' => 'Purchase Bill (Pending Approval): from ' . $vendor->company_name,
                'type' => 'bill',
                'transaction_type' => 'credit',
                'current_balance' => $vendor->balance,
                'bill_id' => $bill->id,
                'vendor_id' => $vendor->id,
            ]);

            DB::commit();

            return redirect()
                ->route('vendors.view', $vendor->uuid)
                ->with('success', 'Bill created successfully. Awaiting admin approval for stock update.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }
    
    public function edit(string $uuid)
    {
        $bill = Bill::where('uuid', $uuid)
            ->with(['vendor', 'billProducts.product', 'extraCharges'])
            ->firstOrFail();
        $vendor = $bill->vendor;
        $products = Product::where('vendor_id', $vendor->id)->get();
        return view('admin.pages.vendors.bills.edit', compact('bill', 'vendor', 'products'));
    }
    
    public function update(Request $request, $uuid): RedirectResponse
    {
        $request->validate([
            'date' => 'required',
            'products' => 'required|array',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $bill = Bill::where('uuid', $uuid)->with(['billProducts', 'extraCharges'])->firstOrFail();
            $vendor = $bill->vendor;
            $totalAmount = 0;

            $existingBillProductIds = $bill->billProducts->pluck('id')->toArray();
            $submittedBillProductIds = collect($request->products)->pluck('bill_product_id')->filter()->toArray();
            $toDelete = array_diff($existingBillProductIds, $submittedBillProductIds);

            if (!empty($toDelete)) {
                foreach ($toDelete as $id) {
                    $itemToDelete = BillProduct::find($id);
                    if ($itemToDelete) {
                        $product = Product::find($itemToDelete->product_id);
                        if ($product && $bill->approval_status == 'approved') {
                            $product->decrement('stock', $itemToDelete->quantity);
                        }
                        $itemToDelete->delete();
                    }
                }
            }

            foreach ($request->products as $productData) {
                $isNewProductEntry = ($productData['product_type'] === 'new');
                $quantity = (int) $productData['quantity'];
                $price = (float) $productData['price'];
                $description = $productData['description'] ?? null;
                $productId = null;

                if ($isNewProductEntry) {
                    $newProduct = Product::create([
                        'uuid' => (string) Str::uuid(),
                        'name' => $productData['name'],
                        'description' => $description,
                        'vendor_id' => $vendor->id,
                        'purchase_price' => $price,
                        'stock' => 0,
                    ]);
                    $productId = $newProduct->id;
                    $targetProduct = $newProduct;
                } else {
                    $productId = $productData['product_id'];
                    $targetProduct = Product::findOrFail($productId);
                    $targetProduct->update(['purchase_price' => $price]);
                }

                if (!empty($productData['bill_product_id'])) {
                    $billProduct = BillProduct::find($productData['bill_product_id']);
                    if ($billProduct) {
                        $oldQty = (int) $billProduct->quantity;
                        $diff = $quantity - $oldQty;

                        if ($diff !== 0 && $bill->approval_status == 'approved') {
                            if ($diff > 0) {
                                $targetProduct->increment('stock', $diff);
                            } else {
                                $targetProduct->decrement('stock', abs($diff));
                            }
                        }

                        $billProduct->update([
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'price' => $price,
                            'description' => $description,
                        ]);
                    }
                } else {
                    $bill->billProducts()->create([
                        'uuid' => (string) Str::uuid(),
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'description' => $description,
                    ]);

                    if (!$isNewProductEntry && $bill->approval_status == 'approved') {
                        $targetProduct->increment('stock', $quantity);
                    }
                }

                $totalAmount += ($quantity * $price);
            }

            $existingChargeIds = $bill->extraCharges->pluck('id')->toArray();
            $submittedChargeIds = collect($request->extra_charges ?? [])->pluck('id')->filter()->toArray();
            $chargesToDelete = array_diff($existingChargeIds, $submittedChargeIds);
            BillExtraCharge::whereIn('id', $chargesToDelete)->delete();

            if ($request->has('extra_charges')) {
                foreach ($request->extra_charges as $chargeData) {
                    if (!empty($chargeData['id'])) {
                        $charge = BillExtraCharge::find($chargeData['id']);
                        if ($charge) {
                            $charge->update([
                                'name' => $chargeData['name'],
                                'amount' => $chargeData['amount'],
                            ]);
                        }
                    } else {
                        BillExtraCharge::create([
                            'bill_id' => $bill->id,
                            'name' => $chargeData['name'],
                            'amount' => $chargeData['amount'],
                        ]);
                    }
                    $totalAmount += (float) $chargeData['amount'];
                }
            }

            $bill->update([
                'date' => $request->date,
                'total_amount' => $totalAmount,
            ]);

            $vendorTransaction = VendorTransaction::where('bill_id', $bill->id)
                ->where('type', 'bill')
                ->first();

            if ($vendorTransaction) {
                $vendorTransaction->update([
                    'date' => $request->date,
                    'amount' => $totalAmount,
                    'description' => 'Purchase Bill Updated: from ' . $vendor->company_name,
                ]);
            }

            $transactions = VendorTransaction::where('vendor_id', $vendor->id)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = 0;
            foreach ($transactions as $txn) {
                $type = strtolower($txn->type);
                if (in_array($type, ['bill', 'balance', 'opening_balance', 'opening balance'])) {
                    $runningBalance += (float) $txn->amount;
                } elseif (in_array($type, ['payment', 'return'])) {
                    $runningBalance -= (float) $txn->amount;
                }
                $txn->update(['current_balance' => $runningBalance]);
            }

            $vendor->update(['balance' => $runningBalance]);

            DB::commit();

            return redirect()
                ->route('vendors.view', $vendor->uuid)
                ->with('success', 'Purchase bill updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function list(Request $request)
    {
        $query = Bill::with('vendor', 'billProducts')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');
        
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }
        
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        
        $bills = $query->paginate(10);
        
        return view('admin.pages.vendors.bills.list', compact('bills'));
    }
    
    public function show(string $uuid)
    {
        $bill = Bill::with(['vendor', 'billProducts.product', 'extraCharges', 'vendorTransaction'])
            ->where('uuid', $uuid)
            ->firstOrFail();
        return view('admin.pages.vendors.bills.show', compact('bill'));
    }
    
    public function downloadPdf(string $uuid)
    {
        $bill = Bill::with(['vendor', 'billProducts.product', 'extraCharges', 'vendorTransaction'])
            ->where('uuid', $uuid)
            ->firstOrFail();
        $pdf = PDF::loadView('admin.pages.vendors.bills.pdf', compact('bill'));
        return $pdf->download('bill-' . $bill->uuid . '.pdf');
    }
    
    public function sendPayment(string $uuid)
    {
        try {
            $banks = Bank::all();
            $vendor = Vendor::where('uuid', $uuid)->firstOrFail();
            return view('admin.pages.vendors.send-payment', compact('vendor', 'banks'));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect()->route('vendors.list')->with([
                'status' => false,
                'message' => 'Internal server error',
            ]);
        }
    }
    
    /**
     * UPDATED: Store Send Payment - PENDING APPROVAL, NO BALANCE UPDATE
     */
    public function storeSendPayment(SendPaymentRequest $request, $uuid)
    {
        try {
            DB::beginTransaction();
            $vendor = Vendor::where('uuid', $uuid)->firstOrFail();

            $vendorTransaction = VendorTransaction::create([
                'uuid' => (string) Str::uuid(),
                'date' => $request->date,
                'amount' => $request->amount,
                'send_via' => $request->send_via,
                'type' => 'payment',
                'transaction_type' => 'debit',
                'approval_status' => 'pending', // PENDING APPROVAL
                'description' => 'Payment sent to ' . $vendor->company_name . ' (Pending Approval)',
                'current_balance' => $vendor->balance, // Balance NOT updated yet
                'vendor_id' => $vendor->id,
            ]);

            if ($request->send_via == 'bank') {
                $bank = Bank::where('id', $request->bank_id)->first();

                if (!$bank) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Bank not found',
                    ]);
                }

                if ($bank->account_balance < $request->amount) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Bank account balance is insufficient',
                    ]);
                }

                BankTransaction::create([
                    'vendor_transaction_id' => $vendorTransaction->id,
                    'bank_id' => $bank->id,
                    'amount' => $request->amount,
                    'balance' => $bank->account_balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                    'transaction_type' => 'debit',
                ]);
            } else if ($request->send_via == 'cash') {
                $cash = Cash::first();

                if (!$cash) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Cash not found, please add cash first',
                    ]);
                }

                if ($cash->balance < $request->amount) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Cash balance is insufficient',
                    ]);
                }

                CashTransaction::create([
                    'vendor_transaction_id' => $vendorTransaction->id,
                    'cash_id' => $cash->id,
                    'amount' => $request->amount,
                    'balance' => $cash->balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                    'transaction_type' => 'debit',
                ]);
            }

            // NO BALANCE UPDATE HERE - Only after approval

            if ($request->hasFile('receipt_images')) {
                $images = $request->file('receipt_images');
                foreach ($images as $image) {
                    $imagePath = $image->store('vendor_transactions_payments', 'public');
                    VendorTransactionImage::create([
                        'vendor_transaction_id' => $vendorTransaction->id,
                        'image' => $imagePath,
                        'date' => $request->date,
                        'vendor_id' => $vendor->id,
                    ]);
                }
            }

            Daybook::create([
                'transaction_date' => $request->date,
                'amount' => $request->amount,
                'description' => 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                'type' => 'transaction',
                'approval_status' => 'pending',
                'vendor_transaction_id' => $vendorTransaction->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment request created successfully! Awaiting admin approval.',
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create payment: ' . $th->getMessage(),
            ]);
        }
    }

    public function generalPaymentCreate()
    {
        $vendors = Vendor::orderBy('company_name', 'asc')->get();
        $banks = Bank::all();
        return view('admin.pages.vendors.general-payment', compact('vendors', 'banks'));
    }

    public function generalPaymentStore(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,uuid',
            'date' => 'required',
            'amount' => 'required|numeric|min:1',
            'send_via' => 'required|in:bank,cash',
            'bank_id' => 'required_if:send_via,bank',
        ]);

        try {
            DB::beginTransaction();

            $vendor = Vendor::where('uuid', $request->vendor_id)->firstOrFail();
            $newVendorBalance = $vendor->balance - $request->amount;

            $vendorTransaction = VendorTransaction::create([
                'uuid' => (string) Str::uuid(),
                'date' => $request->date,
                'amount' => $request->amount,
                'send_via' => $request->send_via,
                'description' => $request->description ?? 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                'type' => 'payment',
                'transaction_type' => 'debit',
                'approval_status' => 'pending',
                'current_balance' => $vendor->balance,
                'vendor_id' => $vendor->id,
            ]);

            if ($request->send_via == 'bank') {
                $bank = Bank::findOrFail($request->bank_id);

                if ($bank->account_balance < $request->amount) {
                    throw new \Exception('Insufficient Bank Balance.');
                }

                BankTransaction::create([
                    'vendor_transaction_id' => $vendorTransaction->id,
                    'bank_id' => $bank->id,
                    'amount' => $request->amount,
                    'balance' => $bank->account_balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                    'transaction_type' => 'debit',
                ]);
            } else if ($request->send_via == 'cash') {
                $cash = Cash::first();

                if (!$cash || $cash->balance < $request->amount) {
                    throw new \Exception('Insufficient Cash Balance.');
                }

                CashTransaction::create([
                    'vendor_transaction_id' => $vendorTransaction->id,
                    'cash_id' => $cash->id,
                    'amount' => $request->amount,
                    'balance' => $cash->balance,
                    'description' => 'Payment to ' . $vendor->company_name . ' (Pending Approval)',
                    'transaction_type' => 'debit',
                ]);
            }

            // NO BALANCE UPDATE HERE

            if ($request->hasFile('receipt_images')) {
                foreach ($request->file('receipt_images') as $image) {
                    $imagePath = $image->store('vendor_transactions_payments', 'public');
                    VendorTransactionImage::create([
                        'vendor_transaction_id' => $vendorTransaction->id,
                        'image' => $imagePath,
                        'date' => $request->date,
                        'vendor_id' => $vendor->id,
                    ]);
                }
            }

            Daybook::create([
                'transaction_date' => $request->date,
                'amount' => $request->amount,
                'description' => 'Payment to ' . $vendor->company_name . ' (' . ucfirst($request->send_via) . ') (Pending Approval)',
                'type' => 'transaction',
                'approval_status' => 'pending',
                'vendor_transaction_id' => $vendorTransaction->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment request created successfully! Awaiting admin approval.',
                'redirect' => route('vendors.view', $vendor->uuid)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function paymentDetails($uuid)
    {
        try {
            $vendorTransaction = VendorTransaction::where('uuid', $uuid)->firstOrFail();
            return view('admin.pages.vendors.payment-details', compact('vendorTransaction'));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return redirect()->route('vendors.list')->with([
                'status' => false,
                'message' => 'Internal server error',
            ]);
        }
    }
    
    public function delete($uuid)
    {
        if (!$this->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied. Only Admin can delete bills.'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $bill = Bill::where('uuid', $uuid)->firstOrFail();
            
            $bill->extraCharges()->delete();
            
            foreach ($bill->billProducts as $billProduct) {
                $product = $billProduct->product;
                if ($product && $bill->approval_status == 'approved') {
                    $product->decrement('stock', $billProduct->quantity);
                    
                    StockHistory::create([
                        'uuid' => (string) Str::uuid(),
                        'date' => now(),
                        'product_id' => $billProduct->product_id,
                        'quantity' => $billProduct->quantity,
                        'type' => 'out',
                        'current_stock' => $product->stock,
                        'description' => 'Bill deleted: ' . $bill->vendor->company_name,
                    ]);
                }
                $billProduct->delete();
            }
            
            if ($bill->vendorTransaction) {
                $daybook = Daybook::where('vendor_transaction_id', $bill->vendorTransaction->id)->first();
                if ($daybook) {
                    $daybook->delete();
                }
                $bill->vendorTransaction()->delete();
            }
            
            $bill->vendor->decrement('balance', $bill->total_amount);
            $bill->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bill deleted successfully',
            ]);
            
        } catch (\Throwable $th) {
            Log::error('Bill deletion error: ' . $th->getMessage());
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $th->getMessage(),
            ], 500);
        }
    }
}