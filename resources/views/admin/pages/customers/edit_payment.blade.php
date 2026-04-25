@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span> Edit Payment
            </h4>
            <a href="{{ route('customers.receive-payment.list') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Edit Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="editPaymentForm" method="POST" action="{{ route('customers.receive-payment.update', $payment->uuid) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" value="{{ $payment->customer->name ?? 'N/A' }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Transaction Date</label>
                                <input type="datetime-local" class="form-control" value="{{ \Carbon\Carbon::parse($payment->transaction_date)->format('Y-m-d\TH:i') }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Amount (PKR)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $payment->amount }}" required>
                                <small class="text-muted">Previous Amount: PKR {{ number_format($payment->amount, 2) }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="method" class="form-control" id="methodSelect" required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ $payment->method == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank" {{ $payment->method == 'bank' ? 'selected' : '' }}>Bank</option>
                                </select>
                            </div>

                            <div class="mb-3" id="bankSelectDiv" style="display: {{ $payment->method == 'bank' ? 'block' : 'none' }}">
                                <label class="form-label">Select Bank</label>
                                <select name="bank_id" class="form-control" id="bankSelect">
                                    <option value="">Select Bank</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ $payment->bank_id == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->name }} ({{ $bank->account_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description/Remarks</label>
                                <textarea name="description" class="form-control" rows="3">{{ $payment->description }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Payment
                                </button>
                                <a href="{{ route('customers.receive-payment.list') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Payment Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Transaction ID</label>
                            <p class="text-muted">{{ $payment->uuid }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Balance</label>
                            <p class="text-info">PKR {{ number_format($payment->current_balance, 2) }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <p><span class="badge bg-label-success">Active</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Created At</label>
                            <p class="text-muted">{{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const methodSelect = document.getElementById('methodSelect');
            const bankSelectDiv = document.getElementById('bankSelectDiv');

            methodSelect.addEventListener('change', function() {
                if (this.value === 'bank') {
                    bankSelectDiv.style.display = 'block';
                } else {
                    bankSelectDiv.style.display = 'none';
                }
            });

            const editPaymentForm = document.getElementById('editPaymentForm');
            editPaymentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const method = document.getElementById('methodSelect').value;

                // Validate bank selection if method is bank
                if (method === 'bank' && !document.getElementById('bankSelect').value) {
                    alert('Please select a bank for bank transfers.');
                    return;
                }

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        alert(data.message);
                        window.location.href = data.redirect;
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the payment.');
                });
            });
        });
    </script>
@endsection
