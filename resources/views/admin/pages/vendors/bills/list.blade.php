@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span> Vendor Bills
        </h4>

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
                    Purchase Bills
                </h5>
                <a href="{{ route('vendors.bills.general_create_2') }}" class="btn btn-primary">
                    <i class="btn btn-primary "></i> Create New Bill
                </a>
            </div>

            {{-- CSS Grid Layout for Bills - No DataTable interference --}}
            <div class="p-3">
                {{-- Header Row --}}
                <div class="bill-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                    <div class="fw-bold text-muted" style="grid-column: 1 / span 1;">Date</div>
                    <div class="fw-bold text-muted" style="grid-column: 2 / span 1;">Bill No</div>
                    <div class="fw-bold text-muted" style="grid-column: 3 / span 1;">Vendor</div>
                    <div class="fw-bold text-muted" style="grid-column: 4 / span 1;">Total Amount</div>
                    <div class="fw-bold text-muted" style="grid-column: 5 / span 1;">Status</div>
                    <div class="fw-bold text-muted" style="grid-column: 6 / span 1;">Actions</div>
                </div>

                {{-- Bill Rows --}}
                @forelse ($bills as $bill)
                    <div class="bill-grid bill-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="bill-row-{{ $bill->uuid }}">
                        <div style="grid-column: 1 / span 1;">
                            <div class="d-md-none fw-bold text-muted small">Date</div>
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($bill->date)->format('d-M-Y') }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($bill->date)->format('h:i A') }}</div>
                        </div>
                        
                        <div style="grid-column: 2 / span 1;">
                            <div class="d-md-none fw-bold text-muted small">Bill No</div>
                            <span class="fw-semibold text-dark">#{{ $bill->id }}</span>
                        </div>
                        
                        <div style="grid-column: 3 / span 1;">
                            <div class="d-md-none fw-bold text-muted small">Vendor</div>
                            <a href="{{ route('vendors.view', $bill->vendor->uuid) }}" class="text-decoration-none fw-medium">
                                {{ $bill->vendor->company_name ?? 'N/A' }}
                            </a>
                        </div>
                        
                        <div style="grid-column: 4 / span 1;">
                            <div class="d-md-none fw-bold text-muted small">Total Amount</div>
                            <span class="fw-bold text-primary">PKR {{ number_format($bill->total_amount, 2) }}</span>
                        </div>
                        
                        <div style="grid-column: 5 / span 1;">
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
                                        class="btn btn-primary approve-action-btn"
                                        style=" color: #000 !important; padding: 6px 12px; border-radius: 20px; border: none; font-size: 0.75rem;"
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
                        
                        <div style="grid-column: 6 / span 1; text-align: center;">
                            <div class="d-md-none fw-bold text-muted small">Actions</div>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                </button>
                                <div class="dropdown-menu shadow-sm border-0">
                                    @php
                                        $isGeneralCreate2Bill = $bill->billProducts->first() && $bill->billProducts->first()->type === 'product';
                                    @endphp
                                    <a class="dropdown-item py-2" target="_blank" 
                                       href="{{ $isGeneralCreate2Bill ? route('vendors.bills.general_show_2', $bill->uuid) : route('vendors.bills.show', $bill->uuid) }}">
                                        <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                    </a>
                                    <a class="dropdown-item py-2" target="_blank" 
                                       href="{{ $isGeneralCreate2Bill ? route('vendors.bills.general_pdf_2', $bill->uuid) : route('vendors.bills.download', $bill->uuid) }}">
                                        <i class="bx bx-download me-2 text-secondary"></i> Download PDF
                                    </a>
                                    <a class="dropdown-item py-2" 
                                       href="{{ $isGeneralCreate2Bill ? route('vendors.bills.general_edit_2', $bill->uuid) : route('vendors.bills.edit', $bill->uuid) }}">
                                        <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Bill
                                    </a>
                                    @if($isAdmin && $bill->approval_status == 'pending')
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item py-2 text-success approve-btn" 
                                                data-bill-uuid="{{ $bill->uuid }}" 
                                                data-bill-id="{{ $bill->id }}">
                                            <i class="bx bx-check-circle me-2"></i> Approve Bill
                                        </button>
                                        <button type="button" class="dropdown-item py-2 text-danger reject-btn" 
                                                data-bill-uuid="{{ $bill->uuid }}" 
                                                data-bill-id="{{ $bill->id }}">
                                            <i class="bx bx-x-circle me-2"></i> Reject Bill
                                        </button>
                                    @endif
                                    @if($isAdmin)
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('vendors.bills.delete', $bill->uuid) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure? Deleting this bill will reverse stock and vendor balance.')">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="bx bx-trash me-2"></i> Delete Bill
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
                        <div class="text-muted">No purchase bills found.</div>
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
    /* CSS Grid Layout - Fixed column widths with proper spacing */
    .bill-grid {
        display: grid;
        grid-template-columns: 140px 80px 1fr 160px 180px 100px;
        gap: 16px;
        align-items: center;
        width: 100%;
    }
    
    /* Make vendor column take remaining space */
    .bill-grid > div:nth-child(3) {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    /* Ensure vendor name doesn't overflow */
    .bill-grid > div:nth-child(3) a {
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    
    @media (max-width: 1200px) {
        .bill-grid {
            grid-template-columns: 130px 70px 1fr 140px 160px 80px;
            gap: 12px;
        }
    }
    
    @media (max-width: 992px) {
        .bill-grid {
            grid-template-columns: 120px 70px 1fr 130px 150px 70px;
            gap: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .bill-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .bill-row {
            margin-bottom: 15px;
        }
    }
    
    .bill-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
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
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
    }
    
    .approve-action-btn:hover {
        transform: scale(1.02);
        filter: brightness(0.95);
    }
    
    /* Status badge styles */
    .badge.bg-success {
        background-color: #28a745 !important;
        color: white !important;
        font-weight: 500;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
        font-weight: 500;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
        font-weight: 500;
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    if (isAdmin) {
        function approveBill(uuid, id) {
            Swal.fire({
                title: 'Approve Bill #' + id + '?',
                text: "Stock will be added to inventory upon approval.",
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
                        url: '/vendors/bills/approve/' + uuid,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function() { Swal.fire({ title: 'Approved!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); },
                        error: function() { Swal.fire({ title: 'Error!', text: 'Something went wrong', icon: 'error' }); }
                    });
                }
            });
        }
        
        function rejectBill(uuid, id) {
            Swal.fire({
                title: 'Reject Bill #' + id + '?',
                text: "This will permanently delete the bill.",
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
                        url: '/vendors/bills/delete/' + uuid,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                        success: function() { Swal.fire({ title: 'Rejected!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); },
                        error: function() { Swal.fire({ title: 'Error!', text: 'Something went wrong', icon: 'error' }); }
                    });
                }
            });
        }
        
        $(document).on('click', '.approve-action-btn', function(e) {
            e.preventDefault();
            let uuid = $(this).data('bill-uuid');
            let id = $(this).data('bill-id');
            Swal.fire({
                title: 'Bill #' + id,
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