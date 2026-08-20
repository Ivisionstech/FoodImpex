@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span> Customers
            </h4>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Customer
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Search Customer</label>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, phone or email..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Balance Status</label>
                            <select name="balance_status" class="form-select">
                                <option value="">All</option>
                                <option value="due" {{ request('balance_status') == 'due' ? 'selected' : '' }}>Due (Balance > 0)</option>
                                <option value="paid" {{ request('balance_status') == 'paid' ? 'selected' : '' }}>Paid (Balance = 0)</option>
                                <option value="credit" {{ request('balance_status') == 'credit' ? 'selected' : '' }}>Credit (Balance < 0)</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('search') || request()->has('balance_status'))
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
                    <i class="bx bx-user me-2 text-primary"></i>
                    Customer List
                </h5>
                <div class="text-muted small">
                    Total: {{ $customers->total() }} customers
                </div>
            </div>

            {{-- CSS Grid Layout for Customers --}}
            <div class="table-responsive" style="overflow-x: auto;">
                <div class="p-3" style="min-width: 800px;">
                    {{-- Header Row --}}
                    <div class="customer-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                        <div class="fw-bold text-muted">Customer Name</div>
                        <div class="fw-bold text-muted">Balance</div>
                        <div class="fw-bold text-muted">Phone</div>
                        <div class="fw-bold text-muted text-center">Actions</div>
                    </div>

                    {{-- Customer Rows --}}
                    @forelse ($customers as $customer)
                        <div class="customer-grid customer-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="customer-row-{{ $customer->uuid }}">
                            {{-- Customer Name Column (Clickable) --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Customer Name</div>
                                <a href="{{ route('customers.view', $customer->uuid) }}" class="text-decoration-none fw-semibold text-dark">
                                    {{ $customer->person_name ?? $customer->name }}
                                </a>
                                @if($customer->person_name)
                                    <div class="small text-muted">{{ $customer->name }}</div>
                                @endif
                            </div>
                            
                            {{-- Balance Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Balance</div>
                                @php
                                    $balance = floatval($customer->balance ?? 0);
                                    // SWAPPED: Positive = CR, Negative = DR
                                    $balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';
                                    $balanceLabel = $balance >= 0 ? 'CR' : 'DR';
                                @endphp
                                <span class="fw-bold {{ $balanceClass }}">
                                    PKR {{ number_format(abs($balance), 2) }}
                                    @if($balance != 0)
                                        <small class="text-muted">{{ $balanceLabel }}</small>
                                    @endif
                                </span>
                            </div>
                            
                            {{-- Phone Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Phone</div>
                                <span class="text-muted">{{ $customer->phone ?? '-' }}</span>
                            </div>
                            
                            {{-- Actions Column --}}
                            <div class="text-center">
                                <div class="d-md-none fw-bold text-muted small">Actions</div>
                                <div class="action-icons d-flex justify-content-center gap-1">
                                    <!-- View -->
                                    <a href="{{ route('customers.view', $customer->uuid) }}" 
                                       class="btn-action-icon btn-view" 
                                       title="View Details">
                                        <i class="bx bx-show-alt"></i>
                                    </a>
                                    
                                    <!-- Edit -->
                                    <a href="{{ route('customers.edit', $customer->uuid) }}" 
                                       class="btn-action-icon btn-edit" 
                                       title="Edit Customer">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    
                                    <!-- Download Ledger -->
                                    <a href="{{ route('customers.bank-statement', $customer->uuid) }}" 
                                       class="btn-action-icon btn-ledger" 
                                       target="_blank"
                                       title="Download Ledger">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    
                                    <!-- Delete -->
                                    @if($isAdmin)
                                        <a href="javascript:void(0);" 
                                           class="btn-action-icon btn-delete action-confirm" 
                                           data-url="{{ route('customers.delete', $customer->uuid) }}"
                                           data-text="You want to delete this customer!" 
                                           data-button-text="Yes, Delete it!"
                                           title="Delete Customer">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-user-x fs-1 mb-2 d-block text-muted"></i>
                            <div class="text-muted">No customers found.</div>
                            <a href="{{ route('customers.create') }}" class="btn btn-primary mt-3">
                                <i class="bx bx-plus me-1"></i> Add New Customer
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            @if($customers->total() > $customers->perPage())
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
                        </div>
                        <div>
                            {{ $customers->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CSS Grid Layout */
    .customer-grid {
        display: grid;
        grid-template-columns: minmax(200px, 1fr) 160px minmax(150px, 1fr) 200px;
        gap: 16px;
        align-items: center;
        width: 100%;
    }
    
    /* Customer Name column - allow wrapping */
    .customer-grid > div:nth-child(1) {
        word-break: break-word;
        white-space: normal;
    }
    
    /* Phone column */
    .customer-grid > div:nth-child(3) {
        word-break: break-word;
        white-space: normal;
    }
    
    /* Balance column - keep on one line */
    .customer-grid > div:nth-child(2) {
        white-space: nowrap;
    }
    
    @media (max-width: 992px) {
        .customer-grid {
            grid-template-columns: minmax(180px, 1fr) 140px minmax(130px, 1fr) 170px;
            gap: 12px;
        }
    }
    
    @media (max-width: 768px) {
        .customer-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .customer-row {
            margin-bottom: 15px;
        }
        .action-icons {
            justify-content: center !important;
        }
    }
    
    .customer-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .customer-row:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
        border-color: #696cff !important;
    }
    
    /* ============================================
       ACTION ICON BUTTONS
       ============================================ */
    .action-icons {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    
    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.25s ease;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    
    .btn-action-icon:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .btn-action-icon i {
        font-size: 17px;
        line-height: 1;
    }
    
    /* View - Blue */
    .btn-view {
        background: #e7f1ff;
        color: #0d6efd;
    }
    .btn-view:hover {
        background: #0d6efd;
        color: #fff;
    }
    
    /* Edit - Amber */
    .btn-edit {
        background: #fff3e0;
        color: #f57c00;
    }
    .btn-edit:hover {
        background: #f57c00;
        color: #fff;
    }
    
    /* Delete - Red */
    .btn-delete {
        background: #fde8e8;
        color: #dc3545;
    }
    .btn-delete:hover {
        background: #dc3545;
        color: #fff;
    }
    
    /* Ledger - Teal */
    .btn-ledger {
        background: #e0f7fa;
        color: #00838f;
    }
    .btn-ledger:hover {
        background: #00838f;
        color: #fff;
    }
    
    /* ============================================
       OTHER STYLES
       ============================================ */
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
        transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
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
    
    .alert {
        border-radius: 12px;
    }
    
    .customer-grid a {
        transition: all 0.2s ease;
    }
    
    .customer-grid a:hover {
        color: #696cff !important;
        transform: translateX(3px);
        display: inline-block;
    }
</style>

<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirmation dialog for delete
    $(document).on('click', '.action-confirm', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        let text = $(this).data('text') || "You want to delete this record!";
        let confirmButtonText = $(this).data('button-text') || "Yes, Delete it!";
        
        Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>
@endpush