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
            $cash = Cash::first();
            return view('admin.pages.cash.list', compact('cash'));
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
            DB::beginTransaction();
            $cash = Cash::create([
                'uuid' => Str::uuid(),
                'balance' => $request->balance,
            ]);
            CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'credit',
                'amount' => $request->balance,
                'balance' => $request->balance,
                'description' => 'Initial Cash',
            ]);
            Daybook::create([
                'transaction_date' => now(),
                'amount' => $request->balance,
                'type' => 'transaction',
                'description' => "Cash created with a balance of {$request->balance}",
                'customer_transaction_id' => null,
                'vendor_transaction_id' => null,
                'expense_id' => null,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Cash created successfully',
                'redirect' => route('cash.list'),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function view(Request $request)
    {
        try {
            $cash = Cash::with('cashTransactions')->first(); // یا جیسا آپ لے رہے ہیں

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
            DB::beginTransaction();
            $cash = Cash::first();
            $cash->increment('balance', $request->amount);

            $transaction = CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'credit',
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'description' => $request->description,
            ]);

            Daybook::create([
                'transaction_date' => now(),
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => "Cash Added: {$request->description}",
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Cash added successfully',
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
            DB::beginTransaction();
            $cash = Cash::first();

            if ($cash->balance < $request->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient cash balance',
                ]);
            }

            $cash->decrement('balance', $request->amount);

            $transaction = CashTransaction::create([
                'cash_id' => $cash->id,
                'transaction_type' => 'debit',
                'amount' => $request->amount,
                'balance' => $cash->balance,
                'description' => $request->description,
            ]);

            Daybook::create([
                'transaction_date' => now(),
                'amount' => $request->amount,
                'type' => 'transaction',
                'description' => "Cash Deducted: {$request->description}",
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Cash deducted successfully',
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
