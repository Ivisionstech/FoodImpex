@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('vendors.payments.list') }}">Payments History</a> /
            Edit Payment
        </h4>

        <div class="card">
            <h5 class="card-header">Edit Payment Voucher #{{ $payment->id }}</h5>
            <div class="card-body">
                <form action="{{ route('vendors.payments.update', $payment->uuid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Using POST with a custom update route as defined in your logic --}}

                    <div class="row mb-3">
                        <!-- Vendor (Read Only for safety, or Dropdown) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vendor</label>
                            <input type="text" class="form-control bg-light" value="{{ $payment->vendor->company_name }}" readonly>
                            <input type="hidden" name="vendor_id" value="{{ $payment->vendor->uuid }}">
                        </div>

                        <!-- Payment Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="date">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ old('date', \Carbon\Carbon::parse($payment->date)->format('Y-m-d\TH:i')) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="amount">Amount (PKR) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required />
                        </div>

                        <!-- Send Via -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="send_via">Send Via <span class="text-danger">*</span></label>
                            <select name="send_via" id="send_via" class="form-select @error('send_via') is-invalid @enderror" required>
                                <option value="cash" {{ $payment->send_via == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ $payment->send_via == 'bank' ? 'selected' : '' }}>Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Conditional Bank Selection -->
                        <div class="col-md-6" id="bank_section" style="{{ $payment->send_via == 'bank' ? '' : 'display: none;' }}">
                            <label class="form-label fw-bold" for="bank_id">Select Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-select">
                                <option value="">-- Choose Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        {{ (isset($payment->bankTransaction) && $payment->bankTransaction->bank_id == $bank->id) ? 'selected' : '' }}>
                                        {{ $bank->name }} (Bal: {{ number_format($bank->account_balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="description">Description / Remarks</label>
                            <input type="text" class="form-control" name="description" value="{{ old('description', $payment->description) }}" placeholder="Cheque # or Reference...">
                        </div>
                    </div>

                    <!-- Receipt Images -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Update Receipt Images (Leave blank to keep old)</label>
                            <input type="file" class="form-control" name="receipt_images[]" multiple accept="image/*">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success px-5">Update Payment</button>
                            <a href="{{ route('vendors.payments.list') }}" class="btn btn-secondary">Cancel</a>
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
        const sendVia = document.getElementById('send_via');
        const bankSection = document.getElementById('bank_section');
        const bankId = document.getElementById('bank_id');

        sendVia.addEventListener('change', function() {
            if (this.value === 'bank') {
                bankSection.style.display = 'block';
                bankId.required = true;
            } else {
                bankSection.style.display = 'none';
                bankId.required = false;
                bankId.value = '';
            }
        });
    });
</script>
@endpush
