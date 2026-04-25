@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('general-transactions.index') }}">General Transactions</a> /
            General Entry
        </h4>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-transfer-alt me-2"></i>
                            General Entry
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('general-transactions.general-entry.store') }}"
                            method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="credit_id">Credit (Money Out) <span class="text-danger">*</span></label>
                                    <select class="form-control @error('credit_id') is-invalid @enderror" id="credit_id"
                                        name="credit_id" required>
                                        <option value="">Select Credit Account</option>
                                        @if(isset($customers) && $customers->count() > 0)
                                        <optgroup label="CUSTOMERS">
                                            @foreach ($customers as $customer)
                                                <option value="customer_{{ $customer->id }}" data-name="{{ $customer->name ?? 'N/A' }}" data-type="Customer" data-balance="{{ $customer->balance ?? 0 }}">
                                                    {{ $customer->name ?? 'N/A' }} (Customer) - Bal: PKR {{ number_format($customer->balance ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif

                                        @if(isset($vendors) && $vendors->count() > 0)
                                        <optgroup label="VENDORS">
                                            @foreach ($vendors as $vendor)
                                                <option value="vendor_{{ $vendor->id }}" data-name="{{ $vendor->company_name ?? 'N/A' }}" data-type="Vendor" data-balance="{{ $vendor->balance ?? 0 }}">
                                                    {{ $vendor->company_name ?? 'N/A' }} (Vendor) - Bal: PKR {{ number_format($vendor->balance ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif

                                        @if(isset($banks) && $banks->count() > 0)
                                        <optgroup label="BANKS">
                                            @foreach ($banks as $bank)
                                                @php
                                                    $balance = property_exists($bank, 'account_balance') ? $bank->account_balance : (property_exists($bank, 'balance') ? $bank->balance : 0);
                                                @endphp
                                                <option value="bank_{{ $bank->id }}" data-name="{{ $bank->name ?? 'N/A' }}" data-type="Bank" data-balance="{{ $balance }}">
                                                    {{ $bank->name ?? 'N/A' }} (Bank) - Bal: PKR {{ number_format($balance, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                    </select>
                                    @error('credit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="debit_id">Debit (Money In) <span class="text-danger">*</span></label>
                                    <select class="form-control @error('debit_id') is-invalid @enderror" id="debit_id"
                                        name="debit_id" required>
                                        <option value="">Select Debit Account</option>
                                        @if(isset($customers) && $customers->count() > 0)
                                        <optgroup label="CUSTOMERS">
                                            @foreach ($customers as $customer)
                                                <option value="customer_{{ $customer->id }}" data-name="{{ $customer->name ?? 'N/A' }}" data-type="Customer" data-balance="{{ $customer->balance ?? 0 }}">
                                                    {{ $customer->name ?? 'N/A' }} (Customer) - Bal: PKR {{ number_format($customer->balance ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif

                                        @if(isset($vendors) && $vendors->count() > 0)
                                        <optgroup label="VENDORS">
                                            @foreach ($vendors as $vendor)
                                                <option value="vendor_{{ $vendor->id }}" data-name="{{ $vendor->company_name ?? 'N/A' }}" data-type="Vendor" data-balance="{{ $vendor->balance ?? 0 }}">
                                                    {{ $vendor->company_name ?? 'N/A' }} (Vendor) - Bal: PKR {{ number_format($vendor->balance ?? 0, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif

                                        @if(isset($banks) && $banks->count() > 0)
                                        <optgroup label="BANKS">
                                            @foreach ($banks as $bank)
                                                @php
                                                    $balance = property_exists($bank, 'account_balance') ? $bank->account_balance : (property_exists($bank, 'balance') ? $bank->balance : 0);
                                                @endphp
                                                <option value="bank_{{ $bank->id }}" data-name="{{ $bank->name ?? 'N/A' }}" data-type="Bank" data-balance="{{ $balance }}">
                                                    {{ $bank->name ?? 'N/A' }} (Bank) - Bal: PKR {{ number_format($balance, 2) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        @endif
                                    </select>
                                    @error('debit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="amount">Transfer Amount <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required />
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Available balance will be shown after selecting credit account</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="transaction_date">Transaction Date <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('transaction_date') is-invalid @enderror"
                                        id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d\TH:i')) }}"
                                        required />
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label" for="description">Description (Optional)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3" placeholder="Enter description for this transfer">{{ old('description') }}</textarea>
                                    <small class="text-muted" id="autoDescriptionHint">A description will be automatically generated based on selected accounts</small>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Preview of how this entry will appear -->
                            <div class="row mb-3" id="preview-section" style="display: none;">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-header py-2">
                                            <h6 class="mb-0">Preview</h6>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Description:</strong> <span id="preview-description"></span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>From (Credit):</strong> <span id="preview-credit"></span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>To (Debit):</strong> <span id="preview-debit"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance Display -->
                            <div class="row mb-3" id="balance-info" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Balance Information</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Credit Account Balance:</strong> <span id="credit-balance">PKR 0.00</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Debit Account Balance:</strong> <span id="debit-balance">PKR 0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2" id="submitButton">
                                        <i class="bx bx-transfer-alt me-1"></i>
                                        Process Transfer
                                    </button>
                                    <a href="{{ route('general-transactions.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-x me-1"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Update balance display when credit or debit is selected
            $('#credit_id, #debit_id, #amount').on('change keyup', function() {
                updateBalanceDisplay();
                validateAmount();
                updatePreview();
            });

            function getAccountName(selectElement) {
                const selected = selectElement.find('option:selected');
                const name = selected.data('name') || 'Unknown';
                const type = selected.data('type') || 'Account';
                return name;
            }

            function updatePreview() {
                const creditSelect = $('#credit_id');
                const debitSelect = $('#debit_id');
                const creditName = getAccountName(creditSelect);
                const debitName = getAccountName(debitSelect);
                const amount = parseFloat($('#amount').val()) || 0;
                const description = $('#description').val();

                if (creditSelect.val() && debitSelect.val()) {
                    $('#preview-credit').text(creditName);
                    $('#preview-debit').text(debitName);
                    
                    // Generate preview description
                    let previewDesc = description ? description + ' - ' : '';
                    previewDesc += 'Transfer from ' + creditName + ' to ' + debitName;
                    if (amount > 0) {
                        previewDesc += ' (PKR ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')';
                    }
                    $('#preview-description').text(previewDesc);
                    
                    $('#preview-section').show();
                } else {
                    $('#preview-section').hide();
                }
            }

            function updateBalanceDisplay() {
                const creditSelect = $('#credit_id');
                const debitSelect = $('#debit_id');
                const creditBalance = creditSelect.find('option:selected').data('balance') || 0;
                const debitBalance = debitSelect.find('option:selected').data('balance') || 0;

                if (creditSelect.val() && debitSelect.val()) {
                    $('#credit-balance').text('PKR ' + parseFloat(creditBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#debit-balance').text('PKR ' + parseFloat(debitBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#balance-info').show();
                } else {
                    $('#balance-info').hide();
                }
            }

            // Validate amount against credit account balance
            $('#amount').on('input', function() {
                validateAmount();
            });

            function validateAmount() {
                const amount = parseFloat($('#amount').val()) || 0;
                const creditBalance = parseFloat($('#credit_id').find('option:selected').data('balance')) || 0;

                if (amount > creditBalance) {
                    $('#amount').addClass('is-invalid');
                    if ($('#amount-error').length === 0) {
                        $('#amount').after('<div id="amount-error" class="invalid-feedback">Amount cannot exceed credit account balance (PKR ' + creditBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')</div>');
                    } else {
                        $('#amount-error').text('Amount cannot exceed credit account balance (PKR ' + creditBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ')');
                    }
                    return false;
                } else {
                    $('#amount').removeClass('is-invalid');
                    $('#amount-error').remove();
                    return true;
                }
            }

            // Prevent selecting same account for credit and debit
            $('#credit_id, #debit_id').on('change', function() {
                const creditVal = $('#credit_id').val();
                const debitVal = $('#debit_id').val();
                
                if (creditVal && debitVal && creditVal === debitVal) {
                    alert('Credit and Debit accounts cannot be the same');
                    $(this).val('').trigger('change');
                }
            });

            // Auto-generate description hint
            $('#description').on('input', function() {
                updatePreview();
            });
        });
    </script>
@endsection