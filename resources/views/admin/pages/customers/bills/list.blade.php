@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span> Customer Bills
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('new.bills.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> New Sales Invoice
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
                            <label class="form-label fw-semibold">Approval Status</label>
                            <select name="approval_status" class="form-select">
                                <option value="">All Bills</option>
                                <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>✓ Approved</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('from_date') || request()->has('to_date') || request()->has('approval_status'))
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
                    <i class="bx bx-receipt me-2 text-primary"></i>
                    Customer Invoices
                </h5>
            </div>

            {{-- CSS Grid Layout for Invoices --}}
            <div class="p-3">
                {{-- Header Row --}}
                <div class="invoice-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                    <div class="fw-bold text-muted">Date</div>
                    <div class="fw-bold text-muted">Invoice No</div>
                    <div class="fw-bold text-muted">Customer</div>
                    <div class="fw-bold text-muted">Total Amount</div>
                    <div class="fw-bold text-muted">Status</div>
                    <div class="fw-bold text-muted text-center">Actions</div>
                </div>

                {{-- Invoice Rows --}}
                @forelse ($bills as $bill)
                    <div class="invoice-grid invoice-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="bill-row-{{ $bill->uuid }}">
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Date</div>
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d-M-Y') }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($bill->bill_date)->format('h:i A') }}</div>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Invoice No</div>
                            <span class="fw-semibold text-dark">#{{ $bill->id }}</span>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Customer</div>
                            @if ($bill->customer)
                                <a href="{{ route('customers.view', $bill->customer->uuid) }}" class="text-decoration-none fw-medium">
                                    {{ $bill->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">{{ $bill->customer_name ?? 'Walk-in Customer' }}</span>
                            @endif
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Total Amount</div>
                            <span class="fw-bold text-primary">PKR {{ number_format($bill->total_amount, 2) }}</span>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Status</div>
                            @if($bill->approval_status == 'approved')
                                <span class="badge bg-success" style="background-color: #28a745 !important; padding: 6px 12px; font-size: 0.75rem; border-radius: 20px;">
                                    <i class="bx bx-check-circle me-1"></i> Approved
                                </span>
                            @elseif($bill->approval_status == 'rejected')
                                <span class="badge bg-danger" style="background-color: #dc3545 !important; padding: 6px 12px; font-size: 0.75rem; border-radius: 20px;">
                                    <i class="bx bx-x-circle me-1"></i> Rejected
                                </span>
                            @else
                                @if($isAdmin)
                                    <button type="button" 
                                        class="btn btn-sm approve-action-btn"
                                        style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; border-radius: 20px; border: none; font-size: 0.75rem;"
                                        data-bill-uuid="{{ $bill->uuid }}"
                                        data-bill-id="{{ $bill->id }}">
                                        <i class="bx bx-time me-1"></i> Pending (Click)
                                    </button>
                                @else
                                    <span class="badge bg-warning" style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; font-size: 0.75rem; border-radius: 20px;">
                                        <i class="bx bx-time me-1"></i> Pending
                                    </span>
                                @endif
                            @endif
                        </div>
                        
                        <div class="text-center">
                            <div class="d-md-none fw-bold text-muted small">Actions</div>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                </button>
                                <div class="dropdown-menu shadow-sm border-0">
                                    @if(($bill->type ?? null) === 'new bill')
                                        <a class="dropdown-item py-2" target="_blank" href="{{ route('new.bills.show', $bill->uuid) }}">
                                            <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                        </a>
                                    @else
                                        <a class="dropdown-item py-2" target="_blank" href="{{ route('customers.bills.show', $bill->uuid) }}">
                                            <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                        </a>
                                    @endif
                                    
                                    @if(($bill->type ?? null) === 'new bill')
                                        <a class="dropdown-item py-2" href="{{ route('new.bills.edit', $bill->uuid) }}">
                                            <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Invoice
                                        </a>
                                    @else
                                        <a class="dropdown-item py-2" href="{{ route('bills.edit', $bill->uuid) }}">
                                            <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Invoice
                                        </a>
                                    @endif
                                    
                                    <a class="dropdown-item py-2" target="_blank" 
                                       href="{{ ($bill->type ?? null) === 'new bill' ? route('customers.bills.download.new', $bill->uuid) : route('customers.bills.download', $bill->uuid) }}">
                                        <i class="bx bx-download me-2 text-secondary"></i> Download PDF
                                    </a>

                                    @if($isAdmin && $bill->approval_status == 'pending')
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item py-2 text-success approve-btn" 
                                                data-bill-uuid="{{ $bill->uuid }}" 
                                                data-bill-id="{{ $bill->id }}">
                                            <i class="bx bx-check-circle me-2"></i> Approve Invoice
                                        </button>
                                        <button type="button" class="dropdown-item py-2 text-danger reject-btn" 
                                                data-bill-uuid="{{ $bill->uuid }}" 
                                                data-bill-id="{{ $bill->id }}">
                                            <i class="bx bx-x-circle me-2"></i> Reject Invoice
                                        </button>
                                    @endif

                                    @if($isAdmin)
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('customers.bills.delete', $bill->uuid) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure? Deleting this invoice will reverse stock.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bx bx-trash me-2"></i> Delete Invoice
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bx bx-receipt fs-1 mb-2 d-block text-muted"></i>
                        <div class="text-muted">No invoices found.</div>
                    </div>
                @endforelse
            </div>

            <div class="card-footer bg-transparent">
                @if(method_exists($bills, 'links'))
                    <div class="d-flex justify-content-center">
                        {{ $bills->appends(request()->input())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CSS Grid Layout */
    .invoice-grid {
        display: grid;
        grid-template-columns: 140px 80px 1fr 160px 180px 100px;
        gap: 16px;
        align-items: center;
        width: 100%;
    }
    
    .invoice-grid > div:nth-child(3) {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    .invoice-grid > div:nth-child(3) a,
    .invoice-grid > div:nth-child(3) span {
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    
    @media (max-width: 1200px) {
        .invoice-grid {
            grid-template-columns: 130px 70px 1fr 140px 160px 80px;
            gap: 12px;
        }
    }
    
    @media (max-width: 992px) {
        .invoice-grid {
            grid-template-columns: 120px 70px 1fr 130px 150px 70px;
            gap: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .invoice-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .invoice-row {
            margin-bottom: 15px;
        }
    }
    
    .invoice-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .invoice-row:hover {
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
    
    .pagination {
        gap: 5px;
    }
    
    .pagination .page-item .page-link {
        border-radius: 8px !important;
        color: #696cff;
        border: none;
        padding: 8px 14px;
        transition: all 0.2s ease;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #696cff;
        color: white;
    }
    
    .pagination .page-item .page-link:hover {
        transform: translateY(-2px);
        background-color: rgba(105, 108, 255, 0.1);
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #696cff, #5a5cbf);
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    }
    
    .approve-action-btn:hover {
        transform: scale(1.02);
        filter: brightness(0.95);
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    if (isAdmin) {
        function approveBill(uuid, id) {
            Swal.fire({
                title: 'Approve Invoice #' + id + '?',
                text: "This invoice will be marked as approved.",
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
                        url: '{{ url("/customers/bills/approve") }}/' + uuid,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { 
                            Swal.fire({ title: 'Approved!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
                        },
                        error: function() { 
                            Swal.fire({ title: 'Error!', text: 'Something went wrong', icon: 'error' }); 
                        }
                    });
                }
            });
        }
        
        function rejectBill(uuid, id) {
            Swal.fire({
                title: 'Reject Invoice #' + id + '?',
                text: "This will permanently delete the invoice.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '{{ url("/customers/bills/delete") }}/' + uuid,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { 
                            Swal.fire({ title: 'Rejected!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
                        },
                        error: function() { 
                            Swal.fire({ title: 'Error!', text: 'Something went wrong', icon: 'error' }); 
                        }
                    });
                }
            });
        }
        
        $(document).on('click', '.approve-action-btn', function(e) {
            e.preventDefault();
            let uuid = $(this).data('bill-uuid');
            let id = $(this).data('bill-id');
            Swal.fire({
                title: 'Invoice #' + id,
                text: "What would you like to do?",
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Approve',
                denyButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                denyButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) approveBill(uuid, id);
                else if (result.isDenied) rejectBill(uuid, id);
            });
        });
        
        $(document).on('click', '.approve-btn', function(e) {
            e.preventDefault();
            approveBill($(this).data('bill-uuid'), $(this).data('bill-id'));
        });
        
        $(document).on('click', '.reject-btn', function(e) {
            e.preventDefault();
            rejectBill($(this).data('bill-uuid'), $(this).data('bill-id'));
        });
    }
});
</script>
@endpush