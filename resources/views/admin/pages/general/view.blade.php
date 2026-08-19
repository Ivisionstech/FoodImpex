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

        @php
            // Check if this entry has a batch_id (grouped entry)
            $batchId = $entry->batch_id ?? null;
            $isGrouped = $batchId && \App\Models\Daybook::where('batch_id', $batchId)->count() > 1;
            
            if ($isGrouped) {
                $groupEntries = \App\Models\Daybook::where('batch_id', $batchId)
                    ->orderBy('id')
                    ->get();
                $totalGroupAmount = $groupEntries->sum('amount');
                $entryCount = $groupEntries->count();
            }
        @endphp

        @if($isGrouped)
            <!-- Grouped Entry Alert -->
            <div class="alert alert-primary alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-layer fs-4 me-2"></i>
                    <div>
                        <strong>Batch Entry!</strong> This entry contains <strong>{{ $entryCount }}</strong> transactions.
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2">
                            Total: PKR {{ number_format($totalGroupAmount, 2) }}
                        </span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="row g-4">
            @if(!$isGrouped)
                <!-- SINGLE ENTRY - Original Layout -->
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

            @else
                <!-- GROUPED ENTRY - Show All Entries in Batch -->
                
                <!-- Left Column: Batch Summary -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="mb-0">
                                <i class="bx bx-layer me-2 text-primary"></i>
                                Batch Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Batch ID</div>
                                <div class="fw-bold small">{{ substr($batchId, 0, 16) }}...</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Transaction Date</div>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-M-Y') }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Total Entries</div>
                                <div class="fw-bold">
                                    <span class="badge bg-primary">{{ $entryCount }}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Total Amount</div>
                                <div class="fw-bold text-info">PKR {{ number_format($totalGroupAmount, 2) }}</div>
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
                            
                            @if($entryCount > 1)
                                <hr>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="collapse" data-bs-target="#batchDetails">
                                        <i class="bx bx-chevron-down me-1"></i> Toggle All Details
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: All Entries in Batch -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom">
                            <h6 class="mb-0">
                                <i class="bx bx-list-ul me-2 text-primary"></i>
                                All Entries in Batch ({{ $entryCount }})
                            </h6>
                        </div>
                        <div class="card-body p-0" id="batchDetails">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Account</th>
                                            <th width="15%">Type</th>
                                            <th width="15%">Amount</th>
                                            <th width="45%">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupEntries as $index => $item)
                                            @php
                                                $isItemDebit = $item->debit_type && $item->debit_id;
                                                $isItemCredit = $item->credit_type && $item->credit_id;
                                                
                                                $itemAccountName = '';
                                                $itemAccountType = '';
                                                $itemIcon = '';
                                                
                                                if ($isItemDebit) {
                                                    $itemAccountType = $item->debit_type;
                                                    $itemIcon = 'bx-arrow-down text-danger';
                                                    
                                                    if ($itemAccountType == 'customer') {
                                                        $customer = \App\Models\Customer::find($item->debit_id);
                                                        $itemAccountName = $customer ? $customer->name : 'Customer #' . $item->debit_id;
                                                    } elseif ($itemAccountType == 'vendor') {
                                                        $vendor = \App\Models\Vendor::find($item->debit_id);
                                                        $itemAccountName = $vendor ? $vendor->company_name : 'Vendor #' . $item->debit_id;
                                                    } elseif ($itemAccountType == 'bank') {
                                                        $bank = \App\Models\Bank::find($item->debit_id);
                                                        $itemAccountName = $bank ? $bank->name : 'Bank #' . $item->debit_id;
                                                    } elseif ($itemAccountType == 'cash') {
                                                        $itemAccountName = 'Cash Account';
                                                    } elseif ($itemAccountType == 'expense') {
                                                        $expense = \App\Models\Expense::find($item->debit_id);
                                                        $itemAccountName = $expense ? $expense->name : 'Expense #' . $item->debit_id;
                                                    }
                                                } elseif ($isItemCredit) {
                                                    $itemAccountType = $item->credit_type;
                                                    $itemIcon = 'bx-arrow-up text-success';
                                                    
                                                    if ($itemAccountType == 'customer') {
                                                        $customer = \App\Models\Customer::find($item->credit_id);
                                                        $itemAccountName = $customer ? $customer->name : 'Customer #' . $item->credit_id;
                                                    } elseif ($itemAccountType == 'vendor') {
                                                        $vendor = \App\Models\Vendor::find($item->credit_id);
                                                        $itemAccountName = $vendor ? $vendor->company_name : 'Vendor #' . $item->credit_id;
                                                    } elseif ($itemAccountType == 'bank') {
                                                        $bank = \App\Models\Bank::find($item->credit_id);
                                                        $itemAccountName = $bank ? $bank->name : 'Bank #' . $item->credit_id;
                                                    } elseif ($itemAccountType == 'cash') {
                                                        $itemAccountName = 'Cash Account';
                                                    } elseif ($itemAccountType == 'expense') {
                                                        $expense = \App\Models\Expense::find($item->credit_id);
                                                        $itemAccountName = $expense ? $expense->name : 'Expense #' . $item->credit_id;
                                                    }
                                                }
                                                
                                                $itemAmountClass = $isItemDebit ? 'text-danger' : 'text-success';
                                                $itemTypeLabel = $isItemDebit ? 'Debit' : 'Credit';
                                            @endphp
                                            <tr>
                                                <td class="text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bx {{ $itemIcon }}"></i>
                                                        <div>
                                                            <div class="fw-semibold small">{{ $itemAccountType }}</div>
                                                            <div class="small text-muted">{{ \Str::limit($itemAccountName, 25) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $isItemDebit ? 'danger' : 'success' }} bg-opacity-10 text-{{ $isItemDebit ? 'danger' : 'success' }}">
                                                        {{ $itemTypeLabel }}
                                                    </span>
                                                </td>
                                                <td class="fw-bold {{ $itemAmountClass }}">
                                                    PKR {{ number_format($item->amount, 2) }}
                                                </td>
                                                <td>
                                                    <div class="small" title="{{ $item->description }}">
                                                        {{ \Str::limit($item->description ?? 'N/A', 50) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-active">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total:</td>
                                            <td class="fw-bold text-info">PKR {{ number_format($totalGroupAmount, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Add View All Entries Button if Grouped -->
        @if($isGrouped && $entryCount > 1)
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="{{ route('general-transactions.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to List
                    </a>
                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="collapse" data-bs-target="#batchDetails">
                        <i class="bx bx-list-ul me-1"></i> View All Entries
                    </button>
                </div>
            </div>
        @endif
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
    
    .table > :not(caption) > * > * {
        padding: 10px 8px;
        vertical-align: middle;
    }
    
    .badge.bg-success.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; color: #28a745 !important; }
    .badge.bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }
    .badge.bg-primary.bg-opacity-10 { background-color: rgba(105, 108, 255, 0.1) !important; color: #696cff !important; }
    .badge.bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107 !important; }
    
    .alert-primary {
        background-color: rgba(105, 108, 255, 0.08) !important;
        border-color: rgba(105, 108, 255, 0.2) !important;
        color: #696cff !important;
    }
    
    /* Animation for collapse */
    .collapsing {
        transition: height 0.35s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-expand batch details if there are more than 3 entries
        var entryCount = {{ $entryCount ?? 1 }};
        if (entryCount > 3) {
            $('#batchDetails').addClass('show');
        }
    });
</script>
@endpush