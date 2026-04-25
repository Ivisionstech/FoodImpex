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
            $vendor = Vendor::create([
                'uuid' => Str::uuid(),
                'company_name' => $request->company_name,
                'person_name' => $request->person_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'balance' => $request->balance,
            ]);
            if ($request->hasFile('profile')) {
                $profile = $request->file('profile');
                $profileName = time() . '.' . $profile->getClientOriginalExtension();
                $profilePath = $profile->storeAs('vendor-profiles', $profileName, 'public');
                $vendor->profile = $profilePath;
                $vendor->save();
            }
            VendorTransaction::create([
                'uuid' => Str::uuid(),
                'date' => $request->open_balance_date ?? now(),
                'amount' => $request->balance,
                'type' => 'Balance',
                'current_balance' => $request->balance,
                'vendor_id' => $vendor->id,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Vendor Added Successfully',
                'redirect' => route('vendors.list')
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    public function view(Request $request, $uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->first();
            if (!$vendor) {
                return redirect(route('vendors.list'))->with([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }

            $bill_from = $request->bill_from;
            $bill_to = $request->bill_to;
            $trans_from = $request->trans_from;
            $trans_to = $request->trans_to;

            $billsQuery = $vendor->bills();
            $transactionsQuery = $vendor->vendorTransactions();

            // Filter Bills
            if ($bill_from && $bill_to) {
                $billsQuery->whereBetween('date', [$bill_from, $bill_to]);
            } elseif ($bill_from) {
                $billsQuery->where('date', '>=', $bill_from);
            } elseif ($bill_to) {
                $billsQuery->where('date', '<=', $bill_to);
            }

            // Filter Transactions
            if ($trans_from && $trans_to) {
                $transactionsQuery->whereBetween('date', [$trans_from, $trans_to]);
            } elseif ($trans_from) {
                $transactionsQuery->where('date', '>=', $trans_from);
            } elseif ($trans_to) {
                $transactionsQuery->where('date', '<=', $trans_to);
            }

            $vendorBills = $billsQuery->orderBy('date', 'DESC')->get();
            $vendorTransactions = $transactionsQuery->orderBy('date', 'DESC')->get();

            return view('admin.pages.vendors.view', compact(
                'vendor',
                'vendorBills',
                'vendorTransactions',
                'bill_from',
                'bill_to',
                'trans_from',
                'trans_to'
            ));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect(route('vendors.list'))->with([
                'status' => false,
                'message' => 'Internal server error'
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
                    'message' => 'Deleted sucessfully'
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

    public function downloadBankStatement($uuid)
    {
        try {
            $vendor = Vendor::where('uuid', $uuid)->first();
            if (!$vendor) {
                return redirect(route('vendors.list'))->with([
                    'status' => false,
                    'message' => 'Vendor was not found'
                ]);
            }

            $vendorTransactions = $vendor->vendorTransactions()
                ->with(['bill.billProducts.product'])
                ->orderBy('date', 'ASC')
                ->get();


            return view('admin.pages.vendors.bank-statement-pdf', compact('vendor', 'vendorTransactions'));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return redirect(route('vendors.list'))->with([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
}
