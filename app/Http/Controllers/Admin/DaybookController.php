<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Daybook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DaybookController extends Controller
{
    public function list(Request $request)
    {
        try {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $entry_type = $request->entry_type;
            
            $query = Daybook::query();

            // Date filter
            if ($from_date && $to_date) {
                $query->whereBetween('transaction_date', [$from_date, $to_date]);
            } elseif ($from_date) {
                $query->where('transaction_date', '>=', $from_date);
            } elseif ($to_date) {
                $query->where('transaction_date', '<=', $to_date);
            }

            // Entry type filter - check both type field and description
            if ($entry_type && $entry_type != 'all') {
                $query->where(function($q) use ($entry_type) {
                    $q->where('type', $entry_type)
                      ->orWhere('description', 'LIKE', '%' . $entry_type . '%');
                });
            }

            $daybooks = $query->orderBy('id', 'desc')->paginate(10);
            
            return view('admin.pages.daybooks.list', compact('daybooks', 'from_date', 'to_date', 'entry_type'));
        } catch (\Throwable $th) {
            \Log::error('Daybook list error: ' . $th->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: ' . $th->getMessage());
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
            
            // Generate UUID if not provided
            $uuid = (string) Str::uuid();
            
            $daybook = Daybook::create([
                'uuid' => $uuid,
                'transaction_date' => $request->expense_date ?? now(),
                'amount' => $request->amount,
                'in_hand' => $in_hand,
                'description' => $request->description,
                'status' => $request->status,
                'type' => $request->entry_type ?? 'transaction',
                'approval_status' => 'approved',
            ]);
            
            $user->update(['in_hand' => $in_hand]);
            
            DB::commit();
            
            return response()->json([
                'status' => true, 
                'message' => 'Daybook entry created successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Daybook store error: ' . $th->getMessage());
            return response()->json([
                'status' => false, 
                'message' => 'Something went wrong: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * Display the specified daybook entry
     */
    public function view($uuid)
    {
        try {
            $daybook = Daybook::with(['customerTransaction.customer', 'vendorTransaction.vendor', 'expense'])
                ->where('uuid', $uuid)
                ->firstOrFail();
            return view('admin.pages.daybooks.view', compact('daybook'));
        } catch (\Throwable $th) {
            \Log::error('Daybook view error: ' . $th->getMessage());
            return redirect()->route('daybooks.list')
                ->with('error', 'Daybook entry not found');
        }
    }

    /**
     * Delete a daybook entry
     */
    public function delete($uuid)
    {
        try {
            DB::beginTransaction();
            
            $daybook = Daybook::where('uuid', $uuid)->firstOrFail();
            
            // Reverse the in_hand balance
            $user = User::first();
            if ($daybook->status == 1) {
                // If it was a debit, add back the amount
                $user->in_hand = $user->in_hand + $daybook->amount;
            } else {
                // If it was a credit, subtract the amount
                $user->in_hand = $user->in_hand - $daybook->amount;
            }
            $user->save();
            
            $daybook->delete();
            
            DB::commit();
            
            return redirect()->route('daybooks.list')
                ->with('success', 'Daybook entry deleted successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Daybook delete error: ' . $th->getMessage());
            return redirect()->route('daybooks.list')
                ->with('error', 'Failed to delete entry: ' . $th->getMessage());
        }
    }
}