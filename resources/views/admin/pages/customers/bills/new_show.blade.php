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
                            <p class="mb-1"><strong>Contact Person:</strong> {{ $bill->customer->person_name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer->phone }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $bill->customer->email }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $bill->customer->address }}</p>
                        @else
                            <p class="mb-1"><strong>Name:</strong> {{ $bill->customer_name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer_phone }}</p>
                            <p class="mb-1"><em>Walk-in Customer</em></p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3">Bill Information</h6>
                        <p class="mb-1"><strong>Bill Number: #</strong>{{ $bill->id }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ $bill->bill_date }}</p>

                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <h6 class="mb-3">Products</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
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
                            @foreach ($bill->billProducts as $billProduct)
                                <tr>
                                    <td>{{ $billProduct->product->name }}</td>
                                    <td>{{ $billProduct->description ?? '-' }}</td>
                                    <td>{{ $billProduct->quantity }}</td>
                                    <td>{{ $billProduct->packing ?? '-' }}</td>
                                    <td>{{ number_format($billProduct->total_weight ?? 0, 2) }}</td>
                                    <td>{{ number_format($billProduct->bardana_weight ?? 0, 2) }}</td>
                                    <td>{{ number_format($billProduct->net_weight ?? 0, 2) }}</td>
                                    <td>PKR {{ number_format($billProduct->rate_per_40kg ?? 0, 2) }}</td>
                                    <td>PKR {{ number_format($billProduct->total ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        @if ($bill->customer)
                            <h6 class="mb-3">Transaction Information</h6>
                            @if ($bill->transactions->isNotEmpty())
                                @foreach ($bill->transactions as $transaction)
                                    <p class="mb-1"><strong>Transaction Date:</strong>
                                        {{ $transaction->date }}</p>
                                    <p class="mb-1"><strong>Amount :</strong>
                                        PKR {{ number_format($transaction->amount, 2) }}</p>
                                    <p class="mb-1"><strong>Current Balance :</strong>
                                        PKR {{ number_format($transaction->current_balance, 2) }}</p>
                                @endforeach
                            @endif
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3">Bill Summary</h6>
                        <p class="mb-1"><strong>Subtotal :</strong>
                            PKR {{ number_format($bill->total_amount - $bill->extraCharges->sum('amount'), 2) }}</p>
                        @foreach ($bill->extraCharges as $charge)
                            <p class="mb-1"><strong>{{ $charge->name }}:</strong>
                                PKR {{ number_format($charge->amount, 2) }}</p>
                        @endforeach
                        <p class="mb-1"><strong>Total Amount :</strong> PKR {{ number_format($bill->total_amount, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
