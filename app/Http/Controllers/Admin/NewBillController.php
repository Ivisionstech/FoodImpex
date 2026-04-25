<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewBillController extends Controller
{
    /**
     * Show the New Sales Invoice form.
     */
    public function create()
    {
        return view('admin.pages.customers.bills.new_create');
    }
}
