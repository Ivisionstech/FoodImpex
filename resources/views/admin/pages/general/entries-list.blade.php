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

        <!-- STATISTICS CARDS -->
        @php
            $totalEntries = $paginatedEntries->total();
            $pendingCount = $paginatedEntries->where('approval_status', 'pending')->count();
            $approvedCount = $paginatedEntries->where('approval_status', 'approved')->count();
            $totalAmount = $paginatedEntries->sum('amount');
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                    <i class="bx bx-receipt fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Total Entries</h6>
                                <h3 class="mb-0 fw-bold">{{ $totalEntries }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning bg-opacity-10 text-warning p-2 rounded-3">
                                    <i class="bx bx-time fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Pending Entries</h6>
                                <h3 class="mb-0 fw-bold text-warning">{{ $pendingCount }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalEntries > 0 ? ($pendingCount/$totalEntries)*100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-3">
                                    <i class="bx bx-check-circle fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Approved Entries</h6>
                                <h3 class="mb-0 fw-bold text-success">{{ $approvedCount }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalEntries > 0 ? ($approvedCount/$totalEntries)*100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-info bg-opacity-10 text-info p-2 rounded-3">
                                    <i class="bx bx-money fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Total Amount</h6>
                                <h3 class="mb-0 fw-bold text-info">PKR {{ number_format($totalAmount, 0) }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('general-transactions.index') }}" method="GET" id="filterForm">
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
                                <a href="{{ route('general-transactions.index') }}" class="btn btn-outline-secondary px-4">
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
                        Showing {{ $paginatedEntries->firstItem() }} to {{ $paginatedEntries->lastItem() }} of {{ $paginatedEntries->total() }} entries
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Date</th>
                                <th width="30%">Description</th>
                                <th width="20%">Account</th>
                                <th width="12%">Approval</th>
                                <th width="10%">Amount</th>
                                <th width="8%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paginatedEntries as $index => $entry)
                                @php
                                    $isDebit = $entry->debit_type && $entry->debit_id;
                                    $isCredit = $entry->credit_type && $entry->credit_id;
                                    $amount = $entry->amount ?? 0;
                                    $isDue = strpos($entry->description ?? '', 'DUE:') !== false;
                                    $isClearedDue = strpos($entry->description ?? '', 'CLEARED:') !== false;
                                    $isAdmin = auth()->user()->role == 'admin';
                                    $isGrouped = $entry->is_grouped ?? false;
                                    $entryCount = $entry->entry_count ?? 1;
                                    
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
                                    
                                    // Determine amount type for amount display
                                    $amountClass = '';
                                    if ($isDue && $entry->approval_status == 'pending') {
                                        $amountClass = 'text-danger';
                                    } elseif ($isClearedDue) {
                                        $amountClass = 'text-info';
                                    } elseif ($isDebit) {
                                        $amountClass = 'text-danger';
                                    } elseif ($isCredit) {
                                        $amountClass = 'text-success';
                                    } else {
                                        $amountClass = 'text-dark';
                                    }
                                    
                                    // Determine account name for single entries
                                    $accountName = '';
                                    $accountType = '';
                                    $accountIcon = '';
                                    if (!$isGrouped) {
                                        if ($isDebit) {
                                            $accountType = $entry->debit_type;
                                            if ($accountType == 'customer') {
                                                $customer = \App\Models\Customer::find($entry->debit_id);
                                                $accountName = $customer ? $customer->name : 'Customer #' . $entry->debit_id;
                                                $accountIcon = 'bx-user';
                                            } elseif ($accountType == 'vendor') {
                                                $vendor = \App\Models\Vendor::find($entry->debit_id);
                                                $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $entry->debit_id;
                                                $accountIcon = 'bx-store';
                                            } elseif ($accountType == 'bank') {
                                                $bank = \App\Models\Bank::find($entry->debit_id);
                                                $accountName = $bank ? $bank->name : 'Bank #' . $entry->debit_id;
                                                $accountIcon = 'bx-bank';
                                            } elseif ($accountType == 'cash') {
                                                $accountName = 'Cash Account';
                                                $accountIcon = 'bx-wallet';
                                            } elseif ($accountType == 'expense') {
                                                $expense = \App\Models\Expense::find($entry->debit_id);
                                                $accountName = $expense ? $expense->name : 'Expense #' . $entry->debit_id;
                                                $accountIcon = 'bx-purchase-tag';
                                            }
                                        } elseif ($isCredit) {
                                            $accountType = $entry->credit_type;
                                            if ($accountType == 'customer') {
                                                $customer = \App\Models\Customer::find($entry->credit_id);
                                                $accountName = $customer ? $customer->name : 'Customer #' . $entry->credit_id;
                                                $accountIcon = 'bx-user';
                                            } elseif ($accountType == 'vendor') {
                                                $vendor = \App\Models\Vendor::find($entry->credit_id);
                                                $accountName = $vendor ? $vendor->company_name : 'Vendor #' . $entry->credit_id;
                                                $accountIcon = 'bx-store';
                                            } elseif ($accountType == 'bank') {
                                                $bank = \App\Models\Bank::find($entry->credit_id);
                                                $accountName = $bank ? $bank->name : 'Bank #' . $entry->credit_id;
                                                $accountIcon = 'bx-bank';
                                            } elseif ($accountType == 'cash') {
                                                $accountName = 'Cash Account';
                                                $accountIcon = 'bx-wallet';
                                            } elseif ($accountType == 'expense') {
                                                $expense = \App\Models\Expense::find($entry->credit_id);
                                                $accountName = $expense ? $expense->name : 'Expense #' . $entry->credit_id;
                                                $accountIcon = 'bx-purchase-tag';
                                            }
                                        }
                                    }
                                    
                                    $rowClass = $isDue && $entry->approval_status == 'pending' ? 'table-danger' : '';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <span class="fw-semibold text-muted">{{ $paginatedEntries->firstItem() + $index }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-M-Y') }}</div>
                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($entry->transaction_date)->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" title="{{ $entry->description }}">
                                            {{ \Str::limit($entry->description ?? 'N/A', 50) }}
                                        </div>
                                        @if($isGrouped)
                                            <span class="badge bg-primary bg-opacity-10 text-primary mt-1">
                                                <i class="bx bx-layer me-1"></i> {{ $entryCount }} entries in batch
                                            </span>
                                        @endif
                                        @if($isDue && $entry->approval_status == 'pending')
                                            <span class="badge bg-danger bg-opacity-10 text-danger mt-1">
                                                <i class="bx bx-info-circle me-1"></i> Due - Needs Payment
                                            </span>
                                        @endif
                                        @if($isClearedDue)
                                            <span class="badge bg-info bg-opacity-10 text-info mt-1">
                                                <i class="bx bx-check-circle me-1"></i> Due Cleared
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isGrouped)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-layer fs-5 text-primary"></i>
                                                <div>
                                                    <div class="fw-semibold small">Multiple Accounts</div>
                                                    <div class="small text-muted">{{ $entryCount }} entries</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx {{ $accountIcon }} fs-5 text-primary"></i>
                                                <div>
                                                    <div class="fw-semibold small text-capitalize">{{ $accountType }}</div>
                                                    <div class="small text-muted">{{ \Str::limit($accountName, 20) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $approvalClass }} bg-opacity-10 text-{{ $approvalClass }} px-3 py-2 rounded-pill">
                                            <i class="bx bx-{{ $entry->approval_status == 'approved' ? 'check-circle' : 'time' }} me-1"></i>
                                            {{ $approvalBadge }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold {{ $amountClass }}">
                                            PKR {{ number_format($amount, 0) }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative; z-index: 2;">
                                                <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="z-index: 1060; min-width: 200px; border-radius: 12px;">
                                                <!-- View Option -->
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="{{ route('general-transactions.get-entry', $entry->id) }}">
                                                        <i class="bx bx-show-alt me-2 text-info" style="font-size: 18px;"></i>
                                                        <span>View Details</span>
                                                    </a>
                                                </li>
                                                
                                                <!-- Edit Option -->
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="{{ route('general-transactions.edit', $entry->id) }}">
                                                        <i class="bx bx-edit-alt me-2 text-warning" style="font-size: 18px;"></i>
                                                        <span>Edit Entry</span>
                                                    </a>
                                                </li>
                                                
                                                <!-- Approve Option - Admin only for pending entries -->
                                                @if($isAdmin && $entry->approval_status == 'pending' && !$isClearedDue)
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 px-3 approve-entry-btn" 
                                                                data-entry-id="{{ $entry->id }}" 
                                                                data-entry-amount="{{ $entry->amount }}">
                                                            <i class="bx bx-check-circle me-2 text-success" style="font-size: 18px;"></i>
                                                            <span>Approve Entry</span>
                                                        </button>
                                                    </li>
                                                @endif
                                                
                                                <!-- Delete Option - Admin only for pending entries -->
                                                @if($isAdmin && $entry->approval_status == 'pending')
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 px-3 delete-entry-btn" 
                                                                data-entry-id="{{ $entry->id }}">
                                                            <i class="bx bx-trash me-2 text-danger" style="font-size: 18px;"></i>
                                                            <span class="text-danger">Delete Entry</span>
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-receipt fs-1 text-muted"></i>
                                        </div>
                                        <h6 class="text-muted">No entries found</h6>
                                        <p class="text-muted small mb-0">Try adjusting your filters or create a new entry.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($paginatedEntries->total() > 0)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3 border-top">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted small">
                                Showing {{ $paginatedEntries->firstItem() }} to {{ $paginatedEntries->lastItem() }} of {{ $paginatedEntries->total() }} entries
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-muted small mb-0">Per Page:</label>
                                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10" {{ $paginatedEntries->perPage() == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $paginatedEntries->perPage() == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $paginatedEntries->perPage() == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $paginatedEntries->perPage() == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            {{ $paginatedEntries->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* Statistics Cards */
    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }
    
    .card .badge.bg-primary.bg-opacity-10 { background-color: rgba(105, 108, 255, 0.1) !important; color: #696cff !important; }
    .card .badge.bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107 !important; }
    .card .badge.bg-success.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; color: #28a745 !important; }
    .card .badge.bg-info.bg-opacity-10 { background-color: rgba(23, 162, 184, 0.1) !important; color: #17a2b8 !important; }
    
    .progress {
        border-radius: 10px;
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }
    
    /* Table Styling */
    .table > :not(caption) > * > * {
        padding: 12px 10px;
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04);
        transition: all 0.2s ease;
    }
    
    .table tr.table-danger {
        border-left: 3px solid #dc3545;
    }
    
    /* Badge Styles */
    .badge.bg-success.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; color: #28a745 !important; }
    .badge.bg-warning.bg-opacity-10 { background-color: rgba(255, 193, 7, 0.1) !important; color: #ffc107 !important; }
    .badge.bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }
    .badge.bg-secondary.bg-opacity-10 { background-color: rgba(108, 117, 125, 0.1) !important; color: #6c757d !important; }
    .badge.bg-info.bg-opacity-10 { background-color: rgba(23, 162, 184, 0.1) !important; color: #17a2b8 !important; }
    
    /* Dropdown Styling */
    .dropdown-menu {
        border-radius: 12px;
        animation: fadeInDown 0.2s ease;
        min-width: 200px;
        padding: 8px 0;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12) !important;
    }
    
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .dropdown-item {
        transition: all 0.2s ease;
        border-radius: 8px;
        margin: 2px 8px;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    
    .dropdown-item:hover {
        transform: translateX(5px);
        background-color: rgba(105, 108, 255, 0.06);
    }
    
    .dropdown-item i {
        width: 20px;
        text-align: center;
    }
    
    .dropdown-divider {
        margin: 6px 12px;
    }
    
    /* Pagination */
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
    
    /* Action Button */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .btn-icon:hover {
        background-color: rgba(105, 108, 255, 0.1);
        transform: scale(1.1);
    }
    
    .dropdown {
        position: relative;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            min-width: 768px;
        }
    }
</style>

<script>
$(document).ready(function() {
    var isAdmin = '{{ auth()->user()->role }}' == 'admin';
    
    // Animate progress bars on load
    $('.progress-bar').each(function() {
        var width = $(this).css('width');
        $(this).css('width', '0%');
        setTimeout(() => {
            $(this).css('width', width);
        }, 300);
    });
    
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
                                text: response.message || 'Entry approved successfully!', 
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
</script>
@endpush