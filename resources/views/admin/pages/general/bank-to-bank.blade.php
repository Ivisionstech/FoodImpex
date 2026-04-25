@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('general-transactions.index') }}">General Transactions</a> /
            Bank to Bank Transfer
        </h4>

        <div class="row">
            <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-building-house me-2"></i>
                            Bank to Bank Transfer
                        </h5>
                    </div>
                    <div class="card-body">
                        <form class="ajax-form" action="{{ route('general-transactions.bank-to-bank.store') }}"
                            method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="from_bank_id">From Bank <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('from_bank_id') is-invalid @enderror"
                                        id="from_bank_id" name="from_bank_id" required>
                                        <option value="">Select Source Bank</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}" data-balance="{{ $bank->account_balance }}">
                                                {{ $bank->name }} - Balance: PKR
                                                {{ number_format($bank->account_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="from_bank_id-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="to_bank_id">To Bank <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('to_bank_id') is-invalid @enderror" id="to_bank_id"
                                        name="to_bank_id" required>
                                        <option value="">Select Destination Bank</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}" data-balance="{{ $bank->account_balance }}">
                                                {{ $bank->name }} - Balance: PKR
                                                {{ number_format($bank->account_balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="to_bank_id-error"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="amount">Transfer Amount <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        id="amount" name="amount" step="0.01" min="0.01" required />
                                    <div class="invalid-feedback" id="amount-error"></div>
                                    <small class="text-muted">Available balance will be shown after selecting source
                                        bank</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="transaction_date">Transaction Date <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('transaction_date') is-invalid @enderror"
                                        id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}"
                                        required />
                                    <div class="invalid-feedback" id="transaction_date-error"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label" for="description">Description (Optional)</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3" placeholder="Enter description for this transfer"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                </div>
                            </div>

                            <!-- Balance Display -->
                            <div class="row mb-3" id="balance-info" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Balance Information</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Source Bank Balance:</strong> <span id="from-bank-balance">PKR
                                                    0.00</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Destination Bank Balance:</strong> <span id="to-bank-balance">PKR
                                                    0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success me-2" id="submitButton">
                                        <i class="bx bx-building-house me-1"></i>
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

    <script>
        $(document).ready(function() {
            // Update balance display when banks are selected
            $('#from_bank_id, #to_bank_id').on('change', function() {
                updateBalanceDisplay();
                validateBankSelection();
            });

            function updateBalanceDisplay() {
                const fromBankSelect = $('#from_bank_id');
                const toBankSelect = $('#to_bank_id');
                const fromBankBalance = fromBankSelect.find('option:selected').data('balance') || 0;
                const toBankBalance = toBankSelect.find('option:selected').data('balance') || 0;

                if (fromBankSelect.val() && toBankSelect.val()) {
                    $('#from-bank-balance').text('PKR ' + parseFloat(fromBankBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#to-bank-balance').text('PKR ' + parseFloat(toBankBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#balance-info').show();
                } else {
                    $('#balance-info').hide();
                }
            }

            function validateBankSelection() {
                const fromBankId = $('#from_bank_id').val();
                const toBankId = $('#to_bank_id').val();

                if (fromBankId && toBankId && fromBankId === toBankId) {
                    $('#to_bank_id').addClass('is-invalid');
                    $('#to_bank_id-error').text('Destination bank must be different from source bank');
                } else {
                    $('#to_bank_id').removeClass('is-invalid');
                    $('#to_bank_id-error').text('');
                }
            }

            // Validate amount against source bank balance
            $('#amount').on('input', function() {
                const amount = parseFloat($(this).val()) || 0;
                const fromBankBalance = parseFloat($('#from_bank_id').find('option:selected').data(
                    'balance')) || 0;

                if (amount > fromBankBalance) {
                    $(this).addClass('is-invalid');
                    $('#amount-error').text('Amount cannot exceed source bank balance');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#amount-error').text('');
                }
            });
        });
    </script>
@endsection
