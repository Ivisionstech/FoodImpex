<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerBill;
use Illuminate\Http\Request;

class ProfitController extends Controller
{
    public function list()
    {
        try {
            $customerBills = CustomerBill::paginate(10);
            return view('admin.pages.profit.list', compact('customerBills'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Internal Server Error',
            ]);
        }
    }
}
