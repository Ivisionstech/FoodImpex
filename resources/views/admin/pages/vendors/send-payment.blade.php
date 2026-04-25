@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('vendors.list') }}">Vendors</a> /
            Send Payment
        </h4>

        {{-- Vendor Details Card --}}
        <div class="card mb-4">
            <h5 class="card-header">Vendor Details</h5>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong> Company Name:</strong><a
                            href="{{ route('vendors.view', $vendor->uuid) }}"> {{ $vendor->company_name }}</a></div>
                    <div class="col-md-6"><strong>Person Name:</strong> {{ $vendor->person_name }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Phone:</strong> {{ $vendor->phone }}</div>
                    <div class="col-md-6">
                        <strong>Balance:</strong>
                        <span class="badge bg-label-primary">PKR {{ number_format($vendor->balance, 2) }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><strong>Address:</strong> {{ $vendor->address }}</div>
                </div>
            </div>
        </div>

        {{-- Send Payment Form Card --}}
        <div class="card">
            <h5 class="card-header">Send Payment</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('vendors.send-payment.store', $vendor->uuid) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                            <div class="invalid-feedback" id="date-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                                name="amount" value="{{ old('amount') }}" required />
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="send_via">Send Via <span class="text-danger">*</span></label>
                            <select name="send_via" id="send_via"
                                class="form-control @error('send_via') is-invalid @enderror" required>
                                <option value="">Select Send Via</option>
                                <option value="bank">Bank</option>
                                <option value="cash">Cash</option>
                            </select>
                            <div class="invalid-feedback" id="send_via-error"></div>
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



                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="receipt_images">Receipt Images </label>
                            <input type="file" class="form-control @error('receipt_images') is-invalid @enderror"
                                id="receipt_images" multiple name="receipt_images[]" value="{{ old('receipt_images') }}"
                                accept="image/*" />
                            <div class="invalid-feedback" id="receipt_images-error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary">Send Payment</button>
                            <a href="{{ route('vendors.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sendViaSelect = document.getElementById('send_via');
                const bankSection = document.getElementById('bank_section');
                const bankSelect = document.getElementById('bank_id');
                const amountInput = document.getElementById('amount');
                const form = document.querySelector('.ajax-form');

                function updatePaymentMethod() {
                    const selectedValue = sendViaSelect.value;

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
                sendViaSelect.addEventListener('change', updatePaymentMethod);

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

                    // Validate send_via selection
                    if (!sendViaSelect.value) {
                        sendViaSelect.classList.add('is-invalid');
                        document.getElementById('send_via-error').textContent =
                            'Please select a payment method.';
                        isValid = false;
                    }

                    // Validate bank selection if bank is selected
                    if (sendViaSelect.value === 'bank' && !bankSelect.value) {
                        bankSelect.classList.add('is-invalid');
                        document.getElementById('bank_id-error').textContent = 'Please select a bank.';
                        isValid = false;
                    }

                    // Validate main amount
                    if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                        amountInput.classList.add('is-invalid');
                        document.getElementById('amount-error').textContent = 'Please enter a valid amount.';
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
