@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('customers.list') }}">Customers</a> /
            Receive General Payment
        </h4>

        <div class="card">
            <h5 class="card-header">Receive Payment from Customer</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('customers.receive-payment.store-general') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <!-- Customer Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold" for="customer_id">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select select2" required>
                                <option value="">-- Choose Customer --</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->uuid }}">
                                        {{ $c->name }} (Current Due: PKR {{ number_format($c->balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="date" name="date"
                                value="{{ now()->format('Y-m-d\TH:i') }}" required />
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label" for="amount">Amount Received <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="amount"
                                name="amount" placeholder="0.00" required />
                        </div>

                        <!-- Receive Via -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="receive_via">Receive Via <span class="text-danger">*</span></label>
                            <select name="receive_via" id="receive_via" class="form-select" required>
                                <option value="">Select Method</option>
                                <option value="bank">Bank Account</option>
                                <option value="cash">Cash in Hand</option>
                            </select>
                        </div>

                        <!-- Bank Selection -->
                        <div class="col-md-6 mt-3" id="bank_section" style="display: none;">
                            <label class="form-label" for="bank_id">Select Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-select">
                                <option value="">Choose Bank...</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }} (Bal: {{ number_format($bank->account_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Receipt Images -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="receipt_images">Receipt Images</label>
                            <input type="file" class="form-control" id="receipt_images" multiple name="receipt_images[]" accept="image/*" />
                        </div>

                        <!-- Description -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label" for="description">Description / Remarks</label>
                            <input type="text" class="form-control" name="description" placeholder="e.g. Received via Bank Transfer">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" id="submitButton" class="btn btn-primary px-5">Save Payment</button>
                            <a href="{{ route('customers.list') }}" class="btn btn-secondary">Cancel</a>
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
            const receiveVia = document.getElementById('receive_via');
            const bankSection = document.getElementById('bank_section');
            const bankSelect = document.getElementById('bank_id');

            receiveVia.addEventListener('change', function() {
                if (this.value === 'bank') {
                    bankSection.style.display = 'block';
                    bankSelect.setAttribute('required', 'required');
                } else {
                    bankSection.style.display = 'none';
                    bankSelect.removeAttribute('required');
                    bankSelect.value = '';
                }
            });
        });
    </script>
@endpush
