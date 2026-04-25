@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumbs Navigation -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('vendors.view', $bill->vendor->uuid) }}">{{ $bill->vendor->company_name }}</a> /
            Bill Details
        </h4>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Purchase Bill Details </h5>
                <div>
                    <!-- Action Buttons -->
                    <a href="{{ route('vendors.bills.general_pdf_2', $bill->uuid) }}" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Download PDF
                    </a>
                    <a href="{{ route('vendors.view', $bill->vendor->uuid) }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Info Section: Vendor and Bill Metadata -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="mb-3 text-primary">Vendor Information</h6>
                        <p class="mb-1"><strong>Company:</strong> {{ $bill->vendor->company_name }}</p>
                        <p class="mb-1"><strong>Contact Person:</strong> {{ $bill->vendor->contact_person }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $bill->vendor->phone }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $bill->vendor->email }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-3 text-primary">Bill Information</h6>
                        <p class="mb-1"><strong>Bill UUID:</strong> <small class="text-muted">{{ $bill->uuid }}</small>
                        </p>
                        <p class="mb-1"><strong>Date:</strong>
                            {{ \Carbon\Carbon::parse($bill->date)->format('d-M-Y h:i A') }}</p>
                        <p class="mb-1"><strong>Bill Type:</strong> <span class="badge bg-info">Product</span></p>
                    </div>
                </div>

                <!-- Products Table Section with Weight Details -->
                <div class="table-responsive mb-4">
                    <h6 class="mb-3 text-primary">Purchased Items with Weight Details</h6>
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Product Name</th>
                                <th style="width: 15%;">Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Packing (KG)</th>
                                <th class="text-center">Total Wt</th>
                                <th class="text-center">Bardana Wt</th>
                                <th class="text-center">Net Wt</th>
                                <th class="text-end">Rate/40kg</th>
                                <th class="text-end">Row Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemsSubtotal = 0; @endphp
                            @foreach ($bill->billProducts as $billProduct)
                                @php
                                    $rowTotal =
                                        $billProduct->total_price ?? $billProduct->quantity * $billProduct->price;
                                    $itemsSubtotal += $rowTotal;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $billProduct->product->name }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $billProduct->description ?? '--' }}</small>
                                    </td>
                                    <td class="text-center">{{ number_format($billProduct->quantity, 0) }}</td>
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
                                    <td class="text-end">PKR {{ number_format($billProduct->price, 2) }}</td>
                                    <td class="text-end"><strong>PKR {{ number_format($rowTotal, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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

                    <!-- Additional Charges (Add) -->
                    @if ($bill->additionalCharges->count() > 0)
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header border-bottom border-dark">
                                    <h6 class="mb-0 text-dark"><strong>Additional Charges (Add)</strong></h6>
                                </div>
                                <div class="card-body">
                                    @php $additionalTotal = 0; @endphp
                                    @foreach ($bill->additionalCharges as $charge)
                                        @php $additionalTotal += $charge->amount; @endphp
                                        <p class="mb-2">
                                            <strong class="text-dark">{{ $charge->name }}:</strong> PKR
                                            {{ number_format($charge->amount, 2) }}
                                        </p>
                                    @endforeach
                                    <hr>
                                    <p class="mb-0"><strong class="text-dark">Total Additional:</strong> PKR
                                        {{ number_format($additionalTotal, 2) }}</p>
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
                        @if ($bill->vendorTransaction)
                            <div class="p-3 border rounded bg-light">
                                <p class="mb-1"><strong>Transaction Date:</strong>
                                    {{ \Carbon\Carbon::parse($bill->vendorTransaction->date)->format('d-M-Y') }}</p>
                                <p class="mb-1"><strong>Entry Type:</strong>
                                    {{ ucfirst($bill->vendorTransaction->transaction_type) }} (Credit)</p>
                                <p class="mb-0"><strong>Vendor Balance after this Bill:</strong>
                                    <span class="text-danger fw-bold">PKR
                                        {{ number_format($bill->vendorTransaction->current_balance, 2) }}</span>
                                </p>
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

                            @if ($bill->additionalCharges->count() > 0)
                                @php $additionalTotal = $bill->additionalCharges->sum('amount'); @endphp
                                <p class="mb-2 text-success">Additional Charges: <strong>+PKR
                                        {{ number_format($additionalTotal, 2) }}</strong></p>
                            @endif

                            <hr class="my-3">
                            <h4 class="fw-bold text-primary">
                                Grand Total: PKR {{ number_format($bill->total_amount, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
