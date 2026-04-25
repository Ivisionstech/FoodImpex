@extends('admin.layout.master')

@php
    // Ensure variables are always defined
    if(!isset($generalEntries)) {
        $generalEntries = collect([]);
    }
    if(!isset($payments)) {
        $payments = collect([]);
    }
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Purchase /</span> Payments & Entries History
        </h4>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Total Payments</h6>
                                <h3 class="mb-0 text-primary">PKR {{ number_format($payments->sum('amount'), 2) }}</h3>
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
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Total Entries</h6>
                                <h3 class="mb-0 text-info">PKR {{ number_format($generalEntries->sum('amount'), 2) }}</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-transfer-alt fs-2"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Total Transactions</h6>
                                <h3 class="mb-0 text-success">{{ $payments->count() + $generalEntries->count() }}</h3>
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
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1">Grand Total</h6>
                                <h3 class="mb-0 text-warning">PKR {{ number_format($payments->sum('amount') + $generalEntries->sum('amount'), 2) }}</h3>
                            </div>
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-wallet fs-2"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route(Route::currentRouteName()) }}" method="GET" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" 
                                   value="{{ request('from_date') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" 
                                   value="{{ request('to_date') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="payments" {{ request('type') == 'payments' ? 'selected' : '' }}>Vendor Payments</option>
                                <option value="general_entries" {{ request('type') == 'general_entries' ? 'selected' : '' }}>General Entries</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-reset me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Transactions</h5>
                <div>
                    <a href="{{ route('general-transactions.general-entry') }}" class="btn btn-info me-2">
                        <i class="bx bx-transfer-alt me-1"></i> New General Entry
                    </a>
                    <a href="{{ route('vendors.payments.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Send Payment
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Amount (PKR)</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $allTransactions = collect([]);
                            
                            // Add payments to collection
                            foreach ($payments as $payment) {
                                $description = $payment->description ?? 'Payment to Vendor';
                                // Clean up any duplicate "Payment to" text
                                if (strpos($description, 'Payment to') === false && strpos($description, 'Payment sent') === false) {
                                    $description = 'Payment to ' . ($payment->vendor->company_name ?? 'Vendor');
                                }
                                
                                $allTransactions->push([
                                    'uuid' => $payment->uuid,
                                    'id' => $payment->id,
                                    'date' => $payment->date,
                                    'description' => $description,
                                    'reference' => $payment->vendor->company_name ?? 'Unknown Vendor',
                                    'amount' => $payment->amount,
                                    'type' => 'payment',
                                    'type_badge' => 'success',
                                    'type_label' => 'Vendor Payment',
                                    'method' => ucfirst($payment->send_via ?? 'N/A'),
                                    'is_payment' => true,
                                    'amount_class' => 'text-success',
                                    'original' => $payment
                                ]);
                            }
                            
                            // Add general entries to collection
                            foreach ($generalEntries as $entry) {
                                $description = $entry->description ?? 'General Entry';
                                
                                $allTransactions->push([
                                    'uuid' => $entry->uuid ?? ('entry_'.$entry->id),
                                    'id' => $entry->id,
                                    'date' => $entry->date ?? $entry->transaction_date ?? now(),
                                    'description' => $description,
                                    'reference' => $entry->reference ?? 'System',
                                    'amount' => $entry->amount ?? 0,
                                    'type' => $entry->type ?? 'general',
                                    'type_badge' => $entry->type_badge ?? 'info',
                                    'type_label' => $entry->type_label ?? 'General Entry',
                                    'method' => $entry->method ?? 'Transfer',
                                    'is_payment' => false,
                                    'amount_class' => $entry->amount_class ?? 'text-primary',
                                ]);
                            }
                            
                            // Sort by date descending
                            $allTransactions = $allTransactions->sortByDesc('date');
                        @endphp

                        @forelse ($allTransactions as $transaction)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($transaction['date'])->format('d-M-Y') }}</strong><br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($transaction['date'])->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $transaction['description'] }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $transaction['reference'] }}</span>
                                </td>
                                <td class="{{ $transaction['amount_class'] }} fw-bold">
                                    PKR {{ number_format($transaction['amount'], 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $transaction['type_badge'] }} rounded-pill">
                                        {{ $transaction['type_label'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $transaction['is_payment'] ? 'info' : 'secondary' }} rounded-pill">
                                        <i class="bx bx-{{ $transaction['is_payment'] ? 'bank' : 'transfer' }} me-1"></i>
                                        {{ $transaction['method'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @if($transaction['is_payment'])
                                                <a class="dropdown-item" href="{{ route('vendors.payments.show', $transaction['uuid']) }}">
                                                    <i class="bx bx-show-alt me-1"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="{{ route('vendors.payments.edit', $transaction['uuid']) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('vendors.payments.delete', $transaction['uuid']) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure? This will refund the amount to your Bank/Cash and update vendor balance.')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Delete Payment
                                                    </button>
                                                </form>
                                            @else
                                                <a class="dropdown-item" href="#" onclick="viewEntry('{{ $transaction['uuid'] }}', {{ $transaction['id'] }})">
                                                    <i class="bx bx-show-alt me-1"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="printEntry('{{ $transaction['uuid'] }}')">
                                                    <i class="bx bx-printer me-1"></i> Print
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-1 mb-3"></i>
                                        <h5>No transactions found</h5>
                                        <p class="mb-3">No transactions match your current filter criteria.</p>
                                        @if(request('from_date') || request('to_date') || request('type'))
                                            <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-primary">
                                                <i class="bx bx-reset me-1"></i> Clear Filters
                                            </a>
                                        @else
                                            <a href="{{ route('vendors.payments.create') }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i> Create New Payment
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($allTransactions->isNotEmpty())
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>Showing {{ $allTransactions->count() }} transactions</small>
                    </div>
                    <div>
                        <small class="text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            Page {{ request()->get('page', 1) }} of {{ ceil($allTransactions->count() / 25) }}
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- View Modal for General Entries -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Entry Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading entry details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Auto-submit form when date fields change (optional)
        $('#from_date, #to_date, select[name="type"]').on('change', function() {
            $('#filterForm').submit();
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    function viewEntry(uuid, id) {
        $('#viewModal').modal('show');
        
        // Show loading state
        $('#modalContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading entry details...</p>
            </div>
        `);
        
        // If it's a daybook entry (has daybook_ prefix), show basic info
        if (uuid && uuid.toString().startsWith('daybook_')) {
            setTimeout(function() {
                $('#modalContent').html(`
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bx bx-transfer-alt fs-2 me-2"></i>
                            <h6 class="mb-0">General Entry Details</h6>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Entry ID:</strong> 
                                <span class="badge bg-label-info">#${id}</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Date:</strong> ${new Date().toLocaleDateString()}
                            </div>
                            <div class="col-12 mb-2">
                                <strong>Description:</strong> 
                                <p class="mt-1">${$('#transactionsTable tr').find('td:eq(1) span').text()}</p>
                            </div>
                            <div class="col-12">
                                <p class="text-muted mt-3">
                                    <i class="bx bx-info-circle me-1"></i>
                                    This is a general entry transaction. Full details available in General Transactions section.
                                </p>
                            </div>
                        </div>
                    </div>
                `);
            }, 500);
        } 
        else {
            // For regular entries, try to fetch via AJAX
            $.ajax({
                url: '/general-transactions/view/' + id,
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response);
                },
                error: function(xhr) {
                    let errorMsg = 'Could not load entry details.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#modalContent').html(`
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bx bx-error-circle fs-2 me-2"></i>
                                <h6 class="mb-0">Entry Details</h6>
                            </div>
                            <hr>
                            <p class="mb-0"><strong>ID:</strong> ${id}</p>
                            <p class="mb-2"><strong>Description:</strong> ${$('#transactionsTable tr').find('td:eq(1) span').text()}</p>
                            <p class="text-muted mt-3 mb-0">${errorMsg}</p>
                        </div>
                    `);
                }
            });
        }
    }

    function printEntry(uuid) {
        // For demo purposes, just show a toast
        alert('Print functionality will be implemented here for entry: ' + uuid);
        
        // Example: window.open('/general-transactions/print/' + uuid, '_blank');
    }
</script>
@endpush