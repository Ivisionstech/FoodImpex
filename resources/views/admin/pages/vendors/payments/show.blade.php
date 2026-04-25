@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('vendors.payments.list') }}">Payments History</a> /
            Details
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Payment Voucher</h5>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <small class="text-muted text-uppercase fw-bold">Paid To:</small>
                                <p class="mb-1 fw-bold">{{ $payment->vendor->company_name }}</p>
                                <p class="mb-0 text-muted">{{ $payment->vendor->phone }}</p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <small class="text-muted text-uppercase fw-bold">Date & Time:</small>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($payment->date)->format('d-M-Y h:i A') }}</p>
                            </div>
                        </div>

                        <div class="table-responsive border rounded">
                            <table class="table table-flush mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>{{ ucfirst($payment->type) }} Sent</strong><br>
                                            <small class="text-muted">{{ $payment->description ?? 'No extra notes provided' }}</small>
                                        </td>
                                        <td class="text-end fw-bold">PKR {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-end fw-bold">Total Paid:</td>
                                        <td class="text-end fw-bold text-primary">PKR {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Receipt Images Section -->
                @if($payment->vendorTransactionImages->count() > 0)
                <div class="card mt-4">
                    <h5 class="card-header">Receipt Images</h5>
                    <div class="card-body">
                        <div class="row">
                            @foreach($payment->vendorTransactionImages as $img)
                            <div class="col-md-4 mb-3">
                                <a href="{{ asset('storage/' . $img->image) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $img->image) }}" class="img-fluid rounded border" alt="Receipt">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
@endsection
