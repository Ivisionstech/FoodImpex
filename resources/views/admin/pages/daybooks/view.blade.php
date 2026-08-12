@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span>
                <a href="{{ route('daybooks.list') }}" class="text-decoration-none">Daybooks</a> /
                Entry Details
            </h4>
            <div>
                <a href="{{ route('daybooks.list') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
                @if(auth()->user()->role == 'admin')
                    <form action="{{ route('daybooks.delete', $daybook->uuid) }}" method="POST" 
                          class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this entry?')">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-danger ms-2">
                            <i class="bx bx-trash me-1"></i> Delete Entry
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Entry Details -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm hover-shadow">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">
                            <i class="bx bx-info-circle me-2 text-primary"></i>
                            Entry Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Entry ID</div>
                            <div class="fw-bold">#{{ $daybook->id }}</div>
                        </div>
                        <hr>
                      
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Transaction Date</div>
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($daybook->transaction_date)->format('d-M-Y h:i A') }}</div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Amount</div>
                            @php
                                $isCredit = false;
                                $isDebit = false;
                                $descLower = strtolower($daybook->description ?? '');
                                
                                if (strpos($descLower, 'credit') !== false || 
                                    strpos($descLower, 'income') !== false || 
                                    strpos($descLower, 'received') !== false) {
                                    $isCredit = true;
                                } elseif (strpos($descLower, 'debit') !== false || 
                                          strpos($descLower, 'expense') !== false || 
                                          strpos($descLower, 'payment') !== false) {
                                    $isDebit = true;
                                } else {
                                    if ($daybook->status == 0) {
                                        $isCredit = true;
                                    } else {
                                        $isDebit = true;
                                    }
                                }
                            @endphp
                            <div class="fw-bold {{ $isDebit ? 'text-danger' : 'text-success' }}">
                                PKR {{ number_format($daybook->amount, 2) }}
                                <span class="badge bg-{{ $isDebit ? 'danger' : 'success' }} bg-opacity-10 text-{{ $isDebit ? 'danger' : 'success' }} ms-2">
                                    <i class="bx bx-{{ $isDebit ? 'arrow-down' : 'arrow-up' }} me-1"></i>
                                    {{ $isDebit ? 'DR' : 'CR' }}
                                </span>
                            </div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Description</div>
                            <div class="fw-bold text-end" style="max-width: 60%;">
                                @php
                                    $cleanDescription = $daybook->description ?? 'N/A';
                                    $cleanDescription = preg_replace('/^credit from\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^debit from\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^credit\s*/i', '', $cleanDescription);
                                    $cleanDescription = preg_replace('/^debit\s*/i', '', $cleanDescription);
                                    $cleanDescription = ucfirst(trim($cleanDescription));
                                @endphp
                                {{ $cleanDescription }}
                            </div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Entry Type</div>
                            <div>
                                @if($daybook->type)
                                    <span class="badge bg-secondary">
                                        <i class="bx bx-tag me-1"></i>
                                        {{ ucfirst($daybook->type) }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Approval Status</div>
                            <div>
                                @if($daybook->approval_status == 'approved')
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle me-1"></i> Approved
                                    </span>
                                @elseif($daybook->approval_status == 'pending')
                                    <span class="badge bg-warning">
                                        <i class="bx bx-time me-1"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </div>
                        </div>
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold text-muted">Created At</div>
                            <div class="small text-muted">{{ $daybook->created_at->format('d-M-Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Account Details -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm hover-shadow">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0">
                            <i class="bx bx-wallet me-2 text-primary"></i>
                            Account Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold text-muted">Current In-Hand Balance</div>
                            <div class="fw-bold text-info">
                                PKR {{ number_format($daybook->in_hand ?? 0, 2) }}
                            </div>
                        </div>
                        <hr>
                        
                        @if($daybook->customerTransaction)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Customer</div>
                                <div class="fw-bold">
                                    <a href="{{ route('customers.view', $daybook->customerTransaction->customer->uuid ?? '') }}" class="text-decoration-none">
                                        {{ $daybook->customerTransaction->customer->name ?? 'N/A' }}
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Customer Transaction ID</div>
                                <div class="fw-bold">#{{ $daybook->customer_transaction_id }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Transaction Amount</div>
                                <div class="fw-bold">PKR {{ number_format($daybook->customerTransaction->amount ?? 0, 2) }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold text-muted">Customer Balance After</div>
                                <div class="fw-bold text-{{ ($daybook->customerTransaction->current_balance ?? 0) >= 0 ? 'danger' : 'success' }}">
                                    PKR {{ number_format(abs($daybook->customerTransaction->current_balance ?? 0), 2) }}
                                    {{ ($daybook->customerTransaction->current_balance ?? 0) >= 0 ? 'DR' : 'CR' }}
                                </div>
                            </div>
                        @elseif($daybook->vendorTransaction)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Vendor</div>
                                <div class="fw-bold">
                                    <a href="{{ route('vendors.view', $daybook->vendorTransaction->vendor->uuid ?? '') }}" class="text-decoration-none">
                                        {{ $daybook->vendorTransaction->vendor->company_name ?? 'N/A' }}
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Vendor Transaction ID</div>
                                <div class="fw-bold">#{{ $daybook->vendor_transaction_id }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Transaction Amount</div>
                                <div class="fw-bold">PKR {{ number_format($daybook->vendorTransaction->amount ?? 0, 2) }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold text-muted">Vendor Balance After</div>
                                <div class="fw-bold text-{{ ($daybook->vendorTransaction->current_balance ?? 0) >= 0 ? 'danger' : 'success' }}">
                                    PKR {{ number_format(abs($daybook->vendorTransaction->current_balance ?? 0), 2) }}
                                    {{ ($daybook->vendorTransaction->current_balance ?? 0) >= 0 ? 'DR' : 'CR' }}
                                </div>
                            </div>
                        @elseif($daybook->expense)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Expense Name</div>
                                <div class="fw-bold">{{ $daybook->expense->name ?? 'N/A' }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold text-muted">Expense ID</div>
                                <div class="fw-bold">#{{ $daybook->expense_id }}</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold text-muted">Expense Amount</div>
                                <div class="fw-bold text-danger">PKR {{ number_format($daybook->expense->amount ?? 0, 2) }}</div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No additional account details available for this entry.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    
    .card-header {
        background-color: transparent !important;
    }
    
    .table-sm th, .table-sm td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
</style>
@endpush