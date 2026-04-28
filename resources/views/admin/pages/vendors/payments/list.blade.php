@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Purchase /</span> Payments & Entries History
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('general-transactions.general-entry') }}" class="btn btn-info">
                    <i class="bx bx-transfer-alt me-1"></i> New General Entry
                </a>
                <a href="{{ route('vendors.payments.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Send Payment
                </a>
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
            $isAdmin = auth()->user()->role == 'admin';
            
            $allTransactions = collect([]);
            
            foreach ($payments as $payment) {
                $description = $payment->description ?? 'Payment to Vendor';
                if (strpos($description, 'Payment to') === false && strpos($description, 'Payment sent') === false) {
                    $description = 'Payment to ' . ($payment->vendor->company_name ?? 'Vendor');
                }
                
                $allTransactions->push([
                    'uuid' => $payment->uuid,
                    'id' => $payment->id,
                    'date' => $payment->date,
                    'reference' => $payment->vendor->company_name ?? 'Unknown Vendor',
                    'amount' => $payment->amount,
                    'type' => 'payment',
                    'type_badge' => 'success',
                    'type_label' => 'Vendor Payment',
                    'method' => ucfirst($payment->send_via ?? 'Cash'),
                    'approval_status' => $payment->approval_status ?? 'pending',
                    'is_payment' => true,
                ]);
            }
            
            foreach ($generalEntries as $entry) {
                $allTransactions->push([
                    'uuid' => $entry->uuid ?? ('entry_'.$entry->id),
                    'id' => $entry->id,
                    'date' => $entry->date ?? $entry->transaction_date ?? now(),
                    'reference' => $entry->reference ?? ($entry->description ? \Str::limit($entry->description, 30) : 'System'),
                    'amount' => $entry->amount ?? 0,
                    'type' => $entry->type ?? 'general',
                    'type_badge' => $entry->type_badge ?? 'info',
                    'type_label' => $entry->type_label ?? 'General Entry',
                    'method' => $entry->method ?? 'Transfer',
                    'approval_status' => 'approved',
                    'is_payment' => false,
                ]);
            }
            
            $allTransactions = $allTransactions->sortByDesc('date');
            
            // Pagination
            $currentPage = request()->get('page', 1);
            $perPage = 15;
            $currentItems = $allTransactions->slice(($currentPage - 1) * $perPage, $perPage);
            $totalItems = $allTransactions->count();
            $lastPage = ceil($totalItems / $perPage);
        @endphp

       
        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route(Route::currentRouteName()) }}" method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Transaction Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="payments" {{ request('type') == 'payments' ? 'selected' : '' }}>Vendor Payments</option>
                                <option value="general_entries" {{ request('type') == 'general_entries' ? 'selected' : '' }}>General Entries</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('from_date') || request()->has('to_date') || request()->has('type'))
                                    <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-refresh me-1"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bx bx-list-ul me-2 text-primary"></i>
                    All Transactions
                </h5>
                <div class="text-muted small">
                    Total: {{ $totalItems }} transactions
                </div>
            </div>

            {{-- CSS Grid Layout for Transactions --}}
            <div class="table-responsive" style="overflow-x: auto;">
                <div class="p-3" style="min-width: 900px;">
                    {{-- Header Row --}}
                    <div class="transaction-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                        <div class="fw-bold text-muted">Date</div>
                        <div class="fw-bold text-muted">Reference</div>
                        <div class="fw-bold text-muted">Amount</div>
                        <div class="fw-bold text-muted">Type</div>
                        <div class="fw-bold text-muted">Method</div>
                        <div class="fw-bold text-muted">Status</div>
                        <div class="fw-bold text-muted text-center">Actions</div>
                    </div>

                    {{-- Transaction Rows --}}
                    @forelse ($currentItems as $transaction)
                        <div class="transaction-grid transaction-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="transaction-row-{{ $transaction['uuid'] }}">
                            {{-- Date Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Date</div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($transaction['date'])->format('d-M-Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($transaction['date'])->format('h:i A') }}</div>
                            </div>
                            
                            {{-- Reference Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Reference</div>
                                <span class="badge bg-label-secondary" style="white-space: normal; word-break: break-word; display: inline-block; max-width: 100%;">
                                    {{ \Str::limit($transaction['reference'], 35) }}
                                </span>
                            </div>
                            
                            {{-- Amount Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Amount</div>
                                <span class="fw-bold {{ $transaction['is_payment'] ? 'text-success' : 'text-primary' }}">
                                    PKR {{ number_format($transaction['amount'], 2) }}
                                </span>
                            </div>
                            
                            {{-- Type Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Type</div>
                                <span class="badge rounded-pill" style="background-color: rgba({{ $transaction['type_badge'] == 'success' ? '40, 167, 69' : ($transaction['type_badge'] == 'info' ? '13, 202, 240' : '105, 108, 255') }}, 0.1) !important; color: {{ $transaction['type_badge'] == 'success' ? '#28a745' : ($transaction['type_badge'] == 'info' ? '#0dcaf0' : '#696cff') }} !important; padding: 6px 12px;">
                                    {{ $transaction['type_label'] }}
                                </span>
                            </div>
                            
                            {{-- Method Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Method</div>
                                <span class="badge bg-label-secondary rounded-pill">
                                    <i class="bx bx-{{ $transaction['is_payment'] ? 'bank' : 'transfer' }} me-1"></i>
                                    {{ $transaction['method'] }}
                                </span>
                            </div>
                            
                            {{-- Status Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Status</div>
                                @if($transaction['approval_status'] == 'approved')
                                    <span class="badge bg-success" style="background-color: #28a745 !important; padding: 6px 12px; border-radius: 20px;">
                                        <i class="bx bx-check-circle me-1"></i> Approved
                                    </span>
                                @else
                                    @if($isAdmin && $transaction['is_payment'])
                                        <button type="button" 
                                            class="btn btn-sm approve-payment-btn"
                                            style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; border-radius: 20px; border: none; font-size: 0.75rem;"
                                            data-payment-uuid="{{ $transaction['uuid'] }}"
                                            data-payment-id="{{ $transaction['id'] }}">
                                            <i class="bx bx-time me-1"></i> Pending (Click)
                                        </button>
                                    @else
                                        <span class="badge bg-warning" style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; border-radius: 20px;">
                                            <i class="bx bx-time me-1"></i> Pending
                                        </span>
                                    @endif
                                @endif
                            </div>
                            
                            {{-- Actions Column --}}
                            <div class="text-center">
                                <div class="d-md-none fw-bold text-muted small">Actions</div>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                    </button>
                                    <div class="dropdown-menu shadow-sm border-0">
                                        @if($transaction['is_payment'])
                                            <a class="dropdown-item py-2" href="{{ route('vendors.payments.show', $transaction['uuid']) }}">
                                                <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                            </a>
                                            <a class="dropdown-item py-2" href="{{ route('vendors.payments.edit', $transaction['uuid']) }}">
                                                <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Payment
                                            </a>
                                            @if($isAdmin && $transaction['approval_status'] == 'pending')
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item py-2 text-success approve-payment-btn" 
                                                        data-payment-uuid="{{ $transaction['uuid'] }}" 
                                                        data-payment-id="{{ $transaction['id'] }}">
                                                    <i class="bx bx-check-circle me-2"></i> Approve Payment
                                                </button>
                                            @endif
                                            @if($isAdmin)
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('vendors.payments.delete', $transaction['uuid']) }}" method="POST" 
                                                      onsubmit="return confirm('Are you sure? This will refund the amount.')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item py-2 text-danger">
                                                        <i class="bx bx-trash me-2"></i> Delete Payment
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a class="dropdown-item py-2" href="#" onclick="viewEntry('{{ $transaction['uuid'] }}', {{ $transaction['id'] }})">
                                                <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                            </a>
                                            <a class="dropdown-item py-2" href="#" onclick="printEntry('{{ $transaction['uuid'] }}')">
                                                <i class="bx bx-printer me-2 text-secondary"></i> Print
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-receipt fs-1 mb-2 d-block text-muted"></i>
                            <div class="text-muted">No transactions found.</div>
                            @if(request('from_date') || request('to_date') || request('type'))
                                <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-primary mt-3">
                                    <i class="bx bx-refresh me-1"></i> Clear Filters
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            @if($totalItems > $perPage)
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ ($currentPage - 1) * $perPage + 1 }} to {{ min($currentPage * $perPage, $totalItems) }} of {{ $totalItems }} entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                {{-- Previous Page Link --}}
                                @if($currentPage > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                                            <i class="bx bx-chevron-left me-1"></i> Previous
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bx bx-chevron-left me-1"></i> Previous</span>
                                    </li>
                                @endif

                                {{-- Page Number Links --}}
                                @php
                                    $start = max(1, $currentPage - 2);
                                    $end = min($lastPage, $currentPage + 2);
                                    
                                    if ($start > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . request()->fullUrlWithQuery(['page' => 1]) . '">1</a></li>';
                                        if ($start > 2) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                    }
                                    
                                    for ($i = $start; $i <= $end; $i++) {
                                        if ($i == $currentPage) {
                                            echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                                        } else {
                                            echo '<li class="page-item"><a class="page-link" href="' . request()->fullUrlWithQuery(['page' => $i]) . '">' . $i . '</a></li>';
                                        }
                                    }
                                    
                                    if ($end < $lastPage) {
                                        if ($end < $lastPage - 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="' . request()->fullUrlWithQuery(['page' => $lastPage]) . '">' . $lastPage . '</a></li>';
                                    }
                                @endphp

                                {{-- Next Page Link --}}
                                @if($currentPage < $lastPage)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                                            Next <i class="bx bx-chevron-right ms-1"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class->page-link">Next <i class="bx bx-chevron-right ms-1"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- View Modal for General Entries -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="bx bx-info-circle me-2 text-primary"></i>
                        Entry Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading entry details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* CSS Grid Layout */
    .transaction-grid {
        display: grid;
        grid-template-columns: 120px minmax(160px, 1fr) 130px 120px 100px 130px 80px;
        gap: 12px;
        align-items: center;
        width: 100%;
    }
    
    /* Reference column - allow wrapping */
    .transaction-grid > div:nth-child(2) {
        word-break: break-word;
        white-space: normal;
    }
    
    /* Amount column - keep on one line */
    .transaction-grid > div:nth-child(3) {
        white-space: nowrap;
    }
    
    @media (max-width: 1200px) {
        .transaction-grid {
            grid-template-columns: 110px minmax(140px, 1fr) 120px 110px 90px 120px 70px;
            gap: 10px;
        }
    }
    
    @media (max-width: 992px) {
        .transaction-grid {
            grid-template-columns: 100px 1fr 110px 100px 80px 110px 65px;
            gap: 8px;
        }
        .transaction-grid > div:nth-child(2) span {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 768px) {
        .transaction-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .transaction-row {
            margin-bottom: 15px;
        }
    }
    
    .transaction-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .transaction-row:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
        border-color: #696cff !important;
    }
    
    .dropdown-menu {
        border-radius: 12px;
        animation: fadeInDown 0.2s ease;
    }
    
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
        border-radius: 8px;
        margin: 2px 8px;
        width: calc(100% - 16px);
    }
    
    .dropdown-item:hover {
        transform: translateX(5px);
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #696cff, #5a5cbf);
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    }
    
    .approve-payment-btn:hover {
        transform: scale(1.02);
        filter: brightness(0.95);
    }
    
    .card {
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background: transparent;
        padding: 1.25rem 1.5rem;
    }
    
    /* Badge styles */
    .badge.bg-label-secondary {
        background-color: rgba(108, 117, 125, 0.1) !important;
        color: #6c757d !important;
        padding: 6px 12px;
        border-radius: 20px;
        display: inline-block;
        max-width: 100%;
        word-break: break-word;
        white-space: normal;
    }
    
    /* Header row styles */
    .header-row {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    
    .header-row .fw-bold {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Pagination Styles */
    .pagination {
        gap: 5px;
        margin-bottom: 0;
    }
    
    .pagination .page-item .page-link {
        border-radius: 8px !important;
        color: #696cff;
        border: none;
        padding: 8px 14px;
        transition: all 0.2s ease;
        background: transparent;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #696cff;
        color: white;
    }
    
    .pagination .page-item .page-link:hover {
        transform: translateY(-2px);
        background-color: rgba(105, 108, 255, 0.1);
    }
    
    .pagination .page-item.disabled .page-link {
        color: #a8a8a8;
        pointer-events: none;
    }
    
    /* Horizontal scroll */
    .table-responsive {
        overflow-x: auto;
        width: 100%;
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    if (isAdmin) {
        function approvePayment(uuid, id) {
            Swal.fire({
                title: 'Approve Payment #' + id + '?',
                text: "This payment will be marked as approved and vendor balance will be updated.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '/vendors/payments/approve/' + uuid,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) { 
                            Swal.fire({ title: 'Approved!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
                        },
                        error: function(xhr) { 
                            let msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({ title: 'Error!', text: msg, icon: 'error' }); 
                        }
                    });
                }
            });
        }
        
        $(document).on('click', '.approve-payment-btn', function(e) {
            e.preventDefault();
            let uuid = $(this).data('payment-uuid');
            let id = $(this).data('payment-id');
            approvePayment(uuid, id);
        });
    }
    
    $('#from_date, #to_date, select[name="type"]').on('change', function() {
        $('#filterForm').submit();
    });
});

function viewEntry(uuid, id) {
    $('#viewModal').modal('show');
    $('#modalContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading entry details...</p>
        </div>
    `);
    
    if (uuid && uuid.toString().startsWith('daybook_')) {
        setTimeout(function() {
            $('#modalContent').html(`
                <div class="p-3">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bx bx-transfer-alt fs-2 me-2 text-info"></i>
                        <h6 class="mb-0">General Entry Details</h6>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Entry ID:</strong> 
                            <span class="badge bg-label-info">#${id}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date:</strong> ${new Date().toLocaleDateString()}
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Description:</strong> 
                            <p class="mt-1 mb-0">General transaction entry</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted mt-2">
                                <i class="bx bx-info-circle me-1"></i>
                                This is a general entry transaction. Full details available in General Transactions section.
                            </p>
                        </div>
                    </div>
                </div>
            `);
        }, 500);
    }
}

function printEntry(uuid) {
    alert('Print functionality will be implemented for entry: ' + uuid);
}
</script>
@endpush