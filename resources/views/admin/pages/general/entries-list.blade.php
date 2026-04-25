@extends('admin.layout.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Dashboard / General Transactions /</span>
        Entries History
    </h4>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Total Amount</h6>
                            <h3 class="mb-0 text-primary">PKR {{ number_format($entries->sum('amount'), 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-money fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Total Expense</h6>
                            <h3 class="mb-0 text-danger">PKR {{ number_format($entries->where('type', 'expense')->sum('amount'), 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-credit-card fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Total Income</h6>
                            <h3 class="mb-0 text-success">PKR {{ number_format($entries->where('type', 'transaction')->sum('amount'), 2) }}</h3>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-trending-up fs-2"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">General Entries List</h5>
            <a href="{{ route('general-transactions.general-entry') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> New Entry
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" id="to_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select id="type_filter" class="form-control">
                        <option value="">All Types</option>
                        <option value="transaction">Transaction</option>
                        <option value="expense">Expense</option>
                        <option value="general_entry">General Entry</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary me-2" id="filterBtn">
                        <i class="bx bx-filter"></i> Filter
                    </button>
                    <button class="btn btn-outline-secondary" id="resetBtn">
                        <i class="bx bx-reset"></i> Reset
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="entriesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d-m-Y') }}</td>
                                <td>{{ $entry->description ?? 'N/A' }}</td>
                                <td>PKR {{ number_format($entry->amount ?? 0, 2) }}</td>
                                <td>
                                    @if($entry->type == 'debit' || $entry->type == 'expense')
                                        <span class="badge bg-danger">Debit/Expense</span>
                                    @elseif($entry->type == 'credit' || $entry->type == 'transaction')
                                        <span class="badge bg-success">Credit/Income</span>
                                    @else
                                        <span class="badge bg-info">{{ ucfirst($entry->type) }}</span>
                                    @endif
                                </td>
                                <td>{{ $entry->reference ?? 'N/A' }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="#" class="dropdown-item" onclick="viewEntry({{ $entry->id }})">
                                                    <i class="bx bx-show me-1"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="dropdown-item" onclick="printEntry({{ $entry->id }})">
                                                    <i class="bx bx-printer me-1"></i> Print
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No entries found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalContent">
                Loading...
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 1rem;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }
    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #696cff;
        color: white !important;
        border: none;
    }
</style>
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with proper configuration
    var table = $('#entriesTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']], // Sort by date column descending
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5], // All columns
                orderable: true
            },
            {
                targets: 5, // Actions column
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: "No entries found",
            zeroRecords: "No matching entries found"
        }
    });

    // Custom filter function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var typeFilter = $('#type_filter').val();
            
            var date = data[0]; // Date column
            var type = data[3].toLowerCase(); // Type column
            
            // Parse date (assuming format dd-mm-yyyy)
            var dateParts = date.split('-');
            var rowDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
            
            // Date filtering
            if (fromDate && toDate) {
                var from = new Date(fromDate);
                var to = new Date(toDate);
                to.setHours(23, 59, 59, 999);
                
                if (rowDate < from || rowDate > to) {
                    return false;
                }
            }
            
            // Type filtering
            if (typeFilter) {
                if (typeFilter === 'debit' && !type.includes('debit') && !type.includes('expense')) {
                    return false;
                }
                if (typeFilter === 'credit' && !type.includes('credit') && !type.includes('income')) {
                    return false;
                }
                if (typeFilter === 'transaction' && !type.includes('transaction')) {
                    return false;
                }
            }
            
            return true;
        }
    );

    // Filter button click
    $('#filterBtn').click(function() {
        table.draw();
    });

    // Reset button click
    $('#resetBtn').click(function() {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#type_filter').val('');
        table.search('').columns().search('').draw();
    });
});

// View entry function
function viewEntry(id) {
    $('#viewModal').modal('show');
    $('#modalContent').html('Loading entry details...');
    
    // You can implement AJAX call here to fetch entry details
    setTimeout(function() {
        $('#modalContent').html(`
            <div class="alert alert-info">
                Entry details for ID: ${id}. Implement AJAX to fetch actual data.
            </div>
        `);
    }, 500);
}

// Print entry function
function printEntry(id) {
    window.open(`/general-transactions/print/${id}`, '_blank');
}
</script>
@endpush