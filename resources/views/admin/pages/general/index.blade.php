@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            General Transactions
        </h4>

        <div class="row">
            <!-- Customer to Vendor Transfer -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-transfer-alt bx-sm"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Customer to Vendor Transfer</h5>
                                <small class="text-muted">Transfer amount from customer to vendor</small>
                            </div>
                        </div>
                        <p class="card-text flex-grow-1">
                            Transfer money from customer account to vendor account. This will reduce customer balance and
                            vendor balance, and create appropriate transactions.
                        </p>
                        <a href="{{ route('general-transactions.customer-to-vendor') }}" class="btn btn-primary">
                            <i class="bx bx-transfer-alt me-1"></i>
                            Start Transfer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bank to Bank Transfer -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-building-house bx-sm"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Bank to Bank Transfer</h5>
                                <small class="text-muted">Transfer money between bank accounts</small>
                            </div>
                        </div>
                        <p class="card-text flex-grow-1">
                            Transfer funds from one bank account to another. This will create bank transactions and daybook
                            entries for both accounts.
                        </p>
                        <a href="{{ route('general-transactions.bank-to-bank') }}" class="btn btn-success">
                            <i class="bx bx-building-house me-1"></i>
                            Start Transfer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bank Withdrawal -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-money bx-sm"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Withdraw from Bank</h5>
                                <small class="text-muted">Withdraw cash from bank account</small>
                            </div>
                        </div>
                        <p class="card-text flex-grow-1">
                            Withdraw money from bank account to cash. This will reduce bank balance, increase cash balance,
                            and create appropriate transactions.
                        </p>
                        <a href="{{ route('general-transactions.bank-withdraw') }}" class="btn btn-warning">
                            <i class="bx bx-money me-1"></i>
                            Withdraw Cash
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bank Deposit -->
            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-wallet bx-sm"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-0">Deposit in Bank</h5>
                                <small class="text-muted">Deposit cash into bank account</small>
                            </div>
                        </div>
                        <p class="card-text flex-grow-1">
                            Deposit cash into bank account. This will reduce cash balance, increase bank balance, and create
                            appropriate transactions.
                        </p>
                        <a href="{{ route('general-transactions.bank-deposit') }}" class="btn btn-info">
                            <i class="bx bx-wallet me-1"></i>
                            Deposit Cash
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Summary -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Total Bank Balance</small>
                                    <h6 class="mb-0 text-primary">PKR {{ number_format($banks->sum('account_balance'), 2) }}
                                    </h6>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Cash Balance</small>
                                    <h6 class="mb-0 text-success">PKR
                                        {{ $cash ? number_format($cash->balance, 2) : '0.00' }}</h6>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Customer Balance</small>
                                    <h6 class="mb-0 text-info">PKR
                                        {{ number_format(\App\Models\Customer::sum('balance'), 2) }}</h6>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Vendor Balance</small>
                                    <h6 class="mb-0 text-warning">PKR
                                        {{ number_format(\App\Models\Vendor::sum('balance'), 2) }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
