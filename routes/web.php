<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorBillController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerBillController;
use App\Http\Controllers\Admin\DaybookController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\GeneralTransactionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfitController;
use App\Http\Controllers\Admin\QuotationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/storage-link', function () {
    try {
        Artisan::call('storage:link');
        return 'success';
    } catch (\Exception $e) {
        dd($e->getMessage());
        return 'failed';
    }
})->name('storage.link');

// ==================== DIRECT BANK STATEMENT ROUTES (NO CONFLICT WITH ANY OTHER ROUTES) ====================
Route::get('customer-statement/{uuid}', [CustomerBillController::class, 'bankStatementHtml'])->name('customer.statement');
Route::get('customer-statement-pdf/{uuid}', [CustomerBillController::class, 'bankStatementPdf'])->name('customer.statement-pdf');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

 // ==================== VENDORS ROUTES ====================
Route::prefix('vendors')->name('vendors.')->group(function () {
    Route::get('list', [VendorController::class, 'list'])->name('list');
    Route::get('create', [VendorController::class, 'create'])->name('create');
    Route::post('store', [VendorController::class, 'store'])->name('store');
    Route::get('edit/{uuid}', [VendorController::class, 'edit'])->name('edit');
    Route::get('view/{uuid}', [VendorController::class, 'view'])->name('view');
    Route::post('update/{uuid}', [VendorController::class, 'update'])->name('update');
    Route::post('delete/{uuid}', [VendorController::class, 'delete'])->name('delete');

    Route::get('payments/create', [VendorBillController::class, 'generalPaymentCreate'])->name('payments.create');
    Route::post('payments/general-store', [VendorBillController::class, 'generalPaymentStore'])->name('payments.store');
    Route::get('payments/list', [VendorBillController::class, 'paymentList'])->name('payments.list');
    Route::get('payments/show/{uuid}', [VendorBillController::class, 'paymentShow'])->name('payments.show');
    Route::get('payments/edit/{uuid}', [VendorBillController::class, 'paymentEdit'])->name('payments.edit');
    Route::post('payments/update/{uuid}', [VendorBillController::class, 'paymentUpdate'])->name('payments.update');
    Route::post('payments/delete/{uuid}', [VendorBillController::class, 'paymentDelete'])->name('payments.delete');

    Route::get('send-payment/{uuid}', [VendorBillController::class, 'sendPayment'])->name('send-payment');
    Route::post('send-payment/store/{uuid}', [VendorBillController::class, 'storeSendPayment'])->name('send-payment.store');
    
    // ============ BILLS ROUTES ============
    Route::get('bills/list', [VendorBillController::class, 'list'])->name('bills.list');
    
    // General Bill Creation (without stock update)
    Route::get('bills/general-create-2', [VendorBillController::class, 'generalCreate2'])->name('bills.general_create_2');
    Route::post('bills/general-store', [VendorBillController::class, 'generalStore'])->name('bills.general_store');
    
    // General Bill View, Edit, PDF
    Route::get('bills/general-show-2/{uuid}', [VendorBillController::class, 'generalShow2'])->name('bills.general_show_2');
    Route::get('bills/general-edit-2/{uuid}', [VendorBillController::class, 'generalEdit2'])->name('bills.general_edit_2');
    Route::put('bills/general-update-2/{uuid}', [VendorBillController::class, 'generalUpdate2'])->name('bills.general_update_2');
    Route::get('bills/general-pdf-2/{uuid}', [VendorBillController::class, 'generalPdf2'])->name('bills.general_pdf_2');
    
    // Old bill routes (for other types)
    Route::get('bills/create/{uuid}', [VendorBillController::class, 'create'])->name('bills.create');
    Route::post('bills/store/{uuid}', [VendorBillController::class, 'store'])->name('bills.store');
    Route::get('bills/edit/{uuid}', [VendorBillController::class, 'edit'])->name('bills.edit');
    Route::put('bills/update/{uuid}', [VendorBillController::class, 'update'])->name('bills.update');
    Route::post('bills/delete/{uuid}', [VendorBillController::class, 'delete'])->name('bills.delete');
    
    // Approve & Reject Bill Routes
    Route::post('bills/approve/{uuid}', [VendorBillController::class, 'approveBill'])->name('bills.approve');
    Route::post('bills/reject/{uuid}', [VendorBillController::class, 'rejectBill'])->name('bills.reject');
    
    Route::get('bills/{uuid}', [VendorBillController::class, 'show'])->name('bills.show');
    Route::get('bills/{uuid}/download', [VendorBillController::class, 'downloadPdf'])->name('bills.download');
    
    Route::get('payment-details/{uuid}', [VendorBillController::class, 'paymentDetails'])->name('payment-details');
    Route::get('bank-statement/{uuid}', [VendorController::class, 'downloadBankStatement'])->name('bank-statement');
});
    // ==================== CUSTOMERS ROUTES ====================
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('list', [CustomerController::class, 'list'])->name('list');
        Route::get('create', [CustomerController::class, 'create'])->name('create');
        Route::post('store', [CustomerController::class, 'store'])->name('store');
        Route::get('edit/{uuid}', [CustomerController::class, 'edit'])->name('edit');
        Route::get('view/{uuid}', [CustomerController::class, 'view'])->name('view');
        Route::post('update', [CustomerController::class, 'update'])->name('update');
        Route::post('delete/{uuid}', [CustomerController::class, 'delete'])->name('delete');

        // Customer Payments
        Route::get('receive-payment-general', [CustomerController::class, 'receivePaymentGeneral'])->name('receive-payment.general');
        Route::post('receive-payment-general/store', [CustomerController::class, 'storeReceivePaymentGeneral'])->name('receive-payment.store-general');
        Route::get('receive-payments/list', [CustomerController::class, 'paymentsList'])->name('receive-payment.list');
        Route::get('receive-payment/{uuid}/edit', [CustomerController::class, 'editPayment'])->name('receive-payment.edit');
        Route::put('receive-payment/{uuid}/update', [CustomerController::class, 'updatePayment'])->name('receive-payment.update');
        Route::delete('receive-payment/{uuid}', [CustomerController::class, 'deletePayment'])->name('receive-payment.delete');

        
    });

    // ==================== CUSTOMER BILLS ROUTES ====================
    Route::get('bills/create/{uuid?}', [CustomerBillController::class, 'create'])->name('bills.create');
    Route::get('bills/new-create', [CustomerBillController::class, 'newsalecreate'])->name('new.bills.create');
    Route::get('bills/list', [CustomerBillController::class, 'list'])->name('bills.list');
    Route::get('bills/edit/{uuid}', [CustomerBillController::class, 'edit'])->name('bills.edit');
    Route::get('bills/new-edit/{uuid}', [CustomerBillController::class, 'newsaleedit'])->name('new.bills.edit');
    Route::get('bills/new-show/{uuid}', [CustomerBillController::class, 'newsaleshow'])->name('new.bills.show');
    Route::post('bills/update/{uuid}', [CustomerBillController::class, 'update'])->name('bills.update');
    Route::post('customers/bills/store', [CustomerBillController::class, 'store'])->name('customers.bills.store');
    Route::get('customers/bills/{uuid}', [CustomerBillController::class, 'show'])->name('customers.bills.show');
    Route::get('customers/bills/{uuid}/download', [CustomerBillController::class, 'downloadPdf'])->name('customers.bills.download');
    Route::get('customers/bills/{uuid}/download-new', [CustomerBillController::class, 'downloadNewPdf'])->name('customers.bills.download.new');
    Route::get('customers/receive-payment/{uuid}', [CustomerBillController::class, 'receivePayment'])->name('customers.receive-payment');
    Route::post('customers/receive-payment/store/{uuid}', [CustomerBillController::class, 'storeReceivePayment'])->name('customers.receive-payment.store');
    Route::get('customers/receive-payment/{uuid}/show', [CustomerBillController::class, 'showReceivePayment'])->name('customers.receive-payment.show');



    // Customer Bills Approve/Reject Routes
