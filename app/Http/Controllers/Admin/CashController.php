<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashController extends Controller
{
    public function list()
    {
        try {
            // Get all cash records
            $cashes = Cash::orderBy('created_at', 'desc')->get();
            
            // Calculate total balance
            $totalBalance = Cash::sum('balance');
            
            return view('admin.pages.cash.list', compact('cashes', 'totalBalance'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'balance' => 'required|numeric|min:0',
                'cash_date' => 'nullable|date'
            ]);

            DB::beginTransaction();
            
            // Use selected date or current date
            $cashDate = $request->cash_date ? date('Y-m-d 00:00:00', strtotime($request->cash_date)) : now();
            
            $cash = Cash::create([
                'uuid' => Str::uuid(),
                'balance' => $request->balance,
                'created_at' => $cashDate,
                'updated_at' => $cashDate,
            ]);
            
            // Create cash transaction as CREDIT by default
            CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'credit',
                'amount' => $request->balance,
                'balance' => $request->balance,
                'description' => 'Initial Cash',
                'created_at' => $cashDate,
            ]);
            
            // Create Daybook entry with status = 0 (CREDIT)
            Daybook::create([
                'transaction_date' => $cashDate,
                'amount' => $request->balance,
                'status' => 0, // 0 = Credit (CR)
                'type' => 'transaction',
                'description' => "Cash created with a balance of {$request->balance} - Credit",
                'customer_transaction_id' => null,
                'vendor_transaction_id' => null,
                'expense_id' => null,
                'created_at' => $cashDate,
                'credit_type' => 'cash',
                'credit_id' => $cash->id,
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'Cash created successfully',
                'redirect' => route('cash.list'),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Cash store error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ]);
        }
    }

    public function view(Request $request)
    {
        try {
            // Get the latest cash for view
            $cash = Cash::with('cashTransactions')->latest()->first();

            if (!$cash) {
                return redirect()->route('cash.list')->with('error', 'No cash found');
            }

            $transactionsQuery = $cash->cashTransactions();

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $from = $request->from_date . ' 00:00:00';
                $to   = $request->to_date . ' 23:59:59';
                $transactionsQuery->whereBetween('created_at', [$from, $to]);
            } elseif ($request->filled('from_date')) {
                $from = $request->from_date . ' 00:00:00';
                $transactionsQuery->where('created_at', '>=', $from);
            } elseif ($request->filled('to_date')) {
                $to = $request->to_date . ' 23:59:59';
                $transactionsQuery->where('created_at', '<=', $to);
            }

            $transactionsQuery->orderBy('created_at', 'asc');

            $filteredTransactions = $transactionsQuery->get();

            return view('admin.pages.cash.view', compact('cash', 'filteredTransactions'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function addCash(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'cash_date' => 'nullable|date',
                'description' => 'nullable|string'
            ]);

            DB::beginTransaction();
            
            // Use selected date or current date
            $cashDate = $request->cash_date ? date('Y-m-d 00:00:00', strtotime($request->cash_date)) : now();
            
            // Get the latest cash or create new if none exists
            $cash = Cash::latest()->first();
            
            if (!$cash) {
                // Create new cash if none exists
                $cash = Cash::create([
                    'uuid' => Str::uuid(),
                    'balance' => 0,
                    'created_at' => $cashDate,
                    'updated_at' => $cashDate,
                ]);
            }
            
            $cash->increment('balance', $request->amount);
            $cash->update(['updated_at' => $cashDate]);

            // Create cash transaction as CREDIT by default
            $transaction = CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'credit',
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'description' => $request->description,
                'created_at' => $cashDate,
            ]);

            // Create Daybook entry with status = 0 (CREDIT)
            Daybook::create([
                'transaction_date' => $cashDate,
                'amount' => $request->amount,
                'status' => 0, // 0 = Credit (CR)
                'type' => 'transaction',
                'description' => "Cash Added: {$request->description} - Credit",
                'customer_transaction_id' => null,
                'vendor_transaction_id' => null,
                'expense_id' => null,
                'created_at' => $cashDate,
                'credit_type' => 'cash',
                'credit_id' => $cash->id,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Cash added successfully',
                'redirect' => route('cash.list'),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ]);
        }
    }

    public function deductCash(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'cash_date' => 'nullable|date',
                'description' => 'nullable|string'
            ]);

            DB::beginTransaction();
            
            // Use selected date or current date
            $cashDate = $request->cash_date ? date('Y-m-d 00:00:00', strtotime($request->cash_date)) : now();
            
            // Get the latest cash
            $cash = Cash::latest()->first();

            if (!$cash) {
                return response()->json([
                    'status' => false,
                    'message' => 'No cash found',
                ]);
            }

            if ($cash->balance < $request->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient cash balance',
                ]);
            }

            $cash->decrement('balance', $request->amount);
            $cash->update(['updated_at' => $cashDate]);

            // Create cash transaction as DEBIT for deduction
            $transaction = CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'debit',
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'description' => $request->description,
                'created_at' => $cashDate,
            ]);

            // Create Daybook entry with status = 1 (DEBIT)
            Daybook::create([
                'transaction_date' => $cashDate,
                'amount' => $request->amount,
                'status' => 1, // 1 = Debit (DR)
                'type' => 'transaction',
                'description' => "Cash Deducted: {$request->description} - Debit",
                'customer_transaction_id' => null,
                'vendor_transaction_id' => null,
                'expense_id' => null,
                'created_at' => $cashDate,
                'debit_type' => 'cash',
                'debit_id' => $cash->id,
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Cash deducted successfully',
                'redirect' => route('cash.list'),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $th->getMessage(),
            ]);
        }
    }
}