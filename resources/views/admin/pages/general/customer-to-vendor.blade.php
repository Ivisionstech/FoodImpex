@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('general-transactions.index') }}">General Transactions</a> /
            Customer to Vendor Transfer
        </h4>

        <div class="row">
            <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-transfer-alt me-2"></i>
                            Customer to Vendor Transfer
                        </h5>
                    </div>
                    <div class="card-body">
                        <form class="ajax-form" action="{{ route('general-transactions.customer-to-vendor.store') }}"
                            method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="customer_id">Select Customer <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('customer_id') is-invalid @enderror" id="customer_id"
                                        name="customer_id" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" data-balance="{{ $customer->balance }}">
                                                {{ $customer->name }} - Balance: PKR
                                                {{ number_format($customer->balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="customer_id-error"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="vendor_id">Select Vendor <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('vendor_id') is-invalid @enderror" id="vendor_id"
                                        name="vendor_id" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" data-balance="{{ $vendor->balance }}">
                                                {{ $vendor->company_name }} - Balance: PKR
                                                {{ number_format($vendor->balance, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="vendor_id-error"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="amount">Transfer Amount <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                        id="amount" name="amount" step="0.01" min="0.01" required />
                                    <div class="invalid-feedback" id="amount-error"></div>
                                    <small class="text-muted">Available balance will be shown after selecting
                                        customer</small>
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
                                                <strong>Customer Balance:</strong> <span id="customer-balance">PKR
                                                    0.00</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Vendor Balance:</strong> <span id="vendor-balance">PKR 0.00</span>
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

    <script>
        $(document).ready(function() {
            // Update balance display when customer or vendor is selected
            $('#customer_id, #vendor_id').on('change', function() {
                updateBalanceDisplay();
            });

            function updateBalanceDisplay() {
                const customerSelect = $('#customer_id');
                const vendorSelect = $('#vendor_id');
                const customerBalance = customerSelect.find('option:selected').data('balance') || 0;
                const vendorBalance = vendorSelect.find('option:selected').data('balance') || 0;

                if (customerSelect.val() && vendorSelect.val()) {
                    $('#customer-balance').text('PKR ' + parseFloat(customerBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#vendor-balance').text('PKR ' + parseFloat(vendorBalance).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#balance-info').show();
                } else {
                    $('#balance-info').hide();
                }
            }

            // Validate amount against customer balance
            $('#amount').on('input', function() {
                const amount = parseFloat($(this).val()) || 0;
                const customerBalance = parseFloat($('#customer_id').find('option:selected').data(
                    'balance')) || 0;

                if (amount > customerBalance) {
                    $(this).addClass('is-invalid');
                    $('#amount-error').text('Amount cannot exceed customer balance');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#amount-error').text('');
                }
            });
        });
    </script>
@endsection
