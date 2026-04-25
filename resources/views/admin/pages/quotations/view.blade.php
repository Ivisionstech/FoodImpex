@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <!-- Quotation Header -->
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-decoration-underline">SYED FOOD IMPEX</h2>
                            <div class="text-muted">RANA SHOUKAT PLAZA OFFICE NO 1 MAIN GHALA MANDI KAMOKI, GUJRANWALA</div>
                            <h3 class="mt-3">INVOICE/PACKINGLIST</h3>
                        </div>

                        <!-- Consignee and Invoice Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="fw-bold">CONSIGNEE</h5>
                                        <p class="mb-0">{{ $quotation->consignee_name }}</p>
                                        <p>{{ $quotation->consignee_address }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6"><strong>INVOICE NO:</strong></div>
                                            <div class="col-sm-6">{{ $quotation->invoice_no }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"><strong>DATE:</strong></div>
                                            <div class="col-sm-6">
                                                {{ \Carbon\Carbon::parse($quotation->invoice_date)->format('d-m-Y') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"><strong>F.I NO:</strong></div>
                                            <div class="col-sm-6">{{ $quotation->fi_no }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"><strong>DESTINATIONS:</strong></div>
                                            <div class="col-sm-6">{{ $quotation->destination }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"><strong>PAYMENT TERM:</strong></div>
                                            <div class="col-sm-6">{{ $quotation->payment_term }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6"><strong>FREIGHT TERM:</strong></div>
                                            <div class="col-sm-6">{{ $quotation->freight_term }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Route -->
                        <div class="card bg-dark text-white mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <span>(KARACHI PAKISTAN TO {{ strtoupper($quotation->destination) }})</span>
                                <span>H.S.CODE: {{ $quotation->hs_code }}</span>
                            </div>
                        </div>

                        <!-- Goods Description -->
                        <div class="card mb-3">
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ITEM DESCRIPTION OF GOODS QUANTITY</th>
                                            <th>RATE {{ $quotation->currency }}</th>
                                            <th>TOTAL VALUE IN {{ $quotation->currency }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p class="text-decoration-underline fw-bold mb-1">1X20FCL CONTAINER SAID TO
                                                    CONTAIN</p>
                                                <div class="ps-4">
                                                    {{ $quotation->total_bags }} MASTER BAGS OF
                                                    {{ number_format($quotation->total_net_weight, 2) }} M.TONS<br>
                                                    1121 SELLA RICE<br>
                                                    BRAND:SYED
                                                </div>
                                            </td>
                                            <td>{{ $quotation->currency }}
                                                {{ number_format($quotation->rate_per_ton, 2) }}</td>
                                            <td>{{ $quotation->currency }}
                                                {{ number_format($quotation->total_value_usd, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Total Value Summary -->
                        <div class="text-center mb-3">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-md-4">
                                    <strong>TOTAL VALUE(C&F) in {{ $quotation->currency }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <strong>{{ $quotation->shipment_mode }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <strong>{{ $quotation->currency }}:{{ number_format($quotation->total_value_usd, 2) }}</strong>
                                </div>
                            </div>
                            <div class="small text-muted mt-2">
                                (TOTAL VALUE C&F IN {{ $quotation->total_value_usd }} ONLY)
                            </div>
                        </div>

                        <!-- Container Details -->
                        <div class="card mb-3">
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>CONTAINER#</th>
                                            <th>NO OF BAGS</th>
                                            <th>PACK DETAILS</th>
                                            <th>Price/Bag</th>
                                            <th>NET WEIGHT</th>
                                            <th>GR WEIGHT</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->items as $item)
                                            <tr>
                                                <td>{{ $item->container_no ?? '-' }}</td>
                                                <td>{{ $item->no_of_bags ?? '-' }}BAGS</td>
                                                <td>{{ $item->pack_details ?? ($item->package_details ?? '-') }}</td>
                                                <td>{{ $item->price ?? '-' }}</td>
                                                <td>{{ $item->net_weight ?? '-' }} KGS</td>
                                                <td>{{ $item->gross_weight ?? '-' }} KGS</td>
                                                <td>{{ $item->total_value ?? '-' }} {{ $quotation->currency }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>TOTAL VALUE IN
                                                    {{ $quotation->currency }}</strong></td>
                                            <td><strong>{{ $quotation->currency }}
                                                    {{ number_format($quotation->total_value_usd, 2) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Terms -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">PAYMENT TERMS</h5>
                            </div>
                            <div class="card-body p-0">
                                @php
                                    $percent = $quotation->percentage ?? 50;
                                    $advanceValue = $quotation->total_value_usd * ($percent / 100);
                                    $balanceValue = $quotation->total_value_usd - $advanceValue;
                                @endphp

                                <table class="table table-bordered mb-0">
                                    <tr>
                                        <td>
                                            {{ $percent }}% ADVANCE {{ $quotation->currency }}
                                            {{ number_format($advanceValue, 2) }}
                                        </td>
                                        <td>
                                            {{ 100 - $percent }}% BL {{ $quotation->currency }}
                                            {{ number_format($balanceValue, 2) }}
                                        </td>
                                    </tr>
                                </table>

                            </div>
                        </div>

                        <!-- Bank Details -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">BANK ACCOUNT DETAIL</h5>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr>
                                        <td>ACCOUNT NO: {{ $quotation->bank_account }}</td>
                                        <td>IBAN: {{ $quotation->iban }}</td>
                                    </tr>
                                    <tr>
                                        <td>SWIFT CODE: {{ $quotation->swift_code }}</td>
                                        <td>COMPANY NAME: {{ $quotation->company_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>BANK: {{ $quotation->bank_name }}</td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="fw-bold mb-2">TOTAL MASTER BAGS: {{ $quotation->total_bags }} MASTER BAGS</div>
                                <div class="fw-bold mb-2">TOTAL NET WEIGHT:
                                    {{ number_format($quotation->total_net_weight, 2) }} KGS</div>
                                <div class="fw-bold mb-2">TOTAL GROSS WEIGHT:
                                    {{ number_format($quotation->total_gross_weight, 2) }} KGS</div>
                                <div class="small text-muted">
                                    *CERTIFYING ORIGIN OF GOODS AND CONTENTS TO BE TRUE AND CORRECT MADE OF PAKISTAN ORIGIN*
                                </div>
                            </div>
                        </div>

                        <!-- Signature -->
                        <div class="mt-4">
                            <p>FOR <strong>SYED FOOD IMPEX</strong></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 text-end">
                            <a href="{{ route('quotations.download', $quotation->uuid) }}" class="btn btn-primary"
                                target="_blank">
                                <i class="bx bx-printer me-1"></i> Download PDF
                            </a>
                            {{-- <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit
                            </a> --}}
                            <a href="{{ route('quotations.list') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .table th,
        .table td {
            padding: 0.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
        }

        .fw-bold {
            font-weight: 600 !important;
        }
    </style>
@endsection
