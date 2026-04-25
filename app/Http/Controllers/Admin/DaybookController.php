<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Daybook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaybookController extends Controller
{
    public function list()
    {
        try {
            $from_date = request('from_date');
            $to_date = request('to_date');
            $query = Daybook::query();

            if ($from_date && $to_date) {
                $query->whereBetween('transaction_date', [$from_date, $to_date]);
            } elseif ($from_date) {
                $query->where('transaction_date', '>=', $from_date);
            } elseif ($to_date) {
                $query->where('transaction_date', '<=', $to_date);
            }

            $daybooks = $query->orderBy('id', 'asc')->paginate(40);
            return view('admin.pages.daybooks.list', compact('daybooks', 'from_date', 'to_date'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function create()
    {
        return view('admin.pages.daybooks.create');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $type = $request->status;
            $user = User::first();
            $in_hand = $user->in_hand;
            if ($type == 1) {
                $in_hand = $in_hand - $request->amount;
            } else {
                $in_hand = $in_hand + $request->amount;
            }
            Daybook::create([
                'expense_date' => $request->expense_date,
                'amount' => $request->amount,
                'in_hand' => $in_hand,
                'description' => $request->description,
                'status' => $request->status,
            ]);
            $user->update([ 'in_hand' => $in_hand]);
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Daybook created successfully']);
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong']);
        }
    }
}
