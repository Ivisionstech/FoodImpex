<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\VendorTransaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class VendorController extends Controller
{
    public function create()
    {
        try {
            return view('admin.pages.vendors.create');
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect(route('dashboard'))->with([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    
    public function list()
    {
        try {
            $vendors = Vendor::where('active', 1)->latest()->paginate(10);
            return view('admin.pages.vendors.list', compact('vendors'));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect(route('dashboard'))->with([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    
public function store(StoreRequest $request)
{
    try {
        DB::beginTransaction();
        
        // Log the incoming data for debugging
        \Log::info('Vendor Store Request:', $request->all());
        
        $vendor = Vendor::create([
            'uuid' => Str::uuid(),
            'company_name' => $request->company_name,
            'person_name' => $request->person_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'balance' => $request->balance ?? 0,
        ]);
        
        \Log::info('Vendor Created:', ['id' => $vendor->id, 'balance' => $vendor->balance]);
        
        if ($request->hasFile('profile')) {
            $profile = $request->file('profile');
            $profileName = time() . '.' . $profile->getClientOriginalExtension();
            $profilePath = $profile->storeAs('vendor-profiles', $profileName, 'public');
            $vendor->profile = $profilePath;
            $vendor->save();
        }
        
        // Create opening balance transaction
        $openingBalance = floatval($request->balance ?? 0);
        $absoluteAmount = abs($openingBalance);
        $transactionType = $openingBalance >= 0 ? 'credit' : 'debit';
        
        \Log::info('Creating Opening Balance:', [
            'amount' => $absoluteAmount,
            'transaction_type' => $transactionType,
            'current_balance' => $openingBalance
        ]);
        
        $transaction = VendorTransaction::create([
            'uuid' => Str::uuid(),
            'date' => $request->open_balance_date ?? now(),
            'amount' => (string)$absoluteAmount,
            'type' => 'balance',
            'transaction_type' => $transactionType,
            'current_balance' => (string)$openingBalance,
            'vendor_id' => $vendor->id,
            'description' => 'Opening Balance',
            'approval_status' => 'approved',
        ]);
        
        \Log::info('VendorTransaction Created:', ['id' => $transaction->id]);
        
        DB::commit();
        
        return response()->json([
            'status' => true,
            'message' => 'Vendor Added Successfully',
            'redirect' => route('vendors.list')
        ]);
        
    } catch (\Throwable $th) {
        \Log::error('Vendor Store Error: ' . $th->getMessage());
        \Log::error('Stack trace: ' . $th->getTraceAsString());
        DB::rollback();
        
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $th->getMessage()
        ]);
    }
}
    public function view(Request $request, $uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->firstOrFail();

            $bill_from = $request->bill_from;
            $bill_to = $request->bill_to;
            $trans_from = $request->trans_from;
            $trans_to = $request->trans_to;

            // Get vendor transactions with filters (same as customer)
            $transactionsQuery = $vendor->vendorTransactions();
            
            // Apply date filters to transactions
            if ($trans_from && $trans_to) {
                $transactionsQuery->whereBetween('date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $transactionsQuery->where('date', '>=', $trans_from);
            } elseif ($trans_to) {
                $transactionsQuery->where('date', '<=', $trans_to);
            }
            
            // Get transactions ordered by date (newest first for display)
            $vendorTransactions = $transactionsQuery->orderBy('date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get();
            
            // Calculate actual balance from all transactions (for the top display)
            $allTransactions = $vendor->vendorTransactions()
                ->orderBy('date', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
            
            $actualBalance = 0;
            foreach ($allTransactions as $transaction) {
                $amount = floatval($transaction->amount);
                $type = strtolower($transaction->type ?? '');
                $approvalStatus = $transaction->approval_status ?? 'pending';
                $description = $transaction->description ?? '';
                $transactionType = $transaction->transaction_type ?? '';
                $isApproved = ($approvalStatus == 'approved');
                
                if ($isApproved) {
                    if ($type == 'bill') {
                        $actualBalance += $amount;
                    } elseif ($type == 'payment') {
                        $actualBalance -= $amount;
                    } elseif ($type == 'balance') {
                        if (stripos($description, 'Opening Balance') !== false) {
                            $actualBalance = $amount;
                        } else {
                            if ($transactionType == 'credit') {
                                $actualBalance += $amount;
                            } elseif ($transactionType == 'debit') {
                                $actualBalance -= $amount;
                            } else {
                                if ($amount > 0) {
                                    $actualBalance += $amount;
                                } else {
                                    $actualBalance -= abs($amount);
                                }
                            }
                        }
                    } elseif ($type == 'return') {
                        $actualBalance -= $amount;
                    } elseif ($type == 'credit') {
                        $actualBalance += $amount;
                    } elseif ($type == 'debit') {
                        $actualBalance -= $amount;
                    } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                        if ($transactionType == 'credit') {
                            $actualBalance += $amount;
                        } elseif ($transactionType == 'debit') {
                            $actualBalance -= $amount;
                        } else {
                            if ($amount > 0) {
                                $actualBalance += $amount;
                            } else {
                                $actualBalance -= abs($amount);
                            }
                        }
                    }
                }
            }
            
            // Get bills with filters
            $billsQuery = $vendor->bills();
            if ($bill_from && $bill_to) {
                $billsQuery->whereBetween('date', [$bill_from, $bill_to]);
            } elseif ($bill_from) {
                $billsQuery->where('date', '>=', $bill_from);
            } elseif ($bill_to) {
                $billsQuery->where('date', '<=', $bill_to);
            }
            $vendorBills = $billsQuery->orderBy('date', 'DESC')->get();

            return view('admin.pages.vendors.view', compact(
                'vendor',
                'vendorBills',
                'vendorTransactions',
                'bill_from',
                'bill_to',
                'trans_from',
                'trans_to',
                'actualBalance'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to view vendor: ' . $e->getMessage());
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to view vendor: ' . $e->getMessage(),
            ]);
        }
    }
    
    public function edit($uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->first();
            if ($vendor) {
                return view('admin.pages.vendors.edit', get_defined_vars());
            } else {
                return redirect(route('vendors.list'))->with([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect(route('vendors.list'))->with([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $vendor = Vendor::where('uuid', $request->uuid)->first();
            if ($vendor) {
                if ($request->hasFile('profile')) {
                    if ($vendor->profile) {
                        Storage::delete('public/' . $vendor->profile);
                    }
                    $profile = $request->file('profile');
                    $profileName = time() . '.' . $profile->getClientOriginalExtension();
                    $profilePath = $profile->storeAs('vendor-profiles', $profileName, 'public');
                    $vendor->profile = $profilePath;
                    $vendor->save();
                }
                $vendor->update([
                    'id' => $vendor->id,
                    'company_name' => $request->company_name,
                    'person_name' => $request->person_name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'phone' => $request->phone,
                ]);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Updated successfully'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    
    public function delete($uuid)
    {
        try {
            DB::beginTransaction();
            $vendor = Vendor::where('uuid', $uuid)->first();
            if ($vendor) {
                $vendor->update([
                    'active' => false,
                ]);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Deleted successfully'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Internal server error! Please contact your developer'
            ]);
        }
    }

    /**
     * DOWNLOAD Bank Statement as PDF
     */
    public function downloadBankStatement(Request $request, $uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->first();
            if (!$vendor) {
                return redirect(route('vendors.list'))->with([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }

            // =============================================
            // APPLY SAME FILTERS AS VIEW PAGE
            // =============================================
            $trans_from = $request->trans_from;
            $trans_to = $request->trans_to;

            // Get vendor transactions with filters
            $transactionsQuery = $vendor->vendorTransactions();
            
            // Apply date filters to transactions
            if ($trans_from && $trans_to) {
                $transactionsQuery->whereBetween('date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $transactionsQuery->where('date', '>=', $trans_from);
            } elseif ($trans_to) {
                $transactionsQuery->where('date', '<=', $trans_to);
            }

            // Get all vendor transactions in ASCENDING order for correct running balance
            $vendorTransactions = $transactionsQuery
                ->with(['bill.billProducts.product'])
                ->orderBy('date', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();

            // =============================================
            // FILTER OUT BILL TYPE TRANSACTIONS
            // ONLY SHOW GENERAL ENTRIES IN BANK STATEMENT
            // =============================================
            $filteredTransactions = $vendorTransactions->filter(function ($transaction) {
                $type = strtolower($transaction->type ?? '');
                // Skip bill type transactions
                return $type != 'bill';
            });

            // =============================================
            // CALCULATE RUNNING BALANCE
            // ONLY APPROVED TRANSACTIONS AFFECT BALANCE
            // =============================================
            $runningBalance = 0;
            $transactionsWithBalance = [];

            foreach ($filteredTransactions as $transaction) {
                $amount = floatval($transaction->amount);
                $type = strtolower($transaction->type ?? '');
                $approvalStatus = $transaction->approval_status ?? 'pending';
                $description = $transaction->description ?? '';
                $transactionType = $transaction->transaction_type ?? '';
                $isApproved = ($approvalStatus == 'approved');
                
                // =============================================
                // ONLY APPROVED TRANSACTIONS AFFECT BALANCE
                // =============================================
                if ($isApproved) {
                    if ($type == 'payment') {
                        // Payment = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                    } elseif ($type == 'balance') {
                        if (stripos($description, 'Opening Balance') !== false) {
                            // Opening Balance = SET the starting balance
                            $runningBalance = $amount;
                        } else {
                            // General Entry from daybook
                            if ($transactionType == 'credit') {
                                // Credit = Money IN = ADD to balance
                                $runningBalance += $amount;
                            } elseif ($transactionType == 'debit') {
                                // Debit = Money OUT = SUBTRACT from balance
                                $runningBalance -= $amount;
                            } else {
                                // Fallback: positive = Money IN, negative = Money OUT
                                if ($amount > 0) {
                                    $runningBalance += $amount;
                                } else {
                                    $runningBalance -= abs($amount);
                                }
                            }
                        }
                    } elseif ($type == 'return') {
                        // Return = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                    } elseif ($type == 'credit') {
                        // Credit = Money IN = ADD to balance
                        $runningBalance += $amount;
                    } elseif ($type == 'debit') {
                        // Debit = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                    } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                        // General Entry
                        if ($transactionType == 'credit') {
                            // Credit = Money IN = ADD to balance
                            $runningBalance += $amount;
                        } elseif ($transactionType == 'debit') {
                            // Debit = Money OUT = SUBTRACT from balance
                            $runningBalance -= $amount;
                        } else {
                            // Fallback: positive = Money IN, negative = Money OUT
                            if ($amount > 0) {
                                $runningBalance += $amount;
                            } else {
                                $runningBalance -= abs($amount);
                            }
                        }
                    }
                }
                
                // Create a copy with calculated balance
                $transactionCopy = clone $transaction;
                $transactionCopy->calculated_balance = $runningBalance;
                $transactionCopy->is_approved = $isApproved;
                $transactionsWithBalance[] = $transactionCopy;
            }

            // Reverse for display (newest first)
            $vendorTransactions = array_reverse($transactionsWithBalance);

            // Calculate summary totals
            $totalDebits = 0;
            $totalCredits = 0;
            
            foreach ($transactionsWithBalance as $transaction) {
                $amount = floatval($transaction->amount);
                $type = strtolower($transaction->type ?? '');
                $transactionType = $transaction->transaction_type ?? '';
                $description = $transaction->description ?? '';
                $isApproved = $transaction->is_approved ?? false;
                
                if (!$isApproved) continue;
                
                if ($type == 'payment') {
                    $totalDebits += $amount;
                } elseif ($type == 'balance') {
                    if (stripos($description, 'Opening Balance') !== false) {
                        if ($amount > 0) {
                            $totalCredits += $amount;
                        } else {
                            $totalDebits += abs($amount);
                        }
                    } else {
                        // General Entry
                        if ($transactionType == 'credit' || $amount > 0) {
                            $totalCredits += $amount;
                        } else {
                            $totalDebits += abs($amount);
                        }
                    }
                } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                    if ($transactionType == 'credit' || $amount > 0) {
                        $totalCredits += $amount;
                    } else {
                        $totalDebits += abs($amount);
                    }
                } elseif ($type == 'credit') {
                    $totalCredits += $amount;
                } elseif ($type == 'debit') {
                    $totalDebits += $amount;
                } elseif ($type == 'return') {
                    $totalDebits += $amount;
                }
            }

            // Get company settings
            $companySettings = null;
            if (Schema::hasTable('settings')) {
                $companySettings = DB::table('settings')->first();
            }

            if (!$companySettings) {
                $companySettings = (object)[
                    'name' => 'Intekhab Sanitary Fittings',
                    'logo' => null,
                    'address' => 'Main Road, Sialkot, Pakistan',
                    'mobile' => '+92 300 0000000',
                ];
            }

            // =============================================
            // FETCH BATCH ENTRIES FOR GENERAL ENTRIES
            // =============================================
            $generalEntryDetails = [];
            foreach ($vendorTransactions as $transaction) {
                $type = strtolower($transaction->type ?? '');
                $isGeneralEntry = ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '');
                
                // If this is a general entry, check if it has a batch_id
                if ($isGeneralEntry && isset($transaction->batch_id) && $transaction->batch_id) {
                    $batchId = $transaction->batch_id;
                    // Get all entries in this batch
                    $batchEntries = \App\Models\Daybook::where('batch_id', $batchId)->get();
                    if ($batchEntries->count() > 1) {
                        $generalEntryDetails[$transaction->id] = [
                            'batch_id' => $batchId,
                            'entries' => $batchEntries,
                            'count' => $batchEntries->count(),
                            'total' => $batchEntries->sum('amount')
                        ];
                    }
                }
            }

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('admin.pages.vendors.bank-statement-pdf', compact(
                'vendor', 
                'vendorTransactions',
                'companySettings',
                'totalDebits',
                'totalCredits',
                'trans_from',
                'trans_to',
                'generalEntryDetails'
            ));
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Download as file
            $filename = 'Vendor_Bank_Statement_' . $vendor->company_name . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
            
        } catch (\Throwable $th) {
            Log::error('Bank Statement PDF Error: ' . $th->getMessage());
            Log::error($th->getTraceAsString());
            return redirect(route('vendors.list'))->with([
                'status' => false,
                'message' => 'Internal server error: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * VIEW Bank Statement as PDF in Browser
     */
    public function bankStatement($uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->first();
            
            if (!$vendor) {
                return redirect()->route('vendors.list')->with('error', 'Vendor not found');
            }

            // Get request filters
            $trans_from = request()->trans_from;
            $trans_to = request()->trans_to;

            // Get ONLY vendor transactions (same as view page - NO daybook entries separately)
            $transactionsQuery = $vendor->vendorTransactions();
            
            // Apply date filters to transactions
            if ($trans_from && $trans_to) {
                $transactionsQuery->whereBetween('date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $transactionsQuery->where('date', '>=', $trans_from);
            } elseif ($trans_to) {
                $transactionsQuery->where('date', '<=', $trans_to);
            }
            
            // Get transactions ordered by date (ASCENDING for statement - oldest first)
            $allTransactions = $transactionsQuery->orderBy('date', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();

            // Filter out bill type transactions (same as view)
            $filteredTransactions = $allTransactions->filter(function ($transaction) {
                $type = strtolower($transaction->type ?? '');
                // Skip bill type transactions
                return $type != 'bill';
            });

            // Calculate running balance correctly (only approved transactions affect balance)
            $runningBalance = 0;
            $transactionsWithBalance = [];

            foreach ($filteredTransactions as $transaction) {
                $amount = floatval($transaction->amount);
                $type = strtolower($transaction->type ?? '');
                $approvalStatus = $transaction->approval_status ?? 'pending';
                $description = $transaction->description ?? '';
                $transactionType = $transaction->transaction_type ?? '';
                $isApproved = ($approvalStatus == 'approved');
                
                // Store the original amount for display
                $transaction->original_amount = $amount;
                
                // ONLY APPROVED TRANSACTIONS AFFECT BALANCE
                if ($isApproved) {
                    if ($type == 'payment') {
                        // Payment = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                        $transaction->transaction_type_display = 'debit';
                    } elseif ($type == 'balance') {
                        if (stripos($description, 'Opening Balance') !== false) {
                            // Opening Balance = SET the starting balance
                            $runningBalance = $amount;
                            $transaction->transaction_type_display = $amount > 0 ? 'credit' : 'debit';
                        } else {
                            // General Entry from daybook
                            if ($transactionType == 'credit') {
                                // Credit = Money IN = ADD to balance
                                $runningBalance += $amount;
                                $transaction->transaction_type_display = 'credit';
                            } elseif ($transactionType == 'debit') {
                                // Debit = Money OUT = SUBTRACT from balance
                                $runningBalance -= $amount;
                                $transaction->transaction_type_display = 'debit';
                            } else {
                                // Fallback: positive = Money IN, negative = Money OUT
                                if ($amount > 0) {
                                    $runningBalance += $amount;
                                    $transaction->transaction_type_display = 'credit';
                                } else {
                                    $runningBalance -= abs($amount);
                                    $transaction->transaction_type_display = 'debit';
                                }
                            }
                        }
                    } elseif ($type == 'return') {
                        // Return = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                        $transaction->transaction_type_display = 'debit';
                    } elseif ($type == 'credit') {
                        // Credit = Money IN = ADD to balance
                        $runningBalance += $amount;
                        $transaction->transaction_type_display = 'credit';
                    } elseif ($type == 'debit') {
                        // Debit = Money OUT = SUBTRACT from balance
                        $runningBalance -= $amount;
                        $transaction->transaction_type_display = 'debit';
                    } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                        // General Entry
                        if ($transactionType == 'credit') {
                            // Credit = Money IN = ADD to balance
                            $runningBalance += $amount;
                            $transaction->transaction_type_display = 'credit';
                        } elseif ($transactionType == 'debit') {
                            // Debit = Money OUT = SUBTRACT from balance
                            $runningBalance -= $amount;
                            $transaction->transaction_type_display = 'debit';
                        } else {
                            // Fallback: positive = Money IN, negative = Money OUT
                            if ($amount > 0) {
                                $runningBalance += $amount;
                                $transaction->transaction_type_display = 'credit';
                            } else {
                                $runningBalance -= abs($amount);
                                $transaction->transaction_type_display = 'debit';
                            }
                        }
                    }
                } else {
                    // For pending transactions, just set display type but don't affect balance
                    if ($type == 'payment' || $type == 'return' || $type == 'debit') {
                        $transaction->transaction_type_display = 'debit';
                    } elseif ($type == 'credit' || $type == 'balance') {
                        $transaction->transaction_type_display = 'credit';
                    } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                        if ($transactionType == 'credit' || $amount > 0) {
                            $transaction->transaction_type_display = 'credit';
                        } else {
                            $transaction->transaction_type_display = 'debit';
                        }
                    }
                }
                
                // Set current balance
                $transaction->current_balance = $runningBalance;
                $transaction->is_approved = $isApproved;
                $transactionsWithBalance[] = $transaction;
            }

            // Reverse for display (newest first) - same as view
            $vendorTransactions = array_reverse($transactionsWithBalance);

            // Calculate summary totals
            $totalDebits = 0;
            $totalCredits = 0;
            
            foreach ($transactionsWithBalance as $transaction) {
                $amount = floatval($transaction->amount);
                $type = strtolower($transaction->type ?? '');
                $transactionType = $transaction->transaction_type ?? '';
                $description = $transaction->description ?? '';
                $isApproved = $transaction->is_approved ?? false;
                
                if (!$isApproved) continue;
                
                if ($type == 'payment') {
                    $totalDebits += $amount;
                } elseif ($type == 'balance') {
                    if (stripos($description, 'Opening Balance') !== false) {
                        if ($amount > 0) {
                            $totalCredits += $amount;
                        } else {
                            $totalDebits += abs($amount);
                        }
                    } else {
                        // General Entry
                        if ($transactionType == 'credit' || $amount > 0) {
                            $totalCredits += $amount;
                        } else {
                            $totalDebits += abs($amount);
                        }
                    }
                } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                    if ($transactionType == 'credit' || $amount > 0) {
                        $totalCredits += $amount;
                    } else {
                        $totalDebits += abs($amount);
                    }
                } elseif ($type == 'credit') {
                    $totalCredits += $amount;
                } elseif ($type == 'debit') {
                    $totalDebits += $amount;
                } elseif ($type == 'return') {
                    $totalDebits += $amount;
                }
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

            // Return the PDF view (not download)
            return view('admin.pages.vendors.bank-statement-pdf', compact(
                'vendor',
                'vendorTransactions',
                'companySettings',
                'totalDebits',
                'totalCredits',
                'trans_from',
                'trans_to'
            ));

        } catch (\Exception $e) {
            \Log::error('Vendor Bank Statement Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to load statement: ' . $e->getMessage());
        }
    }
}