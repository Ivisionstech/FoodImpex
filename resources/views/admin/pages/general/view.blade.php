@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard / General Transactions /</span>
                Entry Details
            </h4>
            <div>
                <a href="{{ route('general-transactions.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
                @if($entry->approval_status == 'pending')
                    <a href="{{ route('general-transactions.edit', $entry->id) }}" class="btn btn-warning ms-2">
                        <i class="bx bx-edit me-1"></i> Edit Entry
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-1"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Entry Details -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">
                            <i class="bx bx-info-circle me-2 text-primary"></i>
                            Entry Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Entry ID</div>
                            <div class="fw-bold">#{{ $entry->id }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Transaction Date</div>
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-M-Y h:i A') }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Description</div>
                            <div class="fw-bold text-end" style="max-width: 60%;">{{ $entry->description ?? 'N/A' }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Amount</div>
                            <div class="fw-bold {{ $entry->amount < 0 ? 'text-danger' : 'text-success' }}">
                                PKR {{ number_format($entry->amount, 2) }}
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Approval Status</div>
                            <div>
                                @if($entry->approval_status == 'approved')
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle me-1"></i> Approved
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bx bx-time me-1"></i> Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold text-muted">Created At</div>
                            <div class="small text-muted">{{ $entry->created_at->format('d-M-Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="col-md-6">
                @php
                    $isDebit = $entry->debit_type && $entry->debit_id;
                    $isCredit = $entry->credit_type && $entry->credit_id;
                    
                    $accountType = '';
                    $accountName = '';
                    $accountIcon = '';
                    $accountBalance = 0;
                    $accountDetails = [];
                    
                    if ($isDebit) {
                        $accountType = 'Debit Account';
                        $accountSubType = $entry->debit_type;
                        $accountId = $entry->debit_id;
                        $accountIcon = 'bx-arrow-down';
                        
                        if ($accountSubType == 'customer') {
                            $customer = \App\Models\Customer::find($accountId);
                            $accountName = $customer ? $customer->name : 'Customer #' . $accountId;
                            $accountBalance = $customer ? $customer->balance : 0;
                            $accountDetails = [
                                'Email' => $customer->email ?? 'N/A',
                                'Phone' => $customer->phone ?? 'N/A',
                                'Address' => $customer->address ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'vendor') {
                            $vendor = \App\Models\Vendor::find($accountId);
                            $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $accountId;
                            $accountBalance = $vendor ? $vendor->balance : 0;
                            $accountDetails = [
                                'Contact Person' => $vendor->person_name ?? 'N/A',
                                'Email' => $vendor->email ?? 'N/A',
                                'Phone' => $vendor->phone ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'bank') {
                            $bank = \App\Models\Bank::find($accountId);
                            $accountName = $bank ? $bank->name : 'Bank #' . $accountId;
                            $accountBalance = $bank ? $bank->account_balance : 0;
                            $accountDetails = [
                                'Account Number' => $bank->account_number ?? 'N/A',
                                'Bank Name' => $bank->bank_name ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'cash') {
                            $cash = \App\Models\Cash::find($accountId);
                            $accountName = 'Cash Account';
                            $accountBalance = $cash ? $cash->balance : 0;
                            $accountDetails = ['Type' => 'Cash Payment'];
                        }
                    } elseif ($isCredit) {
                        $accountType = 'Credit Account';
                        $accountSubType = $entry->credit_type;
                        $accountId = $entry->credit_id;
                        $accountIcon = 'bx-arrow-up';
                        
                        if ($accountSubType == 'customer') {
                            $customer = \App\Models\Customer::find($accountId);
                            $accountName = $customer ? $customer->name : 'Customer #' . $accountId;
                            $accountBalance = $customer ? $customer->balance : 0;
                            $accountDetails = [
                                'Email' => $customer->email ?? 'N/A',
                                'Phone' => $customer->phone ?? 'N/A',
                                'Address' => $customer->address ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'vendor') {
                            $vendor = \App\Models\Vendor::find($accountId);
                            $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $accountId;
                            $accountBalance = $vendor ? $vendor->balance : 0;
                            $accountDetails = [
                                'Contact Person' => $vendor->person_name ?? 'N/A',
                                'Email' => $vendor->email ?? 'N/A',
                                'Phone' => $vendor->phone ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'bank') {
                            $bank = \App\Models\Bank::find($accountId);
                            $accountName = $bank ? $bank->name : 'Bank #' . $accountId;
                            $accountBalance = $bank ? $bank->account_balance : 0;
                            $accountDetails = [
                                'Account Number' => $bank->account_number ?? 'N/A',
                                'Bank Name' => $bank->bank_name ?? 'N/A'
                            ];
                        } elseif ($accountSubType == 'cash') {
                            $cash = \App\Models\Cash::find($accountId);
                            $accountName = 'Cash Account';
                            $accountBalance = $cash ? $cash->balance : 0;
                            $accountDetails = ['Type' => 'Cash Receipt'];
                        }
                    }
                    
                    // Determine balance color
                    $balanceClass = $accountBalance >= 0 ? 'text-success' : 'text-danger';
                    $balanceLabel = $accountBalance >= 0 ? 'CR' : 'DR';
                @endphp

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">
                            <i class="bx {{ $accountIcon }} me-2 text-primary"></i>
                            Account Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Account Type</div>
                            <div class="fw-bold text-capitalize">{{ $accountType }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Account Name</div>
                            <div class="fw-bold">{{ $accountName }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Sub Type</div>
                            <div class="fw-bold text-capitalize">{{ $accountSubType ?? 'N/A' }}</div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Current Balance</div>
                            <div class="fw-bold {{ $balanceClass }}">
                                PKR {{ number_format(abs($accountBalance), 2) }} 
                            </div>
                        </div>
                        
                        @if(!empty($accountDetails))
                            <hr>
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">Additional Details</h6>
                                @foreach($accountDetails as $label => $value)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">{{ $label }}</span>
                                        <span class="fw-semibold small">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
@endpush