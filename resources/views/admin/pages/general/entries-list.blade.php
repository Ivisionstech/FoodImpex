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

        <!-- STATS CARDS - COMMENTED OUT 
        @php
            $isAdmin = auth()->user()->role == 'admin';
            $pendingCount = $entries->where('approval_status', 'pending')->count();
            $approvedCount = $entries->where('approval_status', 'approved')->count();
            $dueCount = $entries->filter(function($e) { 
                return strpos($e->description ?? '', 'DUE:') !== false && $e->approval_status == 'pending';
            })->count();
            $creditCount = $entries->whereNotNull('credit_id')->count();
            $debitCount = $entries->whereNotNull('debit_id')->count();
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card bg-gradient-primary text-white border-0 shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50 mb-1">Total Amount</h6>
                                <h3 class="text-white mb-0">PKR {{ number_format($entries->sum('amount'), 2) }}</h3>
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="bx bx-money fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ... (other stats cards) ...
        </div>
        -->

        <!-- Filter Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route(Route::currentRouteName()) }}" method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">FROM DATE</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">TO DATE</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">APPROVAL STATUS</label>
                            <select name="approval_status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">AMOUNT TYPE</label>
                            <select name="amount_type" class="form-select">
                                <option value="">All</option>
                                <option value="credit" {{ request('amount_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="debit" {{ request('amount_type') == 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="due" {{ request('amount_type') == 'due' ? 'selected' : '' }}>Due</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-filter-alt me-1"></i> Apply Filters
                            </button>
                            @if (request()->has('from_date') || request()->has('to_date') || request()->has('approval_status') || request()->has('amount_type'))
                                <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-outline-secondary px-4">
                                    <i class="bx bx-refresh me-1"></i> Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Entries List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2 text-primary"></i>
                        General Entries
                    </h5>
                    <div class="text-muted small">
                        Showing {{ $entries->firstItem() }} to {{ $entries->lastItem() }} of {{ $entries->total() }} entries
                    </div>
                </div>
            </div>

            <div class="card-body">
                @forelse ($entries as $index => $entry)
                    @php
                        $isDebit = $entry->debit_type && $entry->debit_id;
                        $isCredit = $entry->credit_type && $entry->credit_id;
                        $amount = $entry->amount ?? 0;
                        $isDue = strpos($entry->description ?? '', 'DUE:') !== false;
                        $isClearedDue = strpos($entry->description ?? '', 'CLEARED:') !== false;
                        
                        // Determine status badges
                        $approvalBadge = '';
                        $approvalClass = '';
                        if ($entry->approval_status == 'approved') {
                            $approvalBadge = 'Approved';
                            $approvalClass = 'success';
                        } else {
                            $approvalBadge = 'Pending';
                            $approvalClass = 'warning';
                        }
                        
                        // Determine amount type badge
                        $amountBadge = '';
                        $amountClass = '';
                        $amountIcon = '';
                        if ($isDue && $entry->approval_status == 'pending') {
                            $amountBadge = 'Due';
                            $amountClass = 'danger';
                            $amountIcon = 'bx-error-circle';
                        } elseif ($isClearedDue) {
                            $amountBadge = 'Cleared';
                            $amountClass = 'info';
                            $amountIcon = 'bx-check-circle';
                        } elseif ($isDebit) {
                            $amountBadge = 'Debit';
                            $amountClass = 'danger';
                            $amountIcon = 'bx-arrow-down';
                        } elseif ($isCredit) {
                            $amountBadge = 'Credit';
                            $amountClass = 'success';
                            $amountIcon = 'bx-arrow-up';
                        } else {
                            $amountBadge = 'Pending';
                            $amountClass = 'secondary';
                            $amountIcon = 'bx-time';
                        }
                        
                        // Determine account name
                        $accountName = '';
                        $accountType = '';
                        if ($isDebit) {
                            $accountType = $entry->debit_type;
                            if ($accountType == 'customer') {
                                $customer = \App\Models\Customer::find($entry->debit_id);
                                $accountName = $customer ? $customer->name : 'Customer #' . $entry->debit_id;
                            } elseif ($accountType == 'vendor') {
                                $vendor = \App\Models\Vendor::find($entry->debit_id);
                                $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $entry->debit_id;
                            } elseif ($accountType == 'bank') {
                                $bank = \App\Models\Bank::find($entry->debit_id);
                                $accountName = $bank ? $bank->name : 'Bank #' . $entry->debit_id;
                            } elseif ($accountType == 'cash') {
                                $accountName = 'Cash Account';
                            }
                        } elseif ($isCredit) {
                            $accountType = $entry->credit_type;
                            if ($accountType == 'customer') {
                                $customer = \App\Models\Customer::find($entry->credit_id);
                                $accountName = $customer ? $customer->name : 'Customer #' . $entry->credit_id;
                            } elseif ($accountType == 'vendor') {
                                $vendor = \App\Models\Vendor::find($entry->credit_id);
                                $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $entry->credit_id;
                            } elseif ($accountType == 'bank') {
                                $bank = \App\Models\Bank::find($entry->credit_id);
                                $accountName = $bank ? $bank->name : 'Bank #' . $entry->credit_id;
                            } elseif ($accountType == 'cash') {
                                $accountName = 'Cash Account';
                            }
                        }
                        
                        $rowClass = $isDue && $entry->approval_status == 'pending' ? 'border-start border-danger border-3' : '';
                    @endphp
                    
                    <div class="entry-card mb-3 p-3 rounded-3 bg-white shadow-sm border {{ $rowClass }}" id="entry-row-{{ $entry->id }}">
                        <div class="row g-3 align-items-center">
                            <!-- Serial Number -->
                            <div class="col-md-1">
                                <div class="text-muted small fw-semibold">#{{ $entries->firstItem() + $index }}</div>
                            </div>
                            
                            <!-- Date & Time -->
                            <div class="col-md-2">
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-M-Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('h:i A') }}</div>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-md-3">
                                <div class="fw-semibold text-truncate" style="max-width: 250px;" title="{{ $entry->description }}">
                                    {{ \Str::limit($entry->description ?? 'N/A', 40) }}
                                </div>
                                @if($isDue)
                                    <div class="small text-danger mt-1">
                                        <i class="bx bx-info-circle me-1"></i> Due Amount - Needs Payment
                                    </div>
                                @endif
                                @if($isClearedDue)
                                    <div class="small text-info mt-1">
                                        <i class="bx bx-check-circle me-1"></i> Due Cleared
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Account -->
                            <div class="col-md-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-{{ $accountType == 'customer' ? 'user' : ($accountType == 'vendor' ? 'store' : ($accountType == 'bank' ? 'bank' : 'wallet')) }} fs-5 text-primary"></i>
                                    <div>
                                        <div class="fw-semibold small">{{ ucfirst($accountType) }}</div>
                                        <div class="small text-muted">{{ $accountName }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Approval Status Badge - CLICKABLE -->
                            <div class="col-md-1">
                                @if($entry->approval_status == 'pending' && $isAdmin && !$isClearedDue)
                                    <button type="button" 
                                        class="badge bg-{{ $approvalClass }} bg-opacity-10 text-{{ $approvalClass }} px-3 py-2 rounded-pill border-0 approve-entry-btn"
                                        data-entry-id="{{ $entry->id }}"
                                        data-entry-amount="{{ $entry->amount }}"
                                        style="cursor: pointer; transition: all 0.2s;">
                                        <i class="bx bx-{{ $entry->approval_status == 'approved' ? 'check-circle' : 'time' }} me-1"></i>
                                        {{ $approvalBadge }}
                                    </button>
                                @else
                                    <span class="badge bg-{{ $approvalClass }} bg-opacity-10 text-{{ $approvalClass }} px-3 py-2 rounded-pill">
                                        <i class="bx bx-{{ $entry->approval_status == 'approved' ? 'check-circle' : 'time' }} me-1"></i>
                                        {{ $approvalBadge }}
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Amount Type Badge -->
                            <div class="col-md-1">
                                <span class="badge bg-{{ $amountClass }} bg-opacity-10 text-{{ $amountClass }} px-3 py-2 rounded-pill">
                                    <i class="bx {{ $amountIcon }} me-1"></i>
                                    {{ $amountBadge }}
                                </span>
                            </div>
                            
                            <!-- Amount -->
                            <div class="col-md-1">
                                <div class="fw-bold {{ $isDue && $entry->approval_status == 'pending' ? 'text-danger' : ($isDebit ? 'text-danger' : ($isCredit ? 'text-success' : 'text-dark')) }}">
                                    PKR {{ number_format($amount, 2) }}
                                </div>
                                @if($isDue && $entry->approval_status == 'pending')
                                    <div class="small text-muted">Pending Payment</div>
                                @endif
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-md-1 text-end" style="position: relative;">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown" style="position: relative; z-index: 5;">
                                        <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="z-index: 1050;">
                                        <a class="dropdown-item py-2" href="#" onclick="viewEntry({{ $entry->id }})">
                                            <i class="bx bx-show-alt me-2 text-info"></i> View Details
                                        </a>
                                        @if($isAdmin && $entry->approval_status == 'pending' && !$isClearedDue)
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item py-2 text-success approve-entry-btn" 
                                                    data-entry-id="{{ $entry->id }}" 
                                                    data-entry-amount="{{ $entry->amount }}">
                                                <i class="bx bx-check-circle me-2"></i> Approve Entry
                                            </button>
                                        @endif
                                        @if($isAdmin && ($entry->approval_status == 'pending' || $isDue))
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item py-2 text-danger delete-entry-btn" 
                                                    data-entry-id="{{ $entry->id }}">
                                                <i class="bx bx-trash me-2"></i> Delete Entry
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bx bx-receipt fs-1 text-muted"></i>
                        </div>
                        <h6 class="text-muted">No entries found</h6>
                        <p class="text-muted small mb-0">Try adjusting your filters or create a new entry.</p>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($entries->total() > 0)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted small">
                                Showing {{ $entries->firstItem() }} to {{ $entries->lastItem() }} of {{ $entries->total() }} entries
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-muted small mb-0">Per Page:</label>
                                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10" {{ $entries->perPage() == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $entries->perPage() == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $entries->perPage() == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $entries->perPage() == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            {{ $entries->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- View Modal -->
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
    /* Gradient Backgrounds for Stats Cards - Commented Out 
    .bg-gradient-primary { background: linear-gradient(135deg, #696cff 0%, #5a5cbf 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #03c3ec 0%, #0299b8 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffab00 0%, #cc8800 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #71dd37 0%, #5ab020 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ff3e1d 0%, #cc3117 100%); }
    .bg-gradient-secondary { background: linear-gradient(135deg, #8592a3 0%, #6a7582 100%); }
    */
    
    .entry-card {
        transition: all 0.2s ease-in-out;
        border: 1px solid #e9ecef;
    }
    
    .entry-card:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        border-color: #696cff !important;
    }
    
    .badge.bg-success.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; color: #28a745 !important; }
    .badge.bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107 !important; }
    .badge.bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }
    .badge.bg-secondary.bg-opacity-10 { background-color: rgba(108, 117, 125, 0.1) !important; color: #6c757d !important; }
    .badge.bg-info.bg-opacity-10 { background-color: rgba(23, 162, 184, 0.1) !important; color: #17a2b8 !important; }
    
    .dropdown-menu {
        border-radius: 12px;
        animation: fadeInDown 0.2s ease;
        z-index: 1060 !important;
    }
    
    .dropdown { position: relative; z-index: 1; }
    .dropdown.show { z-index: 1060; }
    
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
    
    .dropdown-item:hover { transform: translateX(5px); }
    
    .pagination { gap: 5px; margin-bottom: 0; }
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
        box-shadow: 0 2px 8px rgba(105, 108, 255, 0.3);
    }
    .pagination .page-item .page-link:hover {
        transform: translateY(-2px);
        background-color: rgba(105, 108, 255, 0.1);
    }
    
    #perPageSelect {
        cursor: pointer;
        border-radius: 8px;
        border-color: #e9ecef;
    }
    
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .badge.approve-entry-btn:hover {
        transform: scale(1.05);
        cursor: pointer;
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    $('#perPageSelect').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        window.location.href = currentUrl.toString();
    });
    
    if (isAdmin) {
        function approveEntry(id, amount) {
            Swal.fire({
                title: 'Approve Entry?',
                html: "<div class='text-start'>" +
                      "<p>You are about to approve this transaction.</p>" +
                      "<div class='alert alert-info mt-2 mb-0'>" +
                      "<strong>Amount:</strong> PKR " + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) +
                      "</div>" +
                      "</div>",
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
                            Swal.fire({ 
                                title: 'Success!', 
                                text: response.message, 
                                icon: 'success', 
                                timer: 2000, 
                                showConfirmButton: false 
                            }).then(() => location.reload()); 
                        },
                        error: function(xhr) { 
                            let msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({ title: 'Error!', text: msg, icon: 'error' }); 
                        }
                    });
                }
            });
        }
        
        function deleteEntry(id) {
            Swal.fire({
                title: 'Delete Entry?',
                text: "This will permanently delete this entry.",
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
                        url: '{{ url("/general-transactions/delete") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) { 
                            Swal.fire({ title: 'Deleted!', text: 'Entry deleted successfully.', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload()); 
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
            e.stopPropagation();
            let id = $(this).data('entry-id');
            let amount = $(this).data('entry-amount');
            approveEntry(id, amount);
        });
        
        $(document).on('click', '.delete-entry-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let id = $(this).data('entry-id');
            deleteEntry(id);
        });
    }
});

function viewEntry(id) {
    $('#viewModal').modal('show');
    $('#modalContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading entry details...</p></div>');
    
    $.ajax({
        url: '/general-transactions/' + id,
        type: 'GET',
        success: function(response) {
            $('#modalContent').html(response);
        },
        error: function() {
            $('#modalContent').html(`
                <div class="p-3">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Entry ID:</strong> ${id}<br>
                        <strong>Status:</strong> View details feature will be available soon.
                    </div>
                </div>
            `);
        }
    });
}
</script>
@endpush