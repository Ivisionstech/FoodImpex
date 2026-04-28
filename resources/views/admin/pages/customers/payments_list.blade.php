@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span> Received Payments
            </h4>
            <a href="{{ route('customers.receive-payment.general') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Receive New Payment
            </a>
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
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $from_date ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $to_date ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Approval Status</label>
                            <select name="approval_status" class="form-select">
                                <option value="">All Payments</option>
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
                    <i class="bx bx-money me-2 text-primary"></i>
                    Received Payments
                </h5>
            </div>

            {{-- CSS Grid Layout for Payments --}}
            <div class="p-3">
                {{-- Header Row --}}
                <div class="payment-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                    <div class="fw-bold text-muted">Date</div>
                    <div class="fw-bold text-muted">Customer</div>
                    <div class="fw-bold text-muted">Method</div>
                    <div class="fw-bold text-muted">Amount</div>
                    <div class="fw-bold text-muted">Remarks</div>
                    <div class="fw-bold text-muted">Status</div>
                    <div class="fw-bold text-muted text-center">Actions</div>
                </div>

                {{-- Payment Rows --}}
                @forelse ($payments as $payment)
                    <div class="payment-grid payment-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="payment-row-{{ $payment->uuid }}">
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Date</div>
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($payment->transaction_date)->format('d-M-Y') }}</div>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($payment->transaction_date)->format('h:i A') }}</div>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Customer</div>
                            @if($payment->customer)
                                <a href="{{ route('customers.view', $payment->customer->uuid) }}" class="text-decoration-none fw-medium">
                                    {{ $payment->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Method</div>
                            <span class="badge bg-label-info text-uppercase" style="background-color: rgba(13, 110, 253, 0.1) !important; color: #0d6efd !important; padding: 6px 12px; border-radius: 20px;">
                                {{ $payment->receive_via ?? 'Cash' }}
                            </span>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Amount</div>
                            <span class="fw-bold text-success">PKR {{ number_format($payment->amount, 2) }}</span>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Remarks</div>
                            <small class="text-muted">{{ Str::limit($payment->description ?? 'No remarks', 40) }}</small>
                        </div>
                        
                        <div>
                            <div class="d-md-none fw-bold text-muted small">Status</div>
                            @if($payment->approval_status == 'approved')
                                <span class="badge bg-success" style="background-color: #28a745 !important; padding: 6px 12px; font-size: 0.75rem; border-radius: 20px;">
                                    <i class="bx bx-check-circle me-1"></i> Approved
                                </span>
                            @else
                                @if($isAdmin)
                                    <button type="button" 
                                        class="btn btn-sm approve-payment-btn"
                                        style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; border-radius: 20px; border: none; font-size: 0.75rem;"
                                        data-payment-uuid="{{ $payment->uuid }}"
                                        data-payment-id="{{ $payment->id }}">
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
                                    <a class="dropdown-item py-2" href="{{ route('customers.receive-payment.edit', $payment->uuid) }}">
                                        <i class="bx bx-edit-alt me-2 text-primary"></i> Edit Payment
                                    </a>
                                    
                                    @if($isAdmin && $payment->approval_status == 'pending')
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item py-2 text-success approve-payment-btn" 
                                                data-payment-uuid="{{ $payment->uuid }}" 
                                                data-payment-id="{{ $payment->id }}">
                                            <i class="bx bx-check-circle me-2"></i> Approve Payment
                                        </button>
                                    @endif

                                    @if($isAdmin)
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item py-2 text-danger delete-payment-btn" 
                                                data-payment-uuid="{{ $payment->uuid }}" 
                                                data-customer-id="{{ $payment->customer->uuid ?? '' }}">
                                            <i class="bx bx-trash me-2"></i> Delete Payment
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bx bx-money fs-1 mb-2 d-block text-muted"></i>
                        <div class="text-muted">No payment history found.</div>
                    </div>
                @endforelse
            </div>

            <div class="card-footer bg-transparent">
                @if(method_exists($payments, 'links'))
                    <div class="d-flex justify-content-center">
                        {{ $payments->appends(request()->input())->links('pagination::bootstrap-5') }}
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
    .payment-grid {
        display: grid;
        grid-template-columns: 140px 180px 100px 130px 1fr 140px 80px;
        gap: 16px;
        align-items: center;
        width: 100%;
    }
    
    .payment-grid > div:nth-child(5) {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    @media (max-width: 1200px) {
        .payment-grid {
            grid-template-columns: 130px 160px 90px 120px 1fr 130px 70px;
            gap: 12px;
        }
    }
    
    @media (max-width: 992px) {
        .payment-grid {
            grid-template-columns: 120px 150px 90px 110px 1fr 120px 70px;
            gap: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .payment-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .payment-row {
            margin-bottom: 15px;
        }
    }
    
    .payment-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .payment-row:hover {
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
    
    .approve-payment-btn:hover {
        transform: scale(1.02);
        filter: brightness(0.95);
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    if (isAdmin) {
        // Approve Payment Function
        function approvePayment(uuid, id) {
            Swal.fire({
                title: 'Approve Payment #' + id + '?',
                text: "This payment will be marked as approved and customer balance will be updated.",
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
                        url: '/customers/payments/approve/' + uuid,
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
        
        // Delete Payment Function
        function deletePayment(uuid) {
            Swal.fire({
                title: 'Delete Payment?',
                text: "This action will reverse the customer balance. This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    $.ajax({
                        url: '/customers/payments/delete/' + uuid,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) { 
                            Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
                        },
                        error: function(xhr) { 
                            let msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({ title: 'Error!', text: msg, icon: 'error' }); 
                        }
                    });
                }
            });
        }
        
        // Click on Status Button
        $(document).on('click', '.approve-payment-btn', function(e) {
            e.preventDefault();
            let uuid = $(this).data('payment-uuid');
            let id = $(this).data('payment-id');
            approvePayment(uuid, id);
        });
        
        // Click on Delete Button
        $(document).on('click', '.delete-payment-btn', function(e) {
            e.preventDefault();
            let uuid = $(this).data('payment-uuid');
            deletePayment(uuid);
        });
    }
});
</script>
@endpush