Route::post('customers/bills/approve/{uuid}', [CustomerBillController::class, 'approveBill'])->name('customers.bills.approve');
Route::delete('customers/bills/delete/{uuid}', [CustomerBillController::class, 'deleteInvoice'])->name('customers.bills.delete');

    // ==================== PRODUCTS ROUTES ====================
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/list', [ProductController::class, 'index'])->name('list');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('store', [ProductController::class, 'store'])->name('store');
        Route::get('edit/{uuid}', [ProductController::class, 'edit'])->name('edit');
        Route::post('update/{uuid}', [ProductController::class, 'update'])->name('update');
        Route::get('view/{uuid}', [ProductController::class, 'view'])->name('view');
        Route::post('add-stock', [ProductController::class, 'addStock'])->name('add-stock');
        Route::delete('/delete/{uuid}', [ProductController::class, 'destroy'])->name('delete');
    });

    // ==================== BANKS ROUTES ====================
    Route::prefix('banks')->name('banks.')->group(function () {
        Route::get('list', [BankController::class, 'list'])->name('list');
        Route::get('create', [BankController::class, 'create'])->name('create');
        Route::post('store', [BankController::class, 'store'])->name('store');
        Route::get('view/{uuid}', [BankController::class, 'view'])->name('view');
        Route::get('edit/{uuid}', [BankController::class, 'edit'])->name('edit');
        Route::post('update/{uuid}', [BankController::class, 'update'])->name('update');
        Route::post('delete/{uuid}', [BankController::class, 'delete'])->name('delete');
    });

    // ==================== CASH ROUTES ====================
    Route::prefix('cash')->name('cash.')->group(function () {
        Route::get('list', [CashController::class, 'list'])->name('list');
        Route::post('store', [CashController::class, 'store'])->name('store');
        Route::get('view', [CashController::class, 'view'])->name('view');
        Route::post('add-cash', [CashController::class, 'addCash'])->name('add-cash');
        Route::post('deduct-cash', [CashController::class, 'deductCash'])->name('deduct-cash');
    });

    // ==================== COMPANY ROUTES ====================
    Route::prefix('company')->name('company.')->group(function () {
        Route::get('info', [CompanyController::class, 'index'])->name('index');
        Route::post('update', [CompanyController::class, 'update'])->name('update');
    });

    // ==================== DAYBOOKS ROUTES ====================
    Route::prefix('daybooks')->name('daybooks.')->group(function () {
        Route::get('list', [DaybookController::class, 'list'])->name('list');
        Route::get('create', [DaybookController::class, 'create'])->name('create');
        Route::post('store', [DaybookController::class, 'store'])->name('store');
        Route::get('view/{uuid}', [DaybookController::class, 'view'])->name('view');
        Route::post('delete/{uuid}', [DaybookController::class, 'delete'])->name('delete');
    });

    // ==================== EXPENSES ROUTES ====================
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('list', [ExpenseController::class, 'list'])->name('list');
        Route::get('create', [ExpenseController::class, 'create'])->name('create');
        Route::post('store', [ExpenseController::class, 'store'])->name('store');
        Route::get('view/{uuid}', [ExpenseController::class, 'view'])->name('view');
        Route::post('delete/{uuid}', [ExpenseController::class, 'delete'])->name('delete');
    });

    // ==================== PROFITS ROUTES ====================
    Route::prefix('profits')->name('profits.')->group(function () {
        Route::get('list', [ProfitController::class, 'list'])->name('list');
    });

    // ==================== QUOTATIONS ROUTES ====================
    Route::prefix('quotations')->name('quotations.')->group(function () {
        Route::get('list', [QuotationController::class, 'list'])->name('list');
        Route::get('create', [QuotationController::class, 'create'])->name('create');
        Route::post('store', [QuotationController::class, 'store'])->name('store');
        Route::get('view/{uuid}', [QuotationController::class, 'view'])->name('view');
        Route::get('edit/{uuid}', [QuotationController::class, 'edit'])->name('edit');
        Route::put('update/{uuid}', [QuotationController::class, 'update'])->name('update');
        Route::post('delete/{uuid}', [QuotationController::class, 'delete'])->name('delete');
        Route::get('{uuid}/download', [QuotationController::class, 'downloadPdf'])->name('download');
    });

    // ==================== GENERAL TRANSACTIONS ROUTES ====================
   Route::prefix('general-transactions')->name('general-transactions.')->group(function () {
    Route::get('/', [GeneralTransactionController::class, 'index'])->name('index');
    Route::get('/entries', [GeneralTransactionController::class, 'generalEntriesList'])->name('entries-list');
    Route::get('/general-entry', [GeneralTransactionController::class, 'generalEntry'])->name('general-entry');
    Route::post('/general-entry/store', [GeneralTransactionController::class, 'storeGeneralEntry'])->name('general-entry.store');
    
    // Approve General Entry Route (Admin only)
    Route::post('/approve/{id}', [GeneralTransactionController::class, 'approveEntry'])->name('approve');
    
    Route::get('/customer-to-vendor', [GeneralTransactionController::class, 'customerToVendorForm'])->name('customer-to-vendor');
    Route::post('/customer-to-vendor', [GeneralTransactionController::class, 'customerToVendorTransfer'])->name('customer-to-vendor.store');
    
    Route::get('/bank-to-bank', [GeneralTransactionController::class, 'bankToBankForm'])->name('bank-to-bank');
    Route::post('/bank-to-bank', [GeneralTransactionController::class, 'bankToBankTransfer'])->name('bank-to-bank.store');
    
    Route::get('/bank-withdraw', [GeneralTransactionController::class, 'bankWithdrawForm'])->name('bank-withdraw');
    Route::post('/bank-withdraw', [GeneralTransactionController::class, 'bankWithdraw'])->name('bank-withdraw.store');
    
    Route::get('/bank-deposit', [GeneralTransactionController::class, 'bankDepositForm'])->name('bank-deposit');
    Route::post('/bank-deposit', [GeneralTransactionController::class, 'bankDeposit'])->name('bank-deposit.store');
    
    Route::get('/get-accounts', [GeneralTransactionController::class, 'getAccounts'])->name('get-accounts');
});
    // ==================== ACCESS CONTROL ROUTES ====================
    Route::prefix('access-control')->name('access-control.')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::get('permissions', [UserController::class, 'index'])->name('permissions.index');
        Route::post('users/store', [UserController::class, 'store'])->name('users.store');
        Route::put('users/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/destroy/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('permissions/assign/{id}', [UserController::class, 'assignPermissions'])->name('permissions.assign');
        Route::post('permissions/update/{id}', [UserController::class, 'updatePermissions'])->name('permissions.update');
    });
});

