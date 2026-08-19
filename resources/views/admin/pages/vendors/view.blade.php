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
        .badge-batch { background-color: #6f42c1; color: white; }
        .text-dr { color: #dc3545 !important; font-weight: bold; }
        .text-cr { color: #28a745 !important; font-weight: bold; }
        .balance-dr { color: #dc3545 !important; font-weight: bold; }
        .balance-cr { color: #28a745 !important; font-weight: bold; }
        
        /* Batch Entry Styles */
        .batch-entry-row {
            background-color: #f8f0ff !important;
            border-left: 3px solid #6f42c1 !important;
        }
        .batch-entry-row td {
            background-color: #f8f0ff !important;
        }
        .batch-icon {
            color: #6f42c1;
            font-size: 14px;
            margin-right: 5px;
        }
        .badge-batch {
            background-color: #6f42c1 !important;
            color: white !important;
        }
        
        /* Batch Details Collapse */
        .batch-details {
            background-color: #faf5ff;
            border-radius: 8px;
            padding: 10px;
            margin-top: 5px;
        }
        .batch-details table {
            margin-bottom: 0;
            font-size: 13px;
        }
        .batch-details table td {
            padding: 4px 8px;
        }
        .batch-details .batch-total {
            background-color: #e8d9ff;
            font-weight: bold;
        }
        
        /* Modal Improvements */
        .modal-body .detail-row {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .modal-body .detail-row:last-child {
            border-bottom: none;
        }
        .modal-body .detail-label {
            font-weight: 600;
            color: #6c757d;
        }
        .modal-body .detail-value {
            font-weight: 500;
        }

        .btn-outline-purple {
            border-color: #6f42c1;
            color: #6f42c1;
        }
        .btn-outline-purple:hover {
            background-color: #6f42c1;
            color: white;
        }
    </style>
    
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span> Vendors
        </h4>
        
        <!-- Vendor Personal Details Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Personal Details</h5>
                <div>
                    <a href="{{ route('vendors.list') }}" class="btn btn-sm btn-outline-secondary">
                        <i class='bx bx-arrow-back'></i> Back to List
                    </a>
                    <a href="{{ route('vendors.edit', $vendor->uuid) }}" class="btn btn-sm btn-primary">
                        <i class='bx bx-edit'></i> Edit
                    </a>
                </div>
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
                                    // Negative = DR, Positive = CR
                                    $balanceClass = $balance < 0 ? 'bg-label-danger' : 'bg-label-success';
                                    $balanceLabel = $balance < 0 ? 'DR' : 'CR';
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

        <!-- Vendor Ledger Section -->
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Vendor Ledger</h5>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2 flex-wrap">
                        <input type="date" name="trans_from" class="form-control form-control-sm" value="{{ $trans_from }}" style="width: 150px;">
                        <input type="date" name="trans_to" class="form-control form-control-sm" value="{{ $trans_to }}" style="width: 150px;">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        @if (request()->has('trans_from') || request()->has('trans_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid]) }}"
                                class="btn btn-sm btn-secondary">Clear</a>
                        @endif
                    </form>
                    
                    <a href="{{ route('vendors.bank-statement', ['uuid' => $vendor->uuid, 'trans_from' => $trans_from, 'trans_to' => $trans_to]) }}" 
                        class="btn btn-sm btn-info" target="_blank">
                        <i class='bx bx-show'></i> View Ledger
                    </a>
                    
                    <a href="{{ route('vendors.download-bank-statement', ['uuid' => $vendor->uuid, 'trans_from' => $trans_from, 'trans_to' => $trans_to]) }}" 
                        class="btn btn-sm btn-success">
                        <i class='bx bx-download'></i> Download PDF
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
                                
                                // Skip bill type transactions
                                if ($type == 'bill') {
                                    continue;
                                }
                                
                                $transactionType = $transaction->transaction_type ?? '';
                                $amount = floatval($transaction->amount ?? 0);
                                $description = $transaction->description ?? '';
                                $approvalStatus = $transaction->approval_status ?? 'pending';
                                $isApproved = ($approvalStatus == 'approved');
                                
                                // Use the current_balance from database (this is the signed value)
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
                                $hasBatch = false;
                                $batchCount = 0;
                                $batchEntries = [];
                                
                                // Determine if Opening Balance or General Entry
                                if ($type == 'balance') {
                                    if (stripos($description, 'Opening Balance') !== false) {
                                        $isOpeningBalance = true;
                                        
                                        // For display purposes, use transaction_type from database
                                        if ($transactionType == 'credit') {
                                            $transactionTypeDisplay = 'CR';
                                            $amountClass = 'text-cr';
                                            $displayType = 'Opening Balance (Credit)';
                                            $badgeClass = 'badge-balance';
                                            $badgeText = 'OPENING CR';
                                            $descriptionText = 'Opening Balance - Vendor owes us';
                                        } else {
                                            $transactionTypeDisplay = 'DR';
                                            $amountClass = 'text-dr';
                                            $displayType = 'Opening Balance (Debit)';
                                            $badgeClass = 'badge-balance';
                                            $badgeText = 'OPENING DR';
                                            $descriptionText = 'Opening Balance - We owe vendor';
                                        }
                                    } else {
                                        $isGeneralEntry = true;
                                    }
                                } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                                    $isGeneralEntry = true;
                                }
                                
                                // Check if this general entry has a batch
                                if ($isGeneralEntry && isset($transaction->batch_id) && $transaction->batch_id) {
                                    $batchId = $transaction->batch_id;
                                    $batchEntries = \App\Models\Daybook::where('batch_id', $batchId)->get();
                                    if ($batchEntries->count() > 1) {
                                        $hasBatch = true;
                                        $batchCount = $batchEntries->count();
                                    }
                                }
                                
                                // Status Badge
                                if ($isApproved) {
                                    $statusBadge = 'badge-approved';
                                    $statusText = 'Approved';
                                } else {
                                    $statusBadge = 'badge-pending';
                                    $statusText = 'Pending';
                                }
                                
                                // DR/CR LOGIC FOR OTHER TRANSACTION TYPES
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
                                } elseif ($isGeneralEntry) {
                                    $displayType = 'General Entry';
                                    $badgeClass = $hasBatch ? 'badge-batch' : 'badge-general';
                                    $badgeText = $hasBatch ? 'BATCH (' . $batchCount . ')' : 'GENERAL';
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
                                
                                // =============================================
                                // CURRENT BALANCE - Negative = DR, Positive = CR
                                // =============================================
                                $drCrDisplay = $currentBalance < 0 ? 'DR' : 'CR';
                                $balanceClass = $currentBalance < 0 ? 'balance-dr' : 'balance-cr';
                                $typeBadgeClass = $transactionTypeDisplay == 'DR' ? 'badge-dr' : 'badge-cr';
                                
                                // Row class for batch entries
                                $rowClass = $hasBatch ? 'batch-entry-row' : '';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>
                                    {{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}
                                    <div style="font-size: 9px; color: #666;">
                                        {{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('h:i A') : '' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-type {{ $badgeClass }}">{{ $badgeText }}</span>
                                    <strong>{{ $displayType }}</strong>
                                    @if($hasBatch)
                                        <span style="color: #6f42c1; font-size: 10px; margin-left: 5px;">
                                            <i class="bx bx-layer batch-icon"></i> Batch of {{ $batchCount }} entries
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-purple btn-sm ms-1" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#batchDetails{{ $transaction->id }}"
                                                style="padding: 0 6px; font-size: 10px;">
                                            <i class='bx bx-chevron-down'></i> View All
                                        </button>
                                    @endif
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
                                    
                                    <!-- Batch Details Collapse -->
                                    @if($hasBatch)
                                        <div class="collapse batch-details mt-2" id="batchDetails{{ $transaction->id }}">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Account</th>
                                                            <th>Type</th>
                                                            <th>Amount</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($batchEntries as $batchIndex => $batchEntry)
                                                            @php
                                                                $isBatchDebit = $batchEntry->debit_type && $batchEntry->debit_id;
                                                                $isBatchCredit = $batchEntry->credit_type && $batchEntry->credit_id;
                                                                
                                                                $batchAccountName = '';
                                                                $batchAccountType = '';
                                                                
                                                                if ($isBatchDebit) {
                                                                    $batchAccountType = $batchEntry->debit_type;
                                                                    if ($batchAccountType == 'customer') {
                                                                        $customer = \App\Models\Customer::find($batchEntry->debit_id);
                                                                        $batchAccountName = $customer ? $customer->name : 'Customer #' . $batchEntry->debit_id;
                                                                    } elseif ($batchAccountType == 'vendor') {
                                                                        $vendorBatch = \App\Models\Vendor::find($batchEntry->debit_id);
                                                                        $batchAccountName = $vendorBatch ? $vendorBatch->company_name : 'Vendor #' . $batchEntry->debit_id;
                                                                    } elseif ($batchAccountType == 'bank') {
                                                                        $bank = \App\Models\Bank::find($batchEntry->debit_id);
                                                                        $batchAccountName = $bank ? $bank->name : 'Bank #' . $batchEntry->debit_id;
                                                                    } elseif ($batchAccountType == 'cash') {
                                                                        $batchAccountName = 'Cash Account';
                                                                    } elseif ($batchAccountType == 'expense') {
                                                                        $expense = \App\Models\Expense::find($batchEntry->debit_id);
                                                                        $batchAccountName = $expense ? $expense->name : 'Expense #' . $batchEntry->debit_id;
                                                                    }
                                                                } elseif ($isBatchCredit) {
                                                                    $batchAccountType = $batchEntry->credit_type;
                                                                    if ($batchAccountType == 'customer') {
                                                                        $customer = \App\Models\Customer::find($batchEntry->credit_id);
                                                                        $batchAccountName = $customer ? $customer->name : 'Customer #' . $batchEntry->credit_id;
                                                                    } elseif ($batchAccountType == 'vendor') {
                                                                        $vendorBatch = \App\Models\Vendor::find($batchEntry->credit_id);
                                                                        $batchAccountName = $vendorBatch ? $vendorBatch->company_name : 'Vendor #' . $batchEntry->credit_id;
                                                                    } elseif ($batchAccountType == 'bank') {
                                                                        $bank = \App\Models\Bank::find($batchEntry->credit_id);
                                                                        $batchAccountName = $bank ? $bank->name : 'Bank #' . $batchEntry->credit_id;
                                                                    } elseif ($batchAccountType == 'cash') {
                                                                        $batchAccountName = 'Cash Account';
                                                                    } elseif ($batchAccountType == 'expense') {
                                                                        $expense = \App\Models\Expense::find($batchEntry->credit_id);
                                                                        $batchAccountName = $expense ? $expense->name : 'Expense #' . $batchEntry->credit_id;
                                                                    }
                                                                }
                                                                
                                                                $batchTypeLabel = $isBatchDebit ? 'Debit' : 'Credit';
                                                                $batchTypeClass = $isBatchDebit ? 'text-danger' : 'text-success';
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $batchIndex + 1 }}</td>
                                                                <td>
                                                                    <span class="text-capitalize">{{ $batchAccountType }}</span>
                                                                    <br><small class="text-muted">{{ $batchAccountName }}</small>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-{{ $isBatchDebit ? 'danger' : 'success' }} bg-opacity-10 text-{{ $isBatchDebit ? 'danger' : 'success' }}">
                                                                        {{ $batchTypeLabel }}
                                                                    </span>
                                                                </td>
                                                                <td class="fw-bold {{ $batchTypeClass }}">
                                                                    PKR {{ number_format($batchEntry->amount, 0) }}
                                                                </td>
                                                                <td><small>{{ $batchEntry->description ?? 'N/A' }}</small></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="batch-total">
                                                            <td colspan="3" class="text-end fw-bold">Total:</td>
                                                            <td class="fw-bold text-primary">
                                                                PKR {{ number_format($batchEntries->sum('amount'), 0) }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $amountClass }}">
                                        PKR {{ number_format(abs($amount), 0) }}
                                    </span>
                                </td>
                                <td>
                                    {{-- TYPE COLUMN - FIXED: Use actual transaction_type from database --}}
                                    @if($isOpeningBalance)
                                        @php
                                            // Get the actual transaction type from database
                                            $actualType = $transaction->transaction_type ?? '';
                                            $displayTypeLabel = strtoupper($actualType);
                                            $typeClass = $displayTypeLabel == 'DR' ? 'badge-dr' : 'badge-cr';
                                        @endphp
                                        <span class="badge {{ $typeClass }}">
                                            {{ $displayTypeLabel ?: 'CR' }}
                                        </span>
                                    @else
                                        <span class="badge {{ $typeBadgeClass }}">{{ $transactionTypeDisplay }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- CURRENT BALANCE COLUMN - Negative = DR, Positive = CR --}}
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
                                                data-entry-balance="{{ number_format(abs($currentBalance), 0) }} {{ $drCrDisplay }}"
                                                data-entry-batch="{{ $hasBatch ? 'Yes (' . $batchCount . ' entries)' : 'No' }}">
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
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value" id="modalEntryDate">-</span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Amount:</span>
                        <span class="detail-value" id="modalEntryAmount">-</span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">
                            <span id="modalEntryType" class="badge"></span>
                        </span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Description:</span>
                        <span class="detail-value" id="modalEntryDescription">-</span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" id="modalEntryStatus">-</span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Balance After:</span>
                        <span class="detail-value" id="modalEntryBalance">-</span>
                    </div>
                    <div class="detail-row d-flex justify-content-between">
                        <span class="detail-label">Batch Entry:</span>
                        <span class="detail-value" id="modalEntryBatch">-</span>
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
                var entryBatch = button.getAttribute('data-entry-batch');
                
                var modalDate = generalEntryModal.querySelector('#modalEntryDate');
                var modalAmount = generalEntryModal.querySelector('#modalEntryAmount');
                var modalType = generalEntryModal.querySelector('#modalEntryType');
                var modalDescription = generalEntryModal.querySelector('#modalEntryDescription');
                var modalStatus = generalEntryModal.querySelector('#modalEntryStatus');
                var modalBalance = generalEntryModal.querySelector('#modalEntryBalance');
                var modalBatch = generalEntryModal.querySelector('#modalEntryBatch');
                
                if (modalDate) modalDate.textContent = entryDate;
                if (modalAmount) modalAmount.textContent = 'PKR ' + entryAmount;
                if (modalType) {
                    modalType.textContent = entryType;
                    if (entryType === 'DR') {
                        modalType.className = 'badge bg-danger';
                    } else if (entryType === 'CR') {
                        modalType.className = 'badge bg-success';
                    } else {
                        modalType.className = 'badge bg-secondary';
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
                if (modalBatch) {
                    modalBatch.textContent = entryBatch;
                    if (entryBatch && entryBatch.includes('Yes')) {
                        modalBatch.style.color = '#6f42c1';
                        modalBatch.style.fontWeight = 'bold';
                    } else {
                        modalBatch.style.color = '#6c757d';
                        modalBatch.style.fontWeight = 'normal';
                    }
                }
            });
        }
    });
</script>
@endpush