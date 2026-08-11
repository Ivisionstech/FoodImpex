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
            / Invoice Details
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
                <!-- Info Section: Customer and Bill Metadata -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-primary">Customer Information</h6>
                        @if ($bill->customer)
                            <p class="mb-1"><strong>Name:</strong> {{ $bill->customer->name }}</p>
                            <p class="mb-1"><strong>Contact Person:</strong> {{ $bill->customer->person_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer->phone ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $bill->customer->email ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $bill->customer->address ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Balance:</strong> <span class="fw-bold text-{{ $bill->customer->balance >= 0 ? 'danger' : 'success' }}">
                                PKR {{ number_format(abs($bill->customer->balance ?? 0), 2) }} {{ $bill->customer->balance >= 0 ? 'DR' : 'CR' }}
                            </span></p>
                        @else
                            <p class="mb-1"><strong>Name:</strong> {{ $bill->customer_name ?? 'Walk-in Customer' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $bill->customer_phone ?? 'N/A' }}</p>
                            <p class="mb-1"><em>Walk-in Customer</em></p>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3 text-primary">Bill Information</h6>
                        <p class="mb-1"><strong>Bill Number:</strong> #{{ $bill->id }}</p>
                        <p class="mb-1"><strong>Date:</strong> {{ $bill->bill_date ? date('d-m-Y h:i A', strtotime($bill->bill_date)) : 'N/A' }}</p>
                        <p class="mb-1"><strong>Bill Type:</strong> <span class="badge bg-info">Sales</span></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-{{ $bill->approval_status == 'approved' ? 'success' : 'warning' }}">
                                <i class="bx bx-{{ $bill->approval_status == 'approved' ? 'check-circle' : 'time' }} me-1"></i>
                                {{ ucfirst($bill->approval_status ?? 'Pending') }}
                            </span>
                        </p>
                        @if(isset($bill->paid_amount) && $bill->paid_amount > 0)
                            <p class="mb-1"><strong>Paid Amount:</strong> PKR {{ number_format($bill->paid_amount, 2) }}</p>
                        @endif
                        <p class="mb-1"><strong>Payment Terms:</strong> {{ $bill->payment_terms ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Products Table Section with Weight Details -->
                <div class="table-responsive mb-4">
                    <h6 class="mb-3 text-primary">Sales Items with Weight Details</h6>
                    @if($bill->billProducts->count() > 0)
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 18%;">Product Name</th>
                                    <th style="width: 15%;">Description</th>
                                    <th class="text-center" style="width: 8%;">Qty</th>
                                    <th class="text-center" style="width: 10%;">Packing (KG)</th>
                                    <th class="text-center" style="width: 10%;">Total Wt</th>
                                    <th class="text-center" style="width: 10%;">Bardana Wt</th>
                                    <th class="text-center" style="width: 10%;">Net Wt</th>
                                    <th class="text-end" style="width: 12%;">Rate/40kg</th>
                                    <th class="text-end" style="width: 12%;">Row Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $itemsSubtotal = 0; @endphp
                                @foreach ($bill->billProducts as $index => $billProduct)
                                    @php 
                                        $rowTotal = $billProduct->total ?? 0;
                                        $itemsSubtotal += $rowTotal;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $billProduct->product->name ?? 'Product Deleted' }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ $billProduct->description ?? '--' }}</small>
                                        </td>
                                        <td class="text-center">{{ number_format($billProduct->quantity ?? 0, 0) }}</td>
                                        <td class="text-center">
                                            {{ $billProduct->packing ? number_format($billProduct->packing, 2) : '--' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $billProduct->total_weight ? number_format($billProduct->total_weight, 2) : '--' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $billProduct->bardana_weight ? number_format($billProduct->bardana_weight, 2) : '--' }}
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $billProduct->net_weight ? number_format($billProduct->net_weight, 2) : '--' }}</strong>
                                        </td>
                                        <td class="text-end">PKR {{ number_format($billProduct->rate_per_40kg ?? 0, 2) }}</td>
                                        <td class="text-end"><strong>PKR {{ number_format($rowTotal, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-1"></i> 
                            <strong>No products found for this bill.</strong> 
                            This means products were not saved properly when the bill was created.
                        </div>
                    @endif
                </div>

                <!-- Charges Section -->
                <div class="row mb-4">
                    <!-- Extra Charges (Subtract) -->
                    @if ($bill->extraCharges->count() > 0)
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header border-bottom border-dark">
                                    <h6 class="mb-0 text-dark"><strong>Extra Charges (Subtract)</strong></h6>
                                </div>
                                <div class="card-body">
                                    @php $extraTotal = 0; @endphp
                                    @foreach ($bill->extraCharges as $charge)
                                        @php $extraTotal += $charge->amount; @endphp
                                        <p class="mb-2">
                                            <strong class="text-dark">{{ $charge->name }}:</strong> PKR
                                            {{ number_format($charge->amount, 2) }}
                                        </p>
                                    @endforeach
                                    <hr>
                                    <p class="mb-0"><strong class="text-dark">Total Extra:</strong> PKR
                                        {{ number_format($extraTotal, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Section: Transaction details and Totals -->
                <div class="row mt-4">
                    <!-- Left Side: Ledger/Balance Info -->
                    <div class="col-md-6">
                        <h6 class="mb-3 text-primary">Ledger Impact</h6>
                        @if ($bill->transactions && $bill->transactions->isNotEmpty())
                            @php $latestTransaction = $bill->transactions->last(); @endphp
                            <div class="p-3 border rounded bg-light">
                                <p class="mb-1"><strong>Transaction Date:</strong>
                                    {{ $latestTransaction->transaction_date ? date('d-m-Y', strtotime($latestTransaction->transaction_date)) : 'N/A' }}</p>
                                <p class="mb-1"><strong>Entry Type:</strong>
                                    {{ ucfirst($latestTransaction->type ?? 'Bill') }}</p>
                                <p class="mb-0"><strong>Customer Balance after this Bill:</strong>
                                    <span class="fw-bold text-{{ $latestTransaction->current_balance >= 0 ? 'danger' : 'success' }}">
                                        PKR {{ number_format(abs($latestTransaction->current_balance ?? 0), 2) }} 
                                        {{ $latestTransaction->current_balance >= 0 ? 'DR' : 'CR' }}
                                    </span>
                                </p>
                            </div>
                        @else
                            <div class="p-3 border rounded bg-light">
                                <p class="mb-0 text-muted">No transaction record found for this bill.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Right Side: Final Bill Calculations -->
                    <div class="col-md-6 text-md-end">
                        <div class="invoice-summary">
                            <p class="mb-2">Subtotal (Products): <strong>PKR
                                    {{ number_format($itemsSubtotal, 2) }}</strong></p>

                            @if ($bill->extraCharges->count() > 0)
                                @php $extraTotal = $bill->extraCharges->sum('amount'); @endphp
                                <p class="mb-2 text-danger">Extra Charges: <strong>-PKR
                                        {{ number_format($extraTotal, 2) }}</strong></p>
                            @endif

                            <hr class="my-3">
                            <h4 class="fw-bold text-primary">
                                Grand Total: PKR {{ number_format($bill->grand_total ?? $itemsSubtotal, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table-sm th, .table-sm td {
        font-size: 0.875rem;
        vertical-align: middle;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .invoice-summary {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.375rem;
    }
    .text-primary {
        color: #696cff !important;
    }
    .border-dark {
        border-color: #dee2e6 !important;
    }
</style>
@endpush