// ==================== ADMIN PURCHASE ROUTES ====================
Route::prefix('admin/purchase')->group(function () {
    Route::get('bills/create-general', [VendorBillController::class, 'generalCreate'])->name('vendors.bills.general_create');
    Route::get('bills/create-general-2', [VendorBillController::class, 'generalCreate2'])->name('vendors.bills.general_create_2');
    Route::get('bills/{uuid}/edit-general-2', [VendorBillController::class, 'generalEdit2'])->name('vendors.bills.general_edit_2');
    Route::get('bills/{uuid}/show-general-2', [VendorBillController::class, 'generalShow2'])->name('vendors.bills.general_show_2');
    Route::get('bills/{uuid}/pdf-general-2', [VendorBillController::class, 'generalPdf2'])->name('vendors.bills.general_pdf_2');
    Route::post('bills/store-general', [VendorBillController::class, 'generalStore'])->name('vendors.bills.general_store');
    Route::post('bills/{uuid}/update-general-2', [VendorBillController::class, 'generalUpdate2'])->name('vendors.bills.general_update_2');
});

require __DIR__ . '/auth.php';

// ==================== UTILITY ROUTES ====================
Route::get('migrate-fresh', function () {
    try {
        Artisan::call('migrate:fresh --seed');
        return 'Migrate Fresh Success';
    } catch (\Exception $e) {
        return 'Migrate Fresh Failed: ' . $e->getMessage();
    }
});

