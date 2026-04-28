@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard / General Transactions /</span>
                Entries History
            </h4>
            <a href="{{ route('general-transactions.general-entry') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> New Entry
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
            $pendingCount = $entries->where('approval_status', 'pending')->count();
            $approvedCount = $entries->where('approval_status', 'approved')->count();
        @endphp

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-muted">Total Amount</h6>
                                <h3 class="mb-0 text-primary">PKR {{ number_format($entries->sum('amount'), 2) }}</h3>
                            </div>
                            <div class="rounded-circle p-3" style="background-color: rgba(105, 108, 255, 0.1);">
                                <i class="bx bx-money fs-2 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-muted">Total Transactions</h6>
                                <h3 class="mb-0 text-info">{{ $entries->count() }}</h3>
                            </div>
                            <div class="rounded-circle p-3" style="background-color: rgba(13, 202, 240, 0.1);">
                                <i class="bx bx-transfer-alt fs-2 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-muted">Pending Approval</h6>
                                <h3 class="mb-0 text-warning">{{ $pendingCount }}</h3>
                            </div>
                            <div class="rounded-circle p-3" style="background-color: rgba(255, 193, 7, 0.1);">
                                <i class="bx bx-time fs-2 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-muted">Approved</h6>
                                <h3 class="mb-0 text-success">{{ $approvedCount }}</h3>
                            </div>
                            <div class="rounded-circle p-3" style="background-color: rgba(40, 167, 69, 0.1);">
                                <i class="bx bx-check-circle fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <option value="">All</option>
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
                    <i class="bx bx-list-ul me-2 text-primary"></i>
                    General Entries
                </h5>
                <div class="text-muted small">
                    Total: {{ $entries->total() }} entries
                </div>
            </div>

            {{-- CSS Grid Layout for Entries --}}
            <div class="table-responsive" style="overflow-x: auto;">
                <div class="p-3" style="min-width: 1000px;">
                    {{-- Header Row --}}
                    <div class="entries-grid header-row d-none d-md-grid mb-2 pb-2 border-bottom">
                        <div class="fw-bold text-muted">Date</div>
                        <div class="fw-bold text-muted">Description</div>
                        <div class="fw-bold text-muted">Amount</div>
                        <div class="fw-bold text-muted">Status</div>
                        <div class="fw-bold text-muted text-center">Actions</div>
                    </div>

                    {{-- Entry Rows --}}
                    @forelse ($entries as $entry)
                        <div class="entries-grid entries-row mb-3 p-3 rounded-3 border bg-white shadow-sm" id="entry-row-{{ $entry->id }}">
                            {{-- Date Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Date</div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-M-Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('h:i A') }}</div>
                            </div>
                            
                            {{-- Description Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Description</div>
                                <span class="fw-semibold">{{ \Str::limit($entry->description ?? 'N/A', 60) }}</span>
                            </div>
                            
                            {{-- Amount Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Amount</div>
                                <span class="fw-bold text-primary">
                                    PKR {{ number_format($entry->amount ?? 0, 2) }}
                                </span>
                            </div>
                            
                            {{-- Status Column --}}
                            <div>
                                <div class="d-md-none fw-bold text-muted small">Status</div>
                                @if($entry->approval_status == 'approved')
                                    <span class="badge bg-success" style="background-color: #28a745 !important; padding: 6px 12px; border-radius: 20px;">
                                        <i class="bx bx-check-circle me-1"></i> Approved
                                    </span>
                                @else
                                    @if($isAdmin)
                                        <button type="button" 
                                            class="btn btn-sm approve-entry-btn"
                                            style="background-color: #ffc107 !important; color: #000 !important; padding: 6px 12px; border-radius: 20px; border: none; font-size: 0.75rem;"
                                            data-entry-id="{{ $entry->id }}"
                                            data-entry-amount="{{ $entry->amount }}">
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
                                        <a class="dropdown-item py-2" href="#" onclick="viewEntry({{ $entry->id }})">
                                            <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                        </a>
                                        <a class="dropdown-item py-2" href="#" onclick="printEntry({{ $entry->id }})">
                                            <i class="bx bx-printer me-2 text-secondary"></i> Print
                                        </a>
                                        @if($isAdmin && $entry->approval_status == 'pending')
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item py-2 text-success approve-entry-btn" 
                                                    data-entry-id="{{ $entry->id }}" 
                                                    data-entry-amount="{{ $entry->amount }}">
                                                <i class="bx bx-check-circle me-2"></i> Approve Entry
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-receipt fs-1 mb-2 d-block text-muted"></i>
                            <div class="text-muted">No entries found.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            @if($entries->total() > $entries->perPage())
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="text-muted small">
                            Showing {{ $entries->firstItem() }} to {{ $entries->lastItem() }} of {{ $entries->total() }} entries
                        </div>
                        <div>
                            {{ $entries->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- View Modal for Entry Details -->
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
    .entries-grid {
        display: grid;
        grid-template-columns: 130px minmax(280px, 1fr) 150px 150px 80px;
        gap: 16px;
        align-items: center;
        width: 100%;
    }
    
    /* Description column - allow wrapping */
    .entries-grid > div:nth-child(2) {
        word-break: break-word;
        white-space: normal;
    }
    
    /* Amount column - keep on one line */
    .entries-grid > div:nth-child(3) {
        white-space: nowrap;
    }
    
    @media (max-width: 992px) {
        .entries-grid {
            grid-template-columns: 120px minmax(220px, 1fr) 130px 130px 70px;
            gap: 12px;
        }
    }
    
    @media (max-width: 768px) {
        .entries-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .entries-row {
            margin-bottom: 15px;
        }
    }
    
    .entries-row {
        transition: all 0.2s ease;
        border: 1px solid #e9ecef !important;
    }
    
    .entries-row:hover {
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
    
    .approve-entry-btn:hover {
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
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    if (isAdmin) {
        function approveEntry(id, amount) {
            Swal.fire({
                title: 'Approve Entry?',
                text: "Amount: PKR " + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}),
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
                        url: '{{ url("/general-transactions/approve") }}/' + id,
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
        
        $(document).on('click', '.approve-entry-btn', function(e) {
            e.preventDefault();
            let id = $(this).data('entry-id');
            let amount = $(this).data('entry-amount');
            approveEntry(id, amount);
        });
    }
});

// View entry function
function viewEntry(id) {
    $('#viewModal').modal('show');
    $('#modalContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading entry details...</p></div>');
    
    // You can implement AJAX call here to fetch entry details
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
                </div>
            </div>
        `);
    }, 500);
}

// Print entry function
function printEntry(id) {
    window.open('/general-transactions/print/' + id, '_blank');
}
</script>
@endpush