@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Statistics Cards -->
        @php
            $totalEntries = $daybooks->total();
            
            // Determine credit/debit based on description or amount sign
            $creditEntries = 0;
            $debitEntries = 0;
            $totalDebitAmount = 0;
            $totalCreditAmount = 0;
            
            foreach ($daybooks as $daybook) {
                $descLower = strtolower($daybook->description ?? '');
                $isCredit = false;
                $isDebit = false;
                
                // Check if it's a credit entry based on description
                if (strpos($descLower, 'credit') !== false || 
                    strpos($descLower, 'income') !== false || 
                    strpos($descLower, 'received') !== false ||
                    strpos($descLower, 'payment received') !== false) {
                    $isCredit = true;
                } 
                // Check if it's a debit entry based on description
                elseif (strpos($descLower, 'debit') !== false || 
                        strpos($descLower, 'expense') !== false || 
                        strpos($descLower, 'payment') !== false ||
                        strpos($descLower, 'withdraw') !== false) {
                    $isDebit = true;
                }
                // If still not determined, use the status field
                else {
                    if ($daybook->status == 0) {
                        $isCredit = true;
                    } else {
                        $isDebit = true;
                    }
                }
                
                if ($isCredit) {
                    $creditEntries++;
                    $totalCreditAmount += abs($daybook->amount);
                } else {
                    $debitEntries++;
                    $totalDebitAmount += abs($daybook->amount);
                }
            }
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
                                <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-3">
                                    <i class="bx bx-arrow-up fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Credit Entries</h6>
                                <h3 class="mb-0 fw-bold text-success">{{ $creditEntries }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalEntries > 0 ? ($creditEntries/$totalEntries)*100 : 0 }}%"></div>
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
                                <span class="badge bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                                    <i class="bx bx-arrow-down fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Debit Entries</h6>
                                <h3 class="mb-0 fw-bold text-danger">{{ $debitEntries }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalEntries > 0 ? ($debitEntries/$totalEntries)*100 : 0 }}%"></div>
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
                                <h3 class="mb-0 fw-bold text-info">PKR {{ number_format($totalCreditAmount + $totalDebitAmount, 0) }}</h3>
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

        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span> Daybooks
        </h4>

        {{-- Filter Section --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('daybooks.list') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted small">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $from_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted small">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $to_date ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('from_date') || request()->has('to_date'))
                                    <a href="{{ route('daybooks.list') }}" class="btn btn-outline-secondary w-100">
                                        <i class="bx bx-refresh me-1"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center pt-4 pb-2">
                <h5 class="mb-0">
                    <i class="bx bx-list-ul me-2 text-primary"></i>
                    Daybook Entries
                    <span class="badge bg-primary ms-2">{{ $daybooks->total() }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('daybooks.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Add Expense
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Date</th>
                                <th width="25%">Description</th>
                                <th width="12%">Debit</th>
                                <th width="12%">Credit</th>
                                <th width="8%">Type</th>
                                <th width="12%">Status</th>
                                <th width="14%">Current Balance</th>
                                <th width="6%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBalance = 0; @endphp
                            @forelse ($daybooks as $index => $daybook)
                                @php
                                    // Determine if this is a debit or credit based on description
                                    $descLower = strtolower($daybook->description ?? '');
                                    $isCredit = false;
                                    $isDebit = false;
                                    
                                    // Check description for credit keywords
                                    if (strpos($descLower, 'credit') !== false || 
                                        strpos($descLower, 'income') !== false || 
                                        strpos($descLower, 'received') !== false ||
                                        strpos($descLower, 'payment received') !== false) {
                                        $isCredit = true;
                                    } 
                                    // Check description for debit keywords
                                    elseif (strpos($descLower, 'debit') !== false || 
                                            strpos($descLower, 'expense') !== false || 
                                            strpos($descLower, 'payment') !== false ||
                                            strpos($descLower, 'withdraw') !== false) {
                                        $isDebit = true;
                                    }
                                    // If still not determined, use status field
                                    else {
                                        if ($daybook->status == 0) {
                                            $isCredit = true;
                                        } else {
                                            $isDebit = true;
                                        }
                                    }
                                    
                                    // Clean description - remove "credit from" or "debit from" prefix
                                    $cleanDescription = $daybook->description ?? 'N/A';
                                    $cleanDescription = preg_replace('/^credit from\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^debit from\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^credit\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^debit\s*/i', '', $cleanDescription);
                                    $cleanDescription = ucfirst(trim($cleanDescription));
                                    
                                    // Debit/Credit amounts
                                    $debitAmount = $isDebit ? abs($daybook->amount) : 0;
                                    $creditAmount = $isCredit ? abs($daybook->amount) : 0;
                                    
                                    // Update running balance
                                    if ($isCredit) {
                                        $runningBalance += $creditAmount;
                                    } else {
                                        $runningBalance -= $debitAmount;
                                    }
                                    
                                    // Type
                                    $type = $isCredit ? 'CR' : 'DR';
                                    $typeClass = $isCredit ? 'success' : 'danger';
                                    $typeIcon = $isCredit ? 'arrow-up' : 'arrow-down';
                                    
                                    // Approval Status
                                    $approvalStatus = $daybook->approval_status ?? 'approved';
                                    $statusBadgeClass = $approvalStatus == 'approved' ? 'success' : 'warning';
                                    $statusIcon = $approvalStatus == 'approved' ? 'check-circle' : 'time';
                                    
                                    // Current Balance
                                    $balanceClass = $runningBalance >= 0 ? 'text-success' : 'text-danger';
                                    $balanceLabel = $runningBalance >= 0 ? 'CR' : 'DR';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-muted">{{ $daybooks->firstItem() + $index }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ date('d-m-Y', strtotime($daybook->transaction_date)) }}</div>
                                        <div class="small text-muted">{{ date('h:i A', strtotime($daybook->transaction_date)) }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" title="{{ $cleanDescription }}">
                                            {{ \Str::limit($cleanDescription, 45) }}
                                        </div>
                                        @if($daybook->type && $daybook->type != 'transaction')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary mt-1">
                                                <i class="bx bx-tag me-1"></i>
                                                {{ ucfirst($daybook->type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($debitAmount > 0)
                                            <span class="fw-bold text-danger">PKR {{ number_format($debitAmount, 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($creditAmount > 0)
                                            <span class="fw-bold text-success">PKR {{ number_format($creditAmount, 2) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $typeClass }} bg-opacity-10 text-{{ $typeClass }} px-3 py-2 rounded-pill">
                                            <i class="bx bx-{{ $typeIcon }} me-1"></i>
                                            {{ $type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadgeClass }} bg-opacity-10 text-{{ $statusBadgeClass }} px-3 py-2 rounded-pill">
                                            <i class="bx bx-{{ $statusIcon }} me-1"></i>
                                            {{ ucfirst($approvalStatus) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold {{ $balanceClass }}">
                                            PKR {{ number_format(abs($runningBalance), 2) }} 
                                            <small>{{ $balanceLabel }}</small>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown" aria-expanded="false" style="position: relative; z-index: 2;">
                                                <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" style="z-index: 1060; min-width: 200px; border-radius: 12px;">
                                                <li>
                                                    @if($daybook->uuid)
                                                        <a class="dropdown-item py-2 px-3" href="{{ route('daybooks.view', $daybook->uuid) }}">
                                                            <i class="bx bx-show-alt me-2 text-info" style="font-size: 18px;"></i>
                                                            <span>View Details</span>
                                                        </a>
                                                    @else
                                                        <span class="dropdown-item py-2 px-3 text-muted">
                                                            <i class="bx bx-show-alt me-2 text-muted" style="font-size: 18px;"></i>
                                                            <span>No UUID (Cannot View)</span>
                                                        </span>
                                                    @endif
                                                </li>
                                                @if(auth()->user()->role == 'admin')
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        @if($daybook->uuid)
                                                            <form action="{{ route('daybooks.delete', $daybook->uuid) }}" method="POST" 
                                                                  onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                                                @csrf
                                                                @method('POST')
                                                                <button type="submit" class="dropdown-item py-2 px-3 text-danger">
                                                                    <i class="bx bx-trash me-2" style="font-size: 18px;"></i>
                                                                    <span>Delete Entry</span>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="dropdown-item py-2 px-3 text-muted">
                                                                <i class="bx bx-trash me-2 text-muted" style="font-size: 18px;"></i>
                                                                <span>Cannot Delete (No UUID)</span>
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="bx bx-receipt fs-1 text-muted"></i>
                                        </div>
                                        <h6 class="text-muted">No daybook entries found</h6>
                                        <p class="text-muted small mb-0">Try adjusting your filters or create a new entry.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Summary Table (2x2) -->
                <div class="row mt-3">
                    <div class="col-md-4 offset-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-semibold text-muted">Total Debit</span>
                                    <span class="fw-bold text-danger">PKR {{ number_format($totalDebitAmount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold text-muted">Total Credit</span>
                                    <span class="fw-bold text-success">PKR {{ number_format($totalCreditAmount, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold">Net Balance</span>
                                    <span class="fw-bold {{ $totalCreditAmount - $totalDebitAmount >= 0 ? 'text-success' : 'text-danger' }}">
                                        PKR {{ number_format(abs($totalCreditAmount - $totalDebitAmount), 2) }}
                                        {{ $totalCreditAmount - $totalDebitAmount >= 0 ? 'CR' : 'DR' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                @if($daybooks->total() > 0)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3 border-top">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted small">
                                Showing {{ $daybooks->firstItem() }} to {{ $daybooks->lastItem() }} of {{ $daybooks->total() }} entries
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-muted small mb-0">Per Page:</label>
                                <select id="perPageSelect" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10" {{ $daybooks->perPage() == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $daybooks->perPage() == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $daybooks->perPage() == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $daybooks->perPage() == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            {{ $daybooks->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
    .card .badge.bg-success.bg-opacity-10 { background-color: rgba(40, 167, 69, 0.1) !important; color: #28a745 !important; }
    .card .badge.bg-danger.bg-opacity-10 { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; }
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
    
    /* Print Styles */
    @media print {
        .layout-menu,
        .layout-navbar,
        .btn,
        .card.mb-4,
        form,
        .card-footer,
        .content-backdrop,
        footer,
        .dropdown,
        .no-print {
            display: none !important;
        }

        .layout-page {
            padding: 0 !important;
            margin: 0 !important;
        }

        .content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }

        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .table {
            width: 100% !important;
            border: 1px solid #ddd !important;
        }

        .table th,
        .table td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }

        .card-header h5 {
            font-size: 1.5rem !important;
            margin-bottom: 20px !important;
            text-align: center;
            display: block !important;
        }

        body {
            color: #000 !important;
            background: #fff !important;
        }

        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }
        
        .row.g-4.mb-4 {
            display: none !important;
        }
    }
</style>

<script>
$(document).ready(function() {
    // Animate progress bars on load
    $('.progress-bar').each(function() {
        var width = $(this).css('width');
        $(this).css('width', '0%');
        setTimeout(() => {
            $(this).css('width', width);
        }, 300);
    });
    
    // Per page select
    $('#perPageSelect').on('change', function() {
        var perPage = $(this).val();
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        window.location.href = currentUrl.toString();
    });
});
</script>
@endpush