@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a
                href="{{ route('vendors.view', $vendorTransaction->vendor->uuid) }}">{{ $vendorTransaction->vendor->company_name }}</a>
            /
            Payment
            Details
        </h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Details</h5>
                <div>
                    <a href="{{ route('vendors.view', $vendorTransaction->vendor->uuid) }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">

                    <div class="col-md-6 ">
                        {{-- <h6 class="mb-3">Payment Information</h6> --}}
                        <p class="mb-1"><strong>Date:</strong>
                            {{ Carbon\Carbon::parse($vendorTransaction->date)->format('d-m-Y') }}</p>
                        <p class="mb-1"><strong>Amount:</strong> PKR {{ $vendorTransaction->amount }}</p>
                        <p class="mb-1"><strong>Balance:</strong> PKR {{ $vendorTransaction->current_balance }}</p>
                        {{-- <p class="mb-1"><strong>Status:</strong>
                            <span
                                class="badge bg-{{ $bill->status === 'pending' ? 'warning' : ($bill->status === 'paid' ? 'success' : 'danger') }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </p> --}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Receipt Images</h6>
                        <div class="row">
                            @foreach ($vendorTransaction->vendorTransactionImages as $image)
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/' . $image->image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $image->image) }}" alt="Receipt" class="img-fluid">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Transaction Information</h6>
                        @if ($bill->vendorTransaction)
                            <p class="mb-1"><strong>Transaction Date:</strong>
                                {{ $bill->vendorTransaction->date }}</p>
                            <p class="mb-1"><strong>Current Balance:</strong>
                                {{ number_format($bill->vendorTransaction->current_balance, 2) }}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3">Bill Summary</h6>
                        <p class="mb-1"><strong>Subtotal:</strong>
                            {{ number_format($bill->total_amount - $bill->extraCharges->sum('amount'), 2) }}</p>
                        <p class="mb-1"><strong>Extra Charges:</strong>
                            {{ number_format($bill->extraCharges->sum('amount'), 2) }}</p>
                        <p class="mb-1"><strong>Total Amount:</strong> {{ number_format($bill->total_amount, 2) }}</p>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
@endsection