Route::get('/debug-transactions', function() {
    $data = [];
    
    $data['vendor_transactions'] = [
        'count' => App\Models\VendorTransaction::count(),
        'sample' => App\Models\VendorTransaction::with('vendor')->limit(5)->get(),
        'types' => App\Models\VendorTransaction::select('type', DB::raw('count(*) as total'))
                    ->groupBy('type')
                    ->get()
    ];
    
    if (Illuminate\Support\Facades\Schema::hasTable('daybooks')) {
        $data['daybooks'] = [
            'count' => DB::table('daybooks')->count(),
            'sample' => DB::table('daybooks')->limit(5)->get(),
            'types' => DB::table('daybooks')->select('type', DB::raw('count(*) as total'))
                        ->groupBy('type')
                        ->get()
        ];
    }
    
    if (Illuminate\Support\Facades\Schema::hasTable('customer_transactions')) {
        $data['customer_transactions'] = [
            'count' => DB::table('customer_transactions')->count(),
            'sample' => DB::table('customer_transactions')->limit(5)->get(),
            'types' => DB::table('customer_transactions')->select('type', DB::raw('count(*) as total'))
                        ->groupBy('type')
                        ->get()
        ];
    }
    
    if (Illuminate\Support\Facades\Schema::hasTable('expenses')) {
        $data['expenses'] = [
            'count' => DB::table('expenses')->count(),
            'sample' => DB::table('expenses')->limit(5)->get()
        ];
    }
    
    if (Illuminate\Support\Facades\Schema::hasTable('cash_transactions')) {
        $data['cash_transactions'] = [
            'count' => DB::table('cash_transactions')->count(),
            'sample' => DB::table('cash_transactions')->limit(5)->get()
        ];
    }
    
    if (Illuminate\Support\Facades\Schema::hasTable('bank_transactions')) {
        $data['bank_transactions'] = [
            'count' => DB::table('bank_transactions')->count(),
            'sample' => DB::table('bank_transactions')->limit(5)->get()
        ];
    }
    
    return response()->json($data);
})->name('debug.transactions');

Route::post('test-bill-store', function(Request $request) {
    \Log::info('TEST STORE HIT', $request->all());
    return response()->json([
        'success' => true,
        'received' => $request->all()
    ]);
});