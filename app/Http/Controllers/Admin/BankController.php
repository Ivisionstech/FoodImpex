<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBankRequest;
use App\Models\Bank;
use App\Models\Daybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BankController extends Controller
{
    public function list()
    {
        try {
            $banks = Bank::paginate(10);
            return view('admin.pages.banks.list', compact('banks'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function create()
    {
        try {
            return view('admin.pages.banks.create');
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function store(StoreBankRequest $request)
    {
        try {
            Bank::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'account_title' => $request->account_title,
                'account_number' => $request->account_number,
                'account_balance' => $request->account_balance,
            ]);
            Daybook::create([
                'transaction_date' => now(),
                'amount' => $request->account_balance,
                'type' => 'transaction',
                'description' => "Bank created with a balance of {$request->account_balance}",
                'customer_transaction_id' => null,
                'vendor_transaction_id' => null,
                'expense_id' => null,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Bank created successfully',
                'redirect' => route('banks.list'),
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function edit($uuid)
    {
        try {
            $bank = Bank::where('uuid', $uuid)->first();
            return view('admin.pages.banks.edit', compact('bank'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function update(StoreBankRequest $request, $uuid)
    {
        try {
            $bank = Bank::where('uuid', $uuid)->first();
            $bank->update([
                'name' => $request->name,
                'account_title' => $request->account_title,
                'account_number' => $request->account_number,
                'account_balance' => $request->account_balance,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Bank updated successfully',
                'redirect' => route('banks.list'),
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function view(Request $request, $uuid)
    {
        try {
            $bank = Bank::where('uuid', $uuid)->first();

            $transactionsQuery = $bank->bankTransactions()->orderBy('created_at', 'asc');

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $from = $request->from_date . ' 00:00:00';
                $to = $request->to_date . ' 23:59:59';
                $transactionsQuery->whereBetween('created_at', [$from, $to]);
            } elseif ($request->filled('from_date')) {
                $from = $request->from_date . ' 00:00:00';
                $transactionsQuery->where('created_at', '>=', $from);
            } elseif ($request->filled('to_date')) {
                $to = $request->to_date . ' 23:59:59';
                $transactionsQuery->where('created_at', '<=', $to);
            }

            $filteredTransactions = $transactionsQuery->get();

            $bank->setRelation('bankTransactions', $filteredTransactions);

            return view('admin.pages.banks.view', compact('bank'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
