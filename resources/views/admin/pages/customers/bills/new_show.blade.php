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
                <!-- 🔍 DEBUG INFORMATION - Remove after bug is resolved -->
                <div class="alert alert-info mb-4">
                    <h5><i class="bx bx-bug me-1"></i> Debug Information</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Bill ID:</strong> {{ $bill->id }}<br>
                            <strong>Bill UUID:</strong> {{ $bill->uuid }}<br>
                            <strong>Bill Date:</strong> {{ $bill->bill_date }}<br>
                            <strong>Total Amount:</strong> {{ $bill->total_amount }}
                        </div>
                        <div class="col-md-4">
                            <strong>Products Count:</strong> {{ $bill->billProducts->count() }}<br>
                            <strong>Extra Charges Count:</strong> {{ $bill->extraCharges->count() }}<br>
                            <strong>Transactions Count:</strong> {{ $bill->transactions->count() }}<br>
                            <strong>Customer:</strong> {{ $bill->customer ? $bill->customer->name : 'No Customer' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Approval Status:</strong> 
                            <span class="badge bg-{{ $bill->approval_status == 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($bill->approval_status ?? 'Pending') }}
                            </span><br>
                            <strong>Bill Type:</strong> {{ $bill->type ?? 'N/A' }}<br>
                            <strong>Payment Terms:</strong> {{ $bill->payment_terms ?? 'N/A' }}
                        </div>
                    </div>
                    
                    <!-- Database Query Debug -->
                    @if($bill->billProducts->count() == 0)
                        <div class="mt-2">
                            <strong style="color: red;">⚠️ No products found! Check if products were saved correctly.</strong>
                            <br>
                            <small>Run this query in your database:</small>
                            <pre class="bg-light p-2 mt-1" style="font-size: 12px;">
SELECT * FROM customer_bill_products WHERE customer_bill_id = {{ $bill->id }};
                            </pre>
                        </div>
                    @endif
                    
                    @if($bill->extraCharges->count() == 0)
                        <div class="mt-2">
                            <strong style="color: orange;">⚠️ No extra charges found.</strong>
                        </div>
                    @endif
                    
                    @if($bill->transactions->count() == 0)
                        <div class="mt-2">
                            <strong style="color: orange;">⚠️ No transactions found.</strong>
                        </div>
                    @endif
                </div>
                <!-- End Debug Information -->

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
                                @foreach ($bill->billProducts as $index => $billProduct)
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
                                    <th>PKR {{ number_format($bill->billProducts->sum('total'), 2) }}</th>
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

                <div class="row">
                    <div class="col-md-6">
                        @if ($bill->customer)
                            <h6 class="mb-3">Transaction Information 
                                <span class="badge bg-info">{{ $bill->transactions->count() }}</span>
                            </h6>
                            @if ($bill->transactions && $bill->transactions->isNotEmpty())
                                @foreach ($bill->transactions as $transaction)
                                    <div class="border-bottom mb-2 pb-2">
                                        <p class="mb-1"><strong>Transaction Date:</strong>
                                            {{ $transaction->transaction_date ? date('d-m-Y', strtotime($transaction->transaction_date)) : 'N/A' }}</p>
                                        <p class="mb-1"><strong>Amount:</strong>
                                            PKR {{ number_format($transaction->amount ?? 0, 2) }}</p>
                                        <p class="mb-1"><strong>Type:</strong>
                                            <span class="badge bg-{{ $transaction->type == 'payment' ? 'success' : 'info' }}">
                                                {{ ucfirst($transaction->type ?? 'N/A') }}
                                            </span>
                                        </p>
                                        <p class="mb-1"><strong>Current Balance:</strong>
                                            PKR {{ number_format($transaction->current_balance ?? 0, 2) }}</p>
                                        <p class="mb-1"><strong>Description:</strong>
                                            {{ $transaction->description ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Status:</strong>
                                            <span class="badge bg-{{ $transaction->approval_status == 'approved' ? 'success' : 'warning' }}">
                                                {{ ucfirst($transaction->approval_status ?? 'Pending') }}
                                            </span>
                                        </p>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No transactions found for this bill.</p>
                            @endif
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3">Bill Summary</h6>
                        <?php 
                            $subtotal = $bill->billProducts->sum('total');
                            $extraChargesTotal = $bill->extraCharges->sum('amount');
                            $total = $subtotal + $extraChargesTotal;
                        ?>
                        <p class="mb-1"><strong>Subtotal:</strong>
                            PKR {{ number_format($subtotal, 2) }}</p>
                        
                        @if($bill->extraCharges->isNotEmpty())
                            @foreach ($bill->extraCharges as $charge)
                                <p class="mb-1"><strong>{{ $charge->name }}:</strong>
                                    + PKR {{ number_format($charge->amount, 2) }}</p>
                            @endforeach
                            <p class="mb-1"><strong>Extra Charges Total:</strong>
                                PKR {{ number_format($extraChargesTotal, 2) }}</p>
                        @else
                            <p class="mb-1 text-muted"><em>No extra charges</em></p>
                        @endif
                        
                        <hr>
                        <p class="mb-1"><strong>Total Amount:</strong> 
                            <strong>PKR {{ number_format($bill->total_amount ?? $total, 2) }}</strong>
                        </p>
                        
                        @if(isset($bill->paid_amount) && $bill->paid_amount > 0)
                            <p class="mb-1"><strong>Paid Amount:</strong>
                                PKR {{ number_format($bill->paid_amount, 2) }}</p>
                            <p class="mb-1"><strong>Balance Due:</strong>
                                <strong>PKR {{ number_format(($bill->total_amount ?? $total) - $bill->paid_amount, 2) }}</strong>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection