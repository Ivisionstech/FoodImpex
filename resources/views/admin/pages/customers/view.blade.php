@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Customers</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Personal Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if ($customer->profile)
                            <img src="{{ asset('storage/' . $customer->profile) }}" width="100" height="100"
                                alt="Avatar" class="rounded-circle mb-3" />
                        @else
                            <img src="{{ asset('images/placeholder.jpg') }}" width="100" height="100" alt="Avatar"
                                class="rounded-circle mb-3" />
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Customer Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $customer->name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Contact Person:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $customer->person_name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $customer->email }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Phone:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $customer->phone }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Address:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $customer->address }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Current Balance:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-label-primary">PKR {{ number_format($customer->balance, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <!-- Customer Bills Section -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customers Bills</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('customers.view', $customer->uuid) }}" class="d-flex gap-2">
                        <input type="hidden" name="trans_from" value="{{ $trans_from }}">
                        <input type="hidden" name="trans_to" value="{{ $trans_to }}">
                        <input type="date" name="bill_from" class="form-control" value="{{ $bill_from }}">
                        <input type="date" name="bill_to" class="form-control" value="{{ $bill_to }}">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        @if (request()->has('bill_from') || request()->has('bill_to'))
                            <a href="{{ route('customers.view', ['uuid' => $customer->uuid, 'trans_from' => $trans_from, 'trans_to' => $trans_to]) }}"
                                class="btn btn-secondary">Clear</a>
                        @endif
                    </form>
                    <a href="{{ route('bills.create', $customer->uuid) }}" class="btn btn-primary">Add Bill</a>
                    <a href="{{ route('new.bills.create') }}" class="btn btn-info">Add New Bill</a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>View</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($customerBills as $bill)
                            <tr>
                                <td>
                                    {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>PKR {{ number_format($bill->total_amount, 0) }}</td>
                                <td>
                                    @if (($bill->type ?? null) === 'new bill')
                                        <a href="{{ route('new.bills.show', $bill->uuid) }}">
                                            <i class='bx bx-md bx-show'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('customers.bills.show', $bill->uuid) }}">
                                            <i class='bx bx-md bx-show'></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if (($bill->type ?? null) === 'new bill')
                                        <a href="{{ route('customers.bills.download.new', $bill->uuid) }}">
                                            <i class='bx bx-md bx-download'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('customers.bills.download', $bill->uuid) }}">
                                            <i class='bx bx-md bx-download'></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Customer Bank Statement Section with General Entries -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customers Bank Statement</h5>
                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('customers.view', $customer->uuid) }}" class="d-flex gap-2">
                        <input type="hidden" name="bill_from" value="{{ $bill_from }}">
                        <input type="hidden" name="bill_to" value="{{ $bill_to }}">
                        <input type="date" name="trans_from" class="form-control" value="{{ $trans_from }}">
                        <input type="date" name="trans_to" class="form-control" value="{{ $trans_to }}">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        @if (request()->has('trans_from') || request()->has('trans_to'))
                            <a href="{{ route('customers.view', ['uuid' => $customer->uuid, 'bill_from' => $bill_from, 'bill_to' => $bill_to]) }}"
                                class="btn btn-sm btn-secondary">Clear</a>
                        @endif
                    </form>
                    
                   <!-- View Statement Button - Opens HTML page -->
                   <a href="{{ url('/customer-statement/' . $customer->uuid) }}?{{ http_build_query(request()->all()) }}" 
                    class="btn btn-info ms-2" 
                    target="_blank">
                   <i class='bx bx-show'></i> Report
                   </a>    
                    <a href="{{ route('customers.receive-payment', $customer->uuid) }}"
                        class="btn btn-success ms-2">Receive Payment</a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Current Balance</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($customerTransactions as $transaction)
                            <tr>
                                <td>
                                    {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    @if (in_array($transaction->type, ['bill', 'general_debit']) && isset($transaction->bill) && $transaction->bill)
                                        <strong>Bill #{{ $transaction->bill->id }}</strong><br>
                                        <small class="text-muted">Bill Date:
                                            {{ $transaction->bill->bill_date ? \Carbon\Carbon::parse($transaction->bill->bill_date)->format('d-m-Y') : '-' }}</small>
                                    @elseif ($transaction->type == 'payment')
                                        <strong>Payment Received</strong><br>
                                        <small class="text-muted">{{ $transaction->description ?? 'Payment received' }}</small>
                                        @if(isset($transaction->method) && $transaction->method)
                                            <br><small class="text-muted">Via: {{ ucfirst($transaction->method) }}</small>
                                        @endif
                                    @elseif ($transaction->type == 'balance')
                                        <strong>Opening Balance</strong><br>
                                        <small class="text-muted">Initial balance</small>
                                    @elseif (in_array($transaction->type, ['general_debit', 'general_credit']))
                                        <strong class="text-info">General Entry</strong><br>
                                        <small class="text-muted">{{ $transaction->description ?? 'Manual entry' }}</small>
                                        @if(isset($transaction->entry_type))
                                            <br><small class="text-muted">Type: {{ $transaction->entry_type }}</small>
                                        @endif
                                    @else
                                        <strong>{{ ucfirst($transaction->type) }}</strong><br>
                                        <small class="text-muted">{{ $transaction->description ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold 
                                        @if($transaction->type == 'payment') text-success
                                        @elseif(in_array($transaction->type, ['general_credit'])) text-info
                                        @elseif(in_array($transaction->type, ['bill', 'general_debit'])) text-danger
                                        @elseif($transaction->type == 'balance') text-warning
                                        @else text-danger
                                        @endif">
                                        PKR {{ number_format($transaction->amount, 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($transaction->type == 'payment') bg-label-success
                                        @elseif(in_array($transaction->type, ['general_credit'])) bg-label-info
                                        @elseif(in_array($transaction->type, ['bill', 'general_debit'])) bg-label-danger
                                        @elseif($transaction->type == 'balance') bg-label-warning
                                        @else bg-label-secondary
                                        @endif">
                                        @if($transaction->type == 'balance')
                                            Balance
                                        @elseif(in_array($transaction->type, ['general_debit', 'general_credit']))
                                            General Entry
                                        @else
                                            {{ ucfirst($transaction->type) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">PKR {{ number_format(abs($transaction->current_balance), 0) }}
                                        <small>{{ $transaction->current_balance >= 0 ? 'DR' : 'CR' }}</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if (in_array($transaction->type, ['bill', 'general_debit']) && isset($transaction->bill) && $transaction->bill)
                                        @if (isset($transaction->bill->type) && ($transaction->bill->type ?? null) === 'new bill')
                                            <a href="{{ route('new.bills.show', $transaction->bill->uuid) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Bill">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @else
                                            <a href="{{ route('customers.bills.show', $transaction->bill->uuid) }}"
                                                class="btn btn-sm btn-outline-primary" title="View Bill">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @endif
                                    @elseif ($transaction->type == 'payment')
                                        <a href="{{ route('customers.receive-payment.show', $transaction->uuid) }}"
                                            class="btn btn-sm btn-outline-info" title="View Payment Details">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    @elseif ($transaction->type == 'balance')
                                        <span class="text-muted" title="Opening Balance Entry">
                                            <i class='bx bx-info-circle fs-5'></i>
                                        </span>
                                    @elseif (in_array($transaction->type, ['general_debit', 'general_credit']))
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                title="View General Entry Details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#generalEntryModal"
                                                data-entry-date="{{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '-' }}"
                                                data-entry-amount="{{ number_format($transaction->amount, 0) }}"
                                                data-entry-type="{{ $transaction->type == 'general_debit' ? 'DEBIT' : 'CREDIT' }}"
                                                data-entry-description="{{ $transaction->description ?? 'No description provided' }}"
                                                data-entry-balance="{{ number_format(abs($transaction->current_balance), 0) }} {{ $transaction->current_balance >= 0 ? 'DR' : 'CR' }}">
                                            <i class='bx bx-show'></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class='bx bx-receipt bx-lg text-muted'></i>
                                    <p class="text-muted mt-2">No transactions found for this customer.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- General Entry Details Modal -->
    <div class="modal fade" id="generalEntryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class='bx bx-info-circle me-2'></i>
                        General Entry Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Date:</div>
                        <div class="col-8" id="modalEntryDate">-</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Amount:</div>
                        <div class="col-8" id="modalEntryAmount">-</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Type:</div>
                        <div class="col-8">
                            <span id="modalEntryType" class="badge"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Description:</div>
                        <div class="col-8" id="modalEntryDescription">-</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Balance After:</div>
                        <div class="col-8" id="modalEntryBalance">-</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Modal data population for General Entries
        var generalEntryModal = document.getElementById('generalEntryModal');
        if (generalEntryModal) {
            generalEntryModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var entryDate = button.getAttribute('data-entry-date');
                var entryAmount = button.getAttribute('data-entry-amount');
                var entryType = button.getAttribute('data-entry-type');
                var entryDescription = button.getAttribute('data-entry-description');
                var entryBalance = button.getAttribute('data-entry-balance');
                
                var modalDate = generalEntryModal.querySelector('#modalEntryDate');
                var modalAmount = generalEntryModal.querySelector('#modalEntryAmount');
                var modalType = generalEntryModal.querySelector('#modalEntryType');
                var modalDescription = generalEntryModal.querySelector('#modalEntryDescription');
                var modalBalance = generalEntryModal.querySelector('#modalEntryBalance');
                
                if (modalDate) modalDate.textContent = entryDate;
                if (modalAmount) modalAmount.textContent = 'PKR ' + entryAmount;
                if (modalType) {
                    modalType.textContent = entryType;
                    if (entryType === 'DEBIT') {
                        modalType.className = 'badge bg-danger';
                    } else {
                        modalType.className = 'badge bg-success';
                    }
                }
                if (modalDescription) modalDescription.textContent = entryDescription;
                if (modalBalance) modalBalance.textContent = 'PKR ' + entryBalance;
            });
        }
    });
</script>
@endpush