@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('vendors.list') }}">Vendors</a> /
            Send General Payment
        </h4>

        <div class="card">
            <h5 class="card-header">Send Payment to Vendor</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('vendors.payments.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <!-- Vendor Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold" for="vendor_id">Select Vendor <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">-- Choose Vendor --</option>
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->uuid }}">
                                        {{ $v->company_name }} (Current Bal: PKR {{ number_format($v->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="vendor_id-error"></div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                            <div class="invalid-feedback" id="date-error"></div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount"
                                name="amount" placeholder="0.00" required />
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>

                        <!-- Send Via -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="send_via">Send Via <span class="text-danger">*</span></label>
                            <select name="send_via" id="send_via"
                                class="form-select @error('send_via') is-invalid @enderror" required>
                                <option value="">Select Payment Method</option>
                                <option value="bank">Bank Account</option>
                                <option value="cash">Cash in Hand</option>
                            </select>
                            <div class="invalid-feedback" id="send_via-error"></div>
                        </div>

                        <!-- Bank Selection (Hidden by default) -->
                        <div class="col-md-6 mt-3" id="bank_section" style="display: none;">
                            <label class="form-label" for="bank_id">Select Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id"
                                class="form-select @error('bank_id') is-invalid @enderror">
                                <option value="">Choose Bank...</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }} (Bal: {{ number_format($bank->account_balance, 2) }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="bank_id-error"></div>
                        </div>

                        <!-- Receipt Images -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="receipt_images">Upload Receipts (Multiple)</label>
                            <input type="file" class="form-control @error('receipt_images') is-invalid @enderror"
                                id="receipt_images" multiple name="receipt_images[]" accept="image/*" />
                            <div class="invalid-feedback" id="receipt_images-error"></div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="description">Description / Remarks</label>
                            <input type="text" class="form-control" name="description" placeholder="e.g. Cheque #1234 or Bank Ref">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary px-5">Process Payment</button>
                            <a href="{{ route('vendors.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sendViaSelect = document.getElementById('send_via');
            const bankSection = document.getElementById('bank_section');
            const bankSelect = document.getElementById('bank_id');

            // Toggle Bank Section based on selection
            sendViaSelect.addEventListener('change', function() {
                if (this.value === 'bank') {
                    bankSection.style.display = 'block';
                    bankSelect.required = true;
                } else {
                    bankSection.style.display = 'none';
                    bankSelect.required = false;
                    bankSelect.value = '';
                }
            });
        });
    </script>
@endpush
