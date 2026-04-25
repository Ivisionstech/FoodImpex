<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankTransaction;
use App\Models\Cash;
use App\Models\CashTransaction;
use App\Models\Daybook;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function list()
    {
        try {

            $from_date = request('from_date');
            $to_date = request('to_date');
            if ($from_date || $to_date) {
                $expensesQuery = Expense::query();

                if ($from_date && $to_date) {
                    $expensesQuery->whereBetween('expense_date', [$from_date, $to_date]);
                } elseif ($from_date) {
                    $expensesQuery->where('expense_date', '>=', $from_date);
                } elseif ($to_date) {
                    $expensesQuery->where('expense_date', '<=', $to_date);
                }

                $expenses = $expensesQuery->orderBy('expense_date', 'desc')->paginate(10);
            } else {
                $expenses = Expense::orderBy('expense_date', 'desc')->paginate(10);
            }

            return view('admin.pages.expenses.list', compact('expenses', 'from_date', 'to_date'));
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function create()
    {
        try {
            $banks = Bank::all();
            return view('admin.pages.expenses.create', compact('banks'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $expense = Expense::create([
                'expense_date' => $request->expense_date,
                'name' => $request->name,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                // 'bank_transaction_id' => $request->bank_id,
                // 'cash_transaction_id' => $request->cash_id,
            ]);
            if ($request->payment_method == 'bank') {
                $bank = Bank::find($request->bank_id);
                if (!$bank) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bank not found',
                    ]);
                }
                if ($bank->account_balance < $request->amount) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bank balance is not enough',
                    ]);
                }
                $bank->decrement('account_balance', $request->amount);
                $bankTransaction = BankTransaction::create([
                    'bank_id' => $bank->id,
                    'amount' => $request->amount,
                    'balance' => $bank->account_balance - $request->amount,
                    'transaction_type' => 'debit',
                    'description' => $expense->name,
                ]);
                $expense->update([
                    'bank_transaction_id' => $bankTransaction->id,
                ]);
            } else {
                $cash = Cash::first();
                if (!$cash) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cash not found',
                    ]);
                }
                if ($cash->balance < $request->amount) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cash balance is not enough',
                    ]);
                }
                $cash->decrement('balance', $request->amount);
                $cashTransaction = CashTransaction::create([
                    'cash_id' => $cash->id,
                    'amount' => $request->amount,
                    'balance' => $cash->balance - $request->amount,
                    'transaction_type' => 'debit',
                    'description' => $expense->name,
                ]);
                $expense->update([
                    'cash_transaction_id' => $cashTransaction->id,
                ]);
            }
            Daybook::create([
                'transaction_date' => now(),
                'amount' => $request->amount,
                'description' => $expense->name,
                'type' => 'expense',
                'expense_id' => $expense->id,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Expense created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function view($uuid)
    {
        try {
            $expense = Expense::where('uuid', $uuid)->first();
            return view('admin.pages.expenses.view', compact('expense'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
