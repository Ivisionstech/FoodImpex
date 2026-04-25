<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $quotations = Quotation::all();
        $vendors = Vendor::all();
        $customers = Customer::all();
        $sendingBalance = Vendor::sum('balance');
        $receivingBalance = Customer::sum('balance');

        return view('admin.pages.index', compact('products', 'quotations', 'vendors', 'customers', 'sendingBalance', 'receivingBalance'));
    }
}
