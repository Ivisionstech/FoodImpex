@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('general-transactions.index') }}">General Transactions</a> /
            Bank Deposit
        </h4>

        <div class="row">
            <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-wallet me-2"></i>
                            Deposit in Bank
                        </h5>
                    </div>
                    <div class="card-body">
                        <form class="ajax-form" action="{{ route('general-transactions.bank-deposit.store') }}"
                            method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="bank_id">Select Bank <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('bank_id') is-invalid @enderror" id="bank_id"
                                        name="bank_id" required>
                                        <option value="">Select Bank</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}" data-balance="{{ $bank->account_balance }}">
                                                {{ $bank->name }} - Balance: PKR
                                                {{ number_format($bank->account_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="bank_id-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="amount">Deposit Amount <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        id="amount" name="amount" step="0.01" min="0.01" required />
                                    <div class="invalid-feedback" id="amount-error"></div>
                                    <small class="text-muted">Available cash: PKR
                                        {{ $cash ? number_format($cash->balance, 2) : '0.00' }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="transaction_date">Transaction Date <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('transaction_date') is-invalid @enderror"
                                        id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}"
                                        required />
                                    <div class="invalid-feedback" id="transaction_date-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Current Cash Balance</label>
                                    <input type="text" class="form-control"
                                        value="PKR {{ $cash ? number_format($cash->balance, 2) : '0.00' }}" readonly />
                                    <small class="text-muted">Cash balance will decrease after deposit</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label" for="description">Description (Optional)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3" placeholder="Enter description for this deposit"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                </div>
                            </div>

                            <!-- Balance Display -->
                            <div class="row mb-3" id="balance-info" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Transaction Summary</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Current Cash:</strong> <span id="current-cash">PKR
                                                    {{ $cash ? number_format($cash->balance, 2) : '0.00' }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Bank Balance:</strong> <span id="bank-balance">PKR 0.00</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Bank After Deposit:</strong> <span id="bank-after">PKR 0.00</span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Cash After Deposit:</strong> <span id="cash-after">PKR
                                                    {{ $cash ? number_format($cash->balance, 2) : '0.00' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning for insufficient cash -->
                            @if (!$cash || $cash->balance <= 0)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="bx bx-error-circle me-2"></i>
                                            <strong>Warning:</strong> No cash available for deposit. Please ensure you have
                                            sufficient cash balance.
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-info me-2" id="submitButton"
                                        {{ !$cash || $cash->balance <= 0 ? 'disabled' : '' }}>
                                        <i class="bx bx-wallet me-1"></i>
                                        Process Deposit
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

    <script>
        $(document).ready(function() {
            const currentCashBalance = {{ $cash ? $cash->balance : 0 }};

            // Update balance display when bank is selected
            $('#bank_id').on('change', function() {
                updateBalanceDisplay();
            });

            $('#amount').on('input', function() {
                updateBalanceDisplay();
                validateAmount();
            });

            function updateBalanceDisplay() {
                const bankSelect = $('#bank_id');
                const bankBalance = bankSelect.find('option:selected').data('balance') || 0;
                const amount = parseFloat($('#amount').val()) || 0;

                if (bankSelect.val()) {
                    $('#current-cash').text('PKR ' + parseFloat(currentCashBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#bank-balance').text('PKR ' + parseFloat(bankBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    const bankAfter = bankBalance + amount;
                    const cashAfter = currentCashBalance - amount;

                    $('#bank-after').text('PKR ' + parseFloat(bankAfter).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#cash-after').text('PKR ' + parseFloat(cashAfter).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));

                    $('#balance-info').show();
                } else {
                    $('#balance-info').hide();
                }
            }

            function validateAmount() {
                const amount = parseFloat($('#amount').val()) || 0;

                if (amount > currentCashBalance) {
                    $('#amount').addClass('is-invalid');
                    $('#amount-error').text('Amount cannot exceed available cash balance');
                    $('#submitButton').prop('disabled', true);
                } else {
                    $('#amount').removeClass('is-invalid');
                    $('#amount-error').text('');
                    $('#submitButton').prop('disabled', false);
                }
            }
        });
    </script>
@endsection
