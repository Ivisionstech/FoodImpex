@extends('admin.layout.master')
@section('content')
    <style>
        .dropdown-item {
            display: inline-block !important;
            padding: 0 !important;
        }
        /* Custom transaction row styling */
        .transaction-bill {
            border-left: 4px solid #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.02);
        }
        .transaction-payment {
            border-left: 4px solid #28a745 !important;
            background-color: rgba(40, 167, 69, 0.02);
        }
        .transaction-balance {
            border-left: 4px solid #17a2b8 !important;
            background-color: rgba(23, 162, 184, 0.02);
        }
        .transaction-return {
            border-left: 4px solid #ffc107 !important;
            background-color: rgba(255, 193, 7, 0.02);
        }
        .transaction-debit {
            border-left: 4px solid #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.05);
        }
        .transaction-credit {
            border-left: 4px solid #28a745 !important;
            background-color: rgba(40, 167, 69, 0.05);
        }
        .row-transition {
            transition: all 0.2s ease;
        }
        .row-transition:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .badge-glow {
            box-shadow: 0 0 8px currentColor;
        }
        .amount-positive {
            color: #28a745;
            font-weight: 600;
        }
        .amount-negative {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Vendors</h4>
        
        <!-- Vendor Details Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Personal Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if ($vendor->profile)
                            <img src="{{ asset('storage/' . $vendor->profile) }}" width="100" height="100"
                                alt="Avatar" class="rounded-circle mb-3" />
                        @else
                            <img src="{{ asset('images/placeholder.jpg') }}" width="100" height="100" alt="Avatar"
                                class="rounded-circle mb-3" />
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Company Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->company_name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Contact Person:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->person_name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->email }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Phone:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->phone }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Address:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->address }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Current Balance:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-{{ $vendor->balance < 0 ? 'danger' : ($vendor->balance > 0 ? 'success' : 'secondary') }} badge-glow">
                                    PKR {{ number_format($vendor->balance, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Bills Card -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendors Bills</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="trans_from" value="{{ $trans_from ?? '' }}">
                        <input type="hidden" name="trans_to" value="{{ $trans_to ?? '' }}">
                        <input type="date" name="bill_from" class="form-control form-control-sm" value="{{ $bill_from ?? '' }}" placeholder="From Date">
                        <input type="date" name="bill_to" class="form-control form-control-sm" value="{{ $bill_to ?? '' }}" placeholder="To Date">
                        <button type="submit" class="btn btn-primary btn-sm">Filter Bills</button>
                        @if (request()->has('bill_from') || request()->has('bill_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid, 'trans_from' => $trans_from ?? '', 'trans_to' => $trans_to ?? '']) }}"
                                class="btn btn-secondary btn-sm">Clear</a>
                        @endif
                    </form>
                    <a href="{{ route('vendors.bills.create', $vendor->uuid) }}" class="btn btn-primary btn-sm">Add Bill</a>
                    <a href="{{ route('vendors.bills.general_create_2') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-plus"></i> Add Purchase Bill
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" style="min-height: 200px;">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Bill #</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>View</th>
                            <th>Download</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($vendorBills as $bill)
                            <tr>
                                <td>
                                    {{ $bill->date ? \Carbon\Carbon::parse($bill->date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    <span class="fw-bold">#{{ $bill->id }}</span>
                                </td>
                                <td class="text-danger fw-bold">
                                    PKR {{ number_format($bill->total_amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $bill->approval_status == 'approved' ? 'success' : 'warning' }}">
                                        {{ ucfirst($bill->approval_status ?? 'pending') }}
                                    </span>
                                </td>
                                <td>
                                    @php $firstProduct = $bill->billProducts->first(); @endphp
                                    @if ($firstProduct && $firstProduct->type === 'product')
                                        <a href="{{ route('vendors.bills.general_show_2', $bill->uuid) }}" class="btn btn-sm btn-outline-primary">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('vendors.bills.show', $bill->uuid) }}" class="btn btn-sm btn-outline-primary">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $firstProduct = $bill->billProducts->first();
                                    @endphp
                                    @if ($firstProduct && $firstProduct->type === 'product')
                                        <a href="{{ route('vendors.bills.general_pdf_2', $bill->uuid) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class='bx bx-download'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('vendors.bills.download', $bill->uuid) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class='bx bx-download'></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @php
                                                $firstProduct = $bill->billProducts->first();
                                            @endphp
                                            @if ($firstProduct && $firstProduct->type === 'product')
                                                <a class="dropdown-item" href="{{ route('vendors.bills.general_edit_2', $bill->uuid) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            @else
                                                <a class="dropdown-item" href="{{ route('vendors.bills.edit', $bill->uuid) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                            @endif
                                            <a class="dropdown-item action-confirm" href="javascript:void(0);"
                                                data-url="{{ route('vendors.bills.delete', $bill->uuid) }}"
                                                data-text="You want to delete this bill!" data-button-text="Yes, Delete it!">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bx bx-receipt fs-1 text-muted"></i>
                                    <p class="mt-2 text-muted">No bills found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vendor Transactions/Bank Statement Card -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendor Statement</h5>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="bill_from" value="{{ $bill_from ?? '' }}">
                        <input type="hidden" name="bill_to" value="{{ $bill_to ?? '' }}">
                        <input type="date" name="trans_from" class="form-control form-control-sm" value="{{ $trans_from ?? '' }}" placeholder="From Date">
                        <input type="date" name="trans_to" class="form-control form-control-sm" value="{{ $trans_to ?? '' }}" placeholder="To Date">
                        <button type="submit" class="btn btn-primary btn-sm">Filter Transactions</button>
                        @if (request()->has('trans_from') || request()->has('trans_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid, 'bill_from' => $bill_from ?? '', 'bill_to' => $bill_to ?? '']) }}"
                                class="btn btn-secondary btn-sm">Clear</a>
                        @endif
                    </form>
                    <a href="{{ route('vendors.bank-statement', $vendor->uuid) }}" class="btn btn-info btn-sm" target="_blank">
                        <i class='bx bx-download'></i> Report
                    </a>
                    <a href="{{ route('vendors.send-payment', $vendor->uuid) }}" class="btn btn-primary btn-sm">Send Payment</a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" style="min-height: 200px;">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Transaction Type</th>
                            <th>Current Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($vendorTransactions as $transaction)
                            @php
                                // Determine row class based on transaction type and transaction_type
                                $rowClass = '';
                                $amountClass = '';
                                $badgeClass = '';
                                $badgeText = '';
                                $icon = '';
                                
                                // Check for transaction_type first (debit/credit from general entry)
                                if (isset($transaction->transaction_type)) {
                                    if ($transaction->transaction_type == 'debit') {
                                        $rowClass = 'transaction-debit';
                                        $amountClass = 'amount-negative';
                                        $badgeClass = 'danger';
                                        $badgeText = 'Debit';
                                        $icon = 'bx-arrow-down';
                                    } elseif ($transaction->transaction_type == 'credit') {
                                        $rowClass = 'transaction-credit';
                                        $amountClass = 'amount-positive';
                                        $badgeClass = 'success';
                                        $badgeText = 'Credit';
                                        $icon = 'bx-arrow-up';
                                    }
                                }
                                
                                // Override based on type (bill, payment, return, balance)
                                if ($transaction->type == 'bill') {
                                    $rowClass = 'transaction-bill';
                                    $amountClass = 'amount-negative';
                                    $badgeClass = 'danger';
                                    $badgeText = 'Bill';
                                    $icon = 'bx-receipt';
                                } elseif ($transaction->type == 'payment') {
                                    $rowClass = 'transaction-payment';
                                    $amountClass = 'amount-positive';
                                    $badgeClass = 'success';
                                    $badgeText = 'Payment';
                                    $icon = 'bx-credit-card';
                                } elseif ($transaction->type == 'return') {
                                    $rowClass = 'transaction-return';
                                    $amountClass = 'amount-positive';
                                    $badgeClass = 'warning';
                                    $badgeText = 'Return';
                                    $icon = 'bx-undo';
                                } elseif ($transaction->type == 'balance') {
                                    $rowClass = 'transaction-balance';
                                    $amountClass = 'amount-positive';
                                    $badgeClass = 'info';
                                    $badgeText = 'Opening';
                                    $icon = 'bx-balance';
                                }
                                
                                // If transaction_type is set and type is not set, use transaction_type
                                if (isset($transaction->transaction_type) && !in_array($transaction->type, ['bill', 'payment', 'return', 'balance'])) {
                                    if ($transaction->transaction_type == 'debit') {
                                        $badgeText = 'Debit';
                                        $badgeClass = 'danger';
                                    } else {
                                        $badgeText = 'Credit';
                                        $badgeClass = 'success';
                                    }
                                }
                                
                                // Format amount with sign
                                $formattedAmount = number_format($transaction->amount, 2);
                                $displayAmount = $transaction->type == 'bill' ? '- PKR ' . $formattedAmount : 'PKR ' . $formattedAmount;
                                if (isset($transaction->transaction_type)) {
                                    $displayAmount = $transaction->transaction_type == 'debit' ? '- PKR ' . $formattedAmount : '+ PKR ' . $formattedAmount;
                                }
                            @endphp
                            <tr class="row-transition {{ $rowClass }}">
                                <td>
                                    <div class="fw-semibold">{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}</div>
                                    <small class="text-muted">{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('h:i A') : '' }}</small>
                                </td>
                                <td>
                                    @if ($transaction->type == 'bill' && $transaction->bill)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-receipt text-danger"></i>
                                            <div>
                                                <strong>Bill #{{ $transaction->bill->id }}</strong>
                                                <div class="small text-muted">
                                                    Bill Date: {{ $transaction->bill->date ? \Carbon\Carbon::parse($transaction->bill->date)->format('d-m-Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($transaction->type == 'payment')
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-credit-card text-success"></i>
                                            <div>
                                                <strong>Payment</strong>
                                                <div class="small text-muted">
                                                    {{ $transaction->send_via ? 'via ' . ucfirst($transaction->send_via) : 'Payment' }}
                                                    @if($transaction->description) - {{ $transaction->description }} @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($transaction->type == 'balance')
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-balance text-info"></i>
                                            <div>
                                                <strong>Opening Balance</strong>
                                                <div class="small text-muted">Initial balance set</div>
                                            </div>
                                        </div>
                                    @elseif (isset($transaction->transaction_type))
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-transfer-alt text-{{ $transaction->transaction_type == 'debit' ? 'danger' : 'success' }}"></i>
                                            <div>
                                                <strong>{{ ucfirst($transaction->transaction_type) }} Entry</strong>
                                                <div class="small text-muted">{{ $transaction->description ?? 'General transaction entry' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx bx-detail"></i>
                                            <div>
                                                <strong>{{ ucfirst($transaction->type) }}</strong>
                                                <div class="small text-muted">{{ $transaction->description ?? '-' }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $amountClass }}">
                                        {{ $displayAmount }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }} px-3 py-2 rounded-pill">
                                        <i class="bx {{ $icon }} me-1"></i>
                                        {{ $badgeText }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold 
                                        @if($transaction->current_balance < 0) text-danger
                                        @elseif($transaction->current_balance > 0) text-success
                                        @else text-secondary @endif">
                                        PKR {{ number_format($transaction->current_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            @if ($transaction->type == 'bill' && $transaction->bill)
                                                @php
                                                    $firstProduct = $transaction->bill->billProducts->first();
                                                @endphp
                                                @if ($firstProduct && $firstProduct->type === 'product')
                                                    <a class="dropdown-item" href="{{ route('vendors.bills.general_show_2', $transaction->bill->uuid) }}">
                                                        <i class='bx bx-show me-1'></i> View Bill
                                                    </a>
                                                @else
                                                    <a class="dropdown-item" href="{{ route('vendors.bills.show', $transaction->bill->uuid) }}">
                                                        <i class='bx bx-show me-1'></i> View Bill
                                                    </a>
                                                @endif
                                            @elseif ($transaction->type == 'payment')
                                                <a class="dropdown-item" href="{{ route('vendors.payment-details', $transaction->uuid) }}">
                                                    <i class='bx bx-show me-1'></i> Payment Details
                                                </a>
                                            @endif
                                            @if(isset($transaction->transaction_type))
                                                <a class="dropdown-item" href="{{ route('general-transactions.get-entry', $transaction->id ?? '#') }}">
                                                    <i class='bx bx-show me-1'></i> View Transaction
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bx bx-receipt fs-1 text-muted"></i>
                                    <p class="mt-2 text-muted">No transactions found</p>
                                    @if(request()->has('trans_from') || request()->has('trans_to'))
                                        <p class="text-muted small">Try clearing the date filters</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($vendorTransactions, 'links'))
                <div class="card-footer">
                    {{ $vendorTransactions->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Confirmation for delete actions
        $('.action-confirm').on('click', function() {
            var url = $(this).data('url');
            var text = $(this).data('text') || 'You want to delete this!';
            var confirmButtonText = $(this).data('button-text') || 'Yes, Delete it!';
            
            Swal.fire({
                title: 'Are you sure?',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
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