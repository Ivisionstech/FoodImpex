@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            @if ($bill->customer)
                <a href="{{ route('customers.view', $bill->customer->uuid) }}">{{ $bill->customer->name }}</a>
            @else
                <a href="{{ route('customers.list') }}">Customers</a>
            @endif
            / Bill Details
        </h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">New Sales Bill Details</h5>
                <div>
                    <a href="{{ route('customers.bills.download.new', $bill->uuid) }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Download PDF
                    </a>
                    @if ($bill->customer)
                        <a href="{{ route('customers.view', $bill->customer->uuid) }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back
                        </a>
                    @else
                        <a href="{{ route('customers.list') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3">Customer Information</h6>
                        @if ($bill->customer)
                            <p class="mb-1"><strong>Name:</strong> {{ $bill->customer->name }}</p>
                            <p class="mb-1"><strong>Contact Person:</strong> {{ $bill->customer->person_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer->phone ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $bill->customer->email ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $bill->customer->address ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Balance:</strong> PKR {{ number_format($bill->customer->balance ?? 0, 2) }}</p>
                        @else
                            <p class="mb-1"><strong>Name:</strong> {{ $bill->customer_name ?? 'Walk-in Customer' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer_phone ?? 'N/A' }}</p>
                            <p class="mb-1"><em>Walk-in Customer</em></p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3">Bill Information</h6>
                        <p class="mb-1"><strong>Bill Number: #</strong>{{ $bill->id }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ $bill->bill_date ? date('d-m-Y', strtotime($bill->bill_date)) : 'N/A' }}</p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-{{ $bill->approval_status == 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($bill->approval_status ?? 'Pending') }}
                            </span>
                        </p>
                        @if(isset($bill->paid_amount) && $bill->paid_amount > 0)
                            <p class="mb-1"><strong>Paid Amount:</strong> PKR {{ number_format($bill->paid_amount, 2) }}</p>
                        @endif
                        <p class="mb-1"><strong>Payment Terms:</strong> {{ $bill->payment_terms ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <h6 class="mb-3">Products 
                        <span class="badge bg-primary">{{ $bill->billProducts->count() }}</span>
                    </h6>
                    @if($bill->billProducts->count() > 0)
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Packing (KG)</th>
                                    <th>Total Weight</th>
                                    <th>Bardana Weight</th>
                                    <th>Net Weight</th>
                                    <th>Rate per 40 KGS</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @foreach ($bill->billProducts as $index => $billProduct)
                                    @php 
                                        $subtotal += $billProduct->total ?? 0; 
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $billProduct->product->name ?? 'Product Deleted' }}</td>
                                        <td>{{ $billProduct->description ?? '-' }}</td>
                                        <td>{{ $billProduct->quantity ?? 0 }}</td>
                                        <td>{{ $billProduct->packing ?? '-' }}</td>
                                        <td>{{ number_format($billProduct->total_weight ?? 0, 2) }}</td>
                                        <td>{{ number_format($billProduct->bardana_weight ?? 0, 2) }}</td>
                                        <td>{{ number_format($billProduct->net_weight ?? 0, 2) }}</td>
                                        <td>PKR {{ number_format($billProduct->rate_per_40kg ?? 0, 2) }}</td>
                                        <td>PKR {{ number_format($billProduct->total ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="9" class="text-end">Subtotal:</th>
                                    <th>PKR {{ number_format($subtotal, 2) }}</th>
                                </tr>
                                @if($bill->extraCharges->count() > 0)
                                    @foreach ($bill->extraCharges as $charge)
                                        <tr>
                                            <th colspan="9" class="text-end">{{ $charge->name }}:</th>
                                            <th>+ PKR {{ number_format($charge->amount, 2) }}</th>
                                        </tr>
                                    @endforeach
                                    <tr class="table-active">
                                        <th colspan="9" class="text-end">Extra Charges Total:</th>
                                        <th>PKR {{ number_format($bill->extraCharges->sum('amount'), 2) }}</th>
                                    </tr>
                                @endif
                                <tr class="table-success">
                                    <th colspan="9" class="text-end"><strong>Grand Total:</strong></th>
                                    <th><strong>PKR {{ number_format($bill->grand_total ?? $subtotal, 2) }}</strong></th>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-1"></i> 
                            <strong>No products found for this bill.</strong> 
                            This means products were not saved properly when the bill was created.
                        </div>
                    @endif
                </div>

                @if ($bill->customer && $bill->transactions->isNotEmpty())
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Transaction History 
                                <span class="badge bg-info">{{ $bill->transactions->count() }}</span>
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bill->transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->transaction_date ? date('d-m-Y', strtotime($transaction->transaction_date)) : 'N/A' }}</td>
                                                <td>PKR {{ number_format($transaction->amount ?? 0, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->type == 'payment' ? 'success' : 'info' }}">
                                                        {{ ucfirst($transaction->type ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td>PKR {{ number_format($transaction->current_balance ?? 0, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->approval_status == 'approved' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($transaction->approval_status ?? 'Pending') }}
                                                    </span>
                                                </td>
                                                <td>{{ $transaction->description ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection