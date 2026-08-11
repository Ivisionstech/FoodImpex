@extends('admin.layout.master')
@section('content')
    <style>
        .dropdown-item {
            display: inline-block !important;
            padding: 0 !important;
        }
        .badge-type {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 5px;
        }
        .badge-bill { background-color: #28a745; color: white; }
        .badge-payment { background-color: #dc3545; color: white; }
        .badge-balance { background-color: #17a2b8; color: white; }
        .badge-return { background-color: #dc3545; color: white; }
        .badge-general { background-color: #6c757d; color: white; }
        .badge-dr { background-color: #dc3545; color: white; }
        .badge-cr { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-approved { background-color: #28a745; color: white; }
        .text-dr { color: #dc3545 !important; font-weight: bold; }
        .text-cr { color: #28a745 !important; font-weight: bold; }
        .balance-dr { color: #dc3545 !important; font-weight: bold; }
        .balance-cr { color: #28a745 !important; font-weight: bold; }
    </style>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Vendors</h4>
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
                                @php
                                    // Use the actual balance from the last transaction
                                    $lastTransaction = $vendor->vendorTransactions()
                                        ->where('approval_status', 'approved')
                                        ->orderBy('date', 'DESC')
                                        ->orderBy('id', 'DESC')
                                        ->first();
                                    
                                    $balance = $lastTransaction ? floatval($lastTransaction->current_balance ?? 0) : 0;
                                    $balanceClass = $balance >= 0 ? 'bg-label-danger' : 'bg-label-success';
                                    $balanceLabel = $balance >= 0 ? 'DR' : 'CR';
                                @endphp
                                <span class="badge {{ $balanceClass }}">
                                    PKR {{ number_format(abs($balance), 0) }} {{ $balanceLabel }}
                                </span>
                                <small class="text-muted">(Approved Only)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Bank Statement Section -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendors Bank Statement</h5>
                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2">
                        <input type="date" name="trans_from" class="form-control" value="{{ $trans_from }}">
                        <input type="date" name="trans_to" class="form-control" value="{{ $trans_to }}">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        @if (request()->has('trans_from') || request()->has('trans_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid]) }}"
                                class="btn btn-sm btn-secondary">Clear</a>
                        @endif
                    </form>
                    
                    <a href="{{ route('vendors.bank-statement', ['uuid' => $vendor->uuid, 'trans_from' => $trans_from, 'trans_to' => $trans_to]) }}" 
                        class="btn btn-info ms-2" target="_blank">
                        <i class='bx bx-download'></i> Report
                    </a>
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
                        @forelse ($vendorTransactions as $transaction)
                            @php
                                $type = strtolower($transaction->type ?? '');
                                
                                // Skip bill type transactions - Don't display purchase bills in bank statement
                                if ($type == 'bill') {
                                    continue;
                                }
                                
                                $transactionType = $transaction->transaction_type ?? '';
                                $amount = floatval($transaction->amount ?? 0);
                                $description = $transaction->description ?? '';
                                $approvalStatus = $transaction->approval_status ?? 'pending';
                                $isApproved = ($approvalStatus == 'approved');
                                
                                // Use the current_balance from database
                                $currentBalance = floatval($transaction->current_balance ?? 0);
                                
                                $displayType = '';
                                $badgeClass = '';
                                $badgeText = '';
                                $descriptionText = '';
                                $amountClass = '';
                                $transactionTypeDisplay = '';
                                $isGeneralEntry = false;
                                $isOpeningBalance = false;
                                $statusBadge = '';
                                $statusText = '';
                                
                                // Determine if Opening Balance or General Entry
                                if ($type == 'balance') {
                                    if (stripos($description, 'Opening Balance') !== false) {
                                        $isOpeningBalance = true;
                                    } else {
                                        $isGeneralEntry = true;
                                    }
                                } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                                    $isGeneralEntry = true;
                                }
                                
                                // Status Badge
                                if ($isApproved) {
                                    $statusBadge = 'badge-approved';
                                    $statusText = 'Approved';
                                } else {
                                    $statusBadge = 'badge-pending';
                                    $statusText = 'Pending';
                                }
                                
                                // DR/CR LOGIC FOR VENDOR
                                if ($type == 'payment') {
                                    $displayType = 'Payment Sent';
                                    $badgeClass = 'badge-payment';
                                    $badgeText = 'PAYMENT';
                                    $amountClass = 'text-dr';
                                    $transactionTypeDisplay = 'DR';
                                    $descriptionText = $description ?: 'Payment to vendor';
                                    if (isset($transaction->send_via) && $transaction->send_via) {
                                        $descriptionText .= ' via ' . ucfirst($transaction->send_via);
                                    }
                                } elseif ($isOpeningBalance) {
                                    $displayType = 'Opening Balance';
                                    $badgeClass = 'badge-balance';
                                    $badgeText = 'OPENING';
                                    $amountClass = 'text-info';
                                    $transactionTypeDisplay = $amount > 0 ? 'CR' : 'DR';
                                    $descriptionText = 'Opening Balance';
                                } elseif ($isGeneralEntry) {
                                    $displayType = 'General Entry';
                                    $badgeClass = 'badge-general';
                                    $badgeText = 'GENERAL';
                                    $amountClass = 'text-primary';
                                    
                                    if ($transactionType == 'credit') {
                                        $transactionTypeDisplay = 'CR';
                                    } elseif ($transactionType == 'debit') {
                                        $transactionTypeDisplay = 'DR';
                                    } else {
                                        $transactionTypeDisplay = $amount > 0 ? 'CR' : 'DR';
                                    }
                                    $descriptionText = $description ?: 'General Entry';
                                } elseif ($type == 'return') {
                                    $displayType = 'Return';
                                    $badgeClass = 'badge-return';
                                    $badgeText = 'RETURN';
                                    $amountClass = 'text-dr';
                                    $transactionTypeDisplay = 'DR';
                                    $descriptionText = $description ?: 'Product return';
                                } elseif ($type == 'credit') {
                                    $displayType = 'Credit Entry';
                                    $badgeClass = 'badge-credit';
                                    $badgeText = 'CREDIT';
                                    $amountClass = 'text-cr';
                                    $transactionTypeDisplay = 'CR';
                                    $descriptionText = $description ?: 'Credit transaction';
                                } elseif ($type == 'debit') {
                                    $displayType = 'Debit Entry';
                                    $badgeClass = 'badge-debit';
                                    $badgeText = 'DEBIT';
                                    $amountClass = 'text-dr';
                                    $transactionTypeDisplay = 'DR';
                                    $descriptionText = $description ?: 'Debit transaction';
                                } else {
                                    $displayType = ucfirst($type) ?: 'Entry';
                                    $badgeClass = 'badge-general';
                                    $badgeText = strtoupper($type) ?: 'ENTRY';
                                    $amountClass = 'text-secondary';
                                    $transactionTypeDisplay = ($transactionType == 'credit' || $amount > 0) ? 'CR' : 'DR';
                                    $descriptionText = $description ?: $displayType;
                                }
                                
                                // Current Balance DR/CR
                                $drCrDisplay = $currentBalance >= 0 ? 'DR' : 'CR';
                                $balanceClass = $currentBalance < 0 ? 'balance-cr' : 'balance-dr';
                                $typeBadgeClass = $transactionTypeDisplay == 'DR' ? 'badge-dr' : 'badge-cr';
                            @endphp
                            <tr>
                                <td>
                                    {{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    <span class="badge-type {{ $badgeClass }}">{{ $badgeText }}</span>
                                    <strong>{{ $displayType }}</strong>
                                    @if($descriptionText && $descriptionText != $displayType)
                                        <br><small class="text-muted">{{ $descriptionText }}</small>
                                    @endif
                                    @if(isset($transaction->send_via) && $transaction->send_via)
                                        <br><small class="text-muted">via {{ ucfirst($transaction->send_via) }}</small>
                                    @endif
                                    <br>
                                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                    @if(!$isApproved)
                                        <span class="text-warning" style="font-size: 10px;">(Not affecting balance)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $amountClass }}">
                                        PKR {{ number_format(abs($amount), 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $typeBadgeClass }}">{{ $transactionTypeDisplay }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $balanceClass }}">
                                        PKR {{ number_format(abs($currentBalance), 0) }}
                                        <small>{{ $drCrDisplay }}</small>
                                    </span>
                                    @if(!$isApproved)
                                        <br><small class="text-warning">(Pending - Not affecting balance)</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($type == 'payment')
                                        <a href="{{ route('vendors.payment-details', $transaction->uuid) }}"
                                            class="btn btn-sm btn-outline-info" title="View Payment Details">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    @elseif ($isOpeningBalance)
                                        <span class="text-muted" title="Opening Balance Entry">
                                            <i class='bx bx-info-circle fs-5'></i>
                                        </span>
                                    @elseif ($isGeneralEntry)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="View General Entry Details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#generalEntryModal"
                                                data-entry-date="{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}"
                                                data-entry-amount="{{ number_format(abs($amount), 0) }}"
                                                data-entry-type="{{ $transactionTypeDisplay }}"
                                                data-entry-description="{{ $descriptionText }}"
                                                data-entry-status="{{ $statusText }}"
                                                data-entry-balance="{{ number_format(abs($currentBalance), 0) }} {{ $drCrDisplay }}">
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
                                    <p class="text-muted mt-2">No transactions found for this vendor.</p>
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
                        <div class="col-4 fw-bold">Status:</div>
                        <div class="col-8" id="modalEntryStatus">-</div>
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
                var entryStatus = button.getAttribute('data-entry-status');
                var entryBalance = button.getAttribute('data-entry-balance');
                
                var modalDate = generalEntryModal.querySelector('#modalEntryDate');
                var modalAmount = generalEntryModal.querySelector('#modalEntryAmount');
                var modalType = generalEntryModal.querySelector('#modalEntryType');
                var modalDescription = generalEntryModal.querySelector('#modalEntryDescription');
                var modalStatus = generalEntryModal.querySelector('#modalEntryStatus');
                var modalBalance = generalEntryModal.querySelector('#modalEntryBalance');
                
                if (modalDate) modalDate.textContent = entryDate;
                if (modalAmount) modalAmount.textContent = 'PKR ' + entryAmount;
                if (modalType) {
                    modalType.textContent = entryType;
                    if (entryType === 'DR') {
                        modalType.className = 'badge bg-danger';
                    } else {
                        modalType.className = 'badge bg-success';
                    }
                }
                if (modalDescription) modalDescription.textContent = entryDescription;
                if (modalStatus) {
                    modalStatus.textContent = entryStatus;
                    if (entryStatus === 'Approved') {
                        modalStatus.className = 'badge bg-success';
                    } else {
                        modalStatus.className = 'badge bg-warning';
                    }
                }
                if (modalBalance) modalBalance.textContent = 'PKR ' + entryBalance;
            });
        }
    });
</script>
@endpush