@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('expenses.list') }}">Expenses</a> /
            Add Expense
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Expense</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data"
                    novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="expense_date">Expense Date <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('expense_date') is-invalid @enderror"
                                id="expense_date" name="expense_date" value="{{ now() }}" required />
                            <div class="invalid-feedback" id="expense_date-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="name">Expense Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Expense Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                                name="amount" value="{{ old('amount') }}" required />
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>
                        <div class="col-md-6 ">
                            <label class="form-label" for="payment_method">Payment Method <span
                                    class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method"
                                class="form-control @error('payment_method') is-invalid @enderror" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                            <div class="invalid-feedback" id="payment_method-error"></div>
                        </div>
                        <div class="col-md-6 mt-3" id="bank_section" style="display: none;">
                            <label class="form-label" for="bank_id">Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id"
                                class="form-control @error('bank_id') is-invalid @enderror">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="bank_id-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Create Expense</button>
                            <a href="{{ route('expenses.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const paymentMethodSelect = document.getElementById('payment_method');
                const bankSection = document.getElementById('bank_section');
                const bankSelect = document.getElementById('bank_id');
                const amountInput = document.getElementById('amount');
                const nameInput = document.getElementById('name');
                const form = document.querySelector('.ajax-form');

                function updatePaymentMethod() {
                    const selectedValue = paymentMethodSelect.value;

                    // Hide bank section by default
                    bankSection.style.display = 'none';

                    // Remove required attribute
                    bankSelect.required = false;

                    // Clear validation states
                    bankSelect.classList.remove('is-invalid');
                    document.getElementById('bank_id-error').textContent = '';

                    // Clear bank selection
                    bankSelect.value = '';

                    if (selectedValue === 'bank') {
                        bankSection.style.display = 'block';
                        bankSelect.required = true;
                    }
                    // When cash is selected, bank section remains hidden (no additional input needed)
                }

                // Event listeners
                paymentMethodSelect.addEventListener('change', updatePaymentMethod);

                // Form validation before submission
                form.addEventListener('submit', function(e) {
                    let isValid = true;

                    // Reset validation states
                    document.querySelectorAll('.is-invalid').forEach(el => {
                        el.classList.remove('is-invalid');
                    });
                    document.querySelectorAll('.invalid-feedback').forEach(el => {
                        el.textContent = '';
                    });

                    // Validate expense name
                    if (!nameInput.value.trim()) {
                        nameInput.classList.add('is-invalid');
                        document.getElementById('name-error').textContent = 'Please enter expense name.';
                        isValid = false;
                    }

                    // Validate amount
                    if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                        amountInput.classList.add('is-invalid');
                        document.getElementById('amount-error').textContent = 'Please enter a valid amount.';
                        isValid = false;
                    }

                    // Validate payment method selection
                    if (!paymentMethodSelect.value) {
                        paymentMethodSelect.classList.add('is-invalid');
                        document.getElementById('payment_method-error').textContent =
                            'Please select a payment method.';
                        isValid = false;
                    }

                    // Validate bank selection if bank is selected
                    if (paymentMethodSelect.value === 'bank' && !bankSelect.value) {
                        bankSelect.classList.add('is-invalid');
                        document.getElementById('bank_id-error').textContent = 'Please select a bank.';
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        // Scroll to first invalid field
                        const firstInvalid = document.querySelector('.is-invalid');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            firstInvalid.focus();
                        }
                    }
                });

                // Initial setup
                updatePaymentMethod();
            });
        </script>
    @endpush
@endsection
