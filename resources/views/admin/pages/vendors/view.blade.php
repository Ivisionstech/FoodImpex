@extends('admin.layout.master')
@section('content')
    <style>
        .dropdown-item {
            display: inline-block !important;
            padding: 0 !important;
        }
    </style>
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Vendors</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Personal Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if ($vendor->profile)
                            <img src="{{ asset('storage/' . $vendor->profile) }}" width="100" height="100"
                                alt="Avatar" class="rounded-circle mb-3" />
                        @else
                            <img src="{{ asset('images/placeholder.jpg') }}" width="100" height="100" alt="Avatar"
                                class="rounded-circle mb-3" />
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Company Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->company_name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Contact Person:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->person_name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Email:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->email }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Phone:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->phone }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Address:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $vendor->address }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Current Balance:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-label-primary">PKR {{ number_format($vendor->balance, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendors Bills</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2">
                        <input type="hidden" name="trans_from" value="{{ $trans_from }}">
                        <input type="hidden" name="trans_to" value="{{ $trans_to }}">
                        <input type="date" name="bill_from" class="form-control" value="{{ $bill_from }}">
                        <input type="date" name="bill_to" class="form-control" value="{{ $bill_to }}">
                        <button type="submit" class="btn  btn-primary">Filter</button>
                        @if (request()->has('bill_from') || request()->has('bill_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid, 'trans_from' => $trans_from, 'trans_to' => $trans_to]) }}"
                                class="btn  btn-secondary">Clear</a>
                        @endif
                    </form>
                    <a href="{{ route('vendors.bills.create', $vendor->uuid) }}" class="btn btn-primary">Add Bill</a>

                    <a href="{{ route('vendors.bills.general_create_2') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-plus "></i> Add bill Purchase
                    </a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>View</th>
                            <th>Download</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($vendorBills as $bill)
                            <tr>
                                <td>
                                    {{ $bill->date ? \Carbon\Carbon::parse($bill->date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>PKR {{ number_format($bill->total_amount, 0) }}</td>
                                <td>
                                    @php $firstProduct = $bill->billProducts->first(); @endphp
                                    @if ($firstProduct && $firstProduct->type === 'product')
                                        <a href="{{ route('vendors.bills.general_show_2', $bill->uuid) }}">
                                            <i class='bx bx-md bx-show'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('vendors.bills.show', $bill->uuid) }}">
                                            <i class='bx bx-md bx-show'></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $firstProduct = $bill->billProducts->first();
                                    @endphp
                                    @if ($firstProduct && $firstProduct->type === 'product')
                                        <a href="{{ route('vendors.bills.general_pdf_2', $bill->uuid) }}">
                                            <i class='bx bx-md bx-download'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('vendors.bills.download', $bill->uuid) }}">
                                            <i class='bx bx-md bx-download'></i>
                                        </a>
                                    @endif

                                </td>
                                <td class="text-center">
                                    @php
                                        $firstProduct = $bill->billProducts->first();
                                    @endphp
                                    @if ($firstProduct && $firstProduct->type === 'product')
                                        <a href="{{ route('vendors.bills.general_edit_2', $bill->uuid) }}" class="">
                                            <i class='bx bx-md bx-edit'></i>
                                        </a>
                                    @else
                                        <a href="{{ route('vendors.bills.edit', $bill->uuid) }}" class="">
                                            <i class='bx bx-md bx-edit'></i>
                                        </a>
                                    @endif

                                    <a href="javascript:void(0);" class="dropdown-item action-confirm "
                                        data-url="{{ route('vendors.bills.delete', $bill->uuid) }}"
                                        data-text="You want to delete this bill!" data-button-text="Yes, Delete it!">
                                        <i class='bx bx-md bx-trash'></i>
                                    </a>
                                </td>


                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vendors Bank Statement</h5>
                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('vendors.view', $vendor->uuid) }}" class="d-flex gap-2">
                        <input type="hidden" name="bill_from" value="{{ $bill_from }}">
                        <input type="hidden" name="bill_to" value="{{ $bill_to }}">
                        <input type="date" name="trans_from" class="form-control" value="{{ $trans_from }}">
                        <input type="date" name="trans_to" class="form-control" value="{{ $trans_to }}">
                        <button type="submit" class="btn  btn-primary">Filter</button>
                        @if (request()->has('trans_from') || request()->has('trans_to'))
                            <a href="{{ route('vendors.view', ['uuid' => $vendor->uuid, 'bill_from' => $bill_from, 'bill_to' => $bill_to]) }}"
                                class="btn  btn-secondary">Clear</a>
                        @endif
                    </form>
                    <a href="{{ route('vendors.bank-statement', $vendor->uuid) }}" class="btn btn-info ms-2"
                        target="_blank">
                        <i class='bx bx-download'></i> Report
                    </a>
                    <a href="{{ route('vendors.send-payment', $vendor->uuid) }}" class="btn btn-primary ms-2">Send
                        Payment</a>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">mx
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Current Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($vendorTransactions as $transaction)
                            <tr>
                                <td>
                                    {{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    @if ($transaction->type == 'bill' && $transaction->bill)
                                        <strong>Bill #{{ $transaction->bill->id }}</strong><br>
                                        <small class="text-muted">Bill Date:
                                            {{ $transaction->bill->date ? \Carbon\Carbon::parse($transaction->bill->date)->format('d-m-Y') : '-' }}</small>
                                    @elseif ($transaction->type == 'payment')
                                        <strong>Payment</strong><br>
                                        <small
                                            class="text-muted">{{ $transaction->send_via ? 'via ' . $transaction->send_via : 'Payment received' }}</small>
                                    @elseif ($transaction->type == 'Balance')
                                        <strong>Opening Balance</strong><br>
                                        <small class="text-muted">Initial balance</small>
                                    @else
                                        <strong>{{ ucfirst($transaction->type) }}</strong><br>
                                        <small class="text-muted">{{ $transaction->description ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="fw-bold {{ $transaction->type == 'payment' ? 'text-success' : 'text-danger' }}">
                                        PKR {{ number_format($transaction->amount, 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-label-{{ $transaction->type == 'payment' ? 'success' : ($transaction->type == 'Balance' ? 'info' : 'danger') }}">
                                        {{ $transaction->type == 'Balance' ? 'Balance' : ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">PKR {{ number_format($transaction->current_balance, 0) }}</span>
                                </td>
                                <td>
                                    @if ($transaction->type == 'bill' && $transaction->bill)
                                        @php
                                            $firstProduct = $transaction->bill->billProducts->first();
                                        @endphp
                                        @if ($firstProduct && $firstProduct->type === 'product')
                                            <a href="{{ route('vendors.bills.general_show_2', $transaction->bill->uuid) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @else
                                            <a href="{{ route('vendors.bills.show', $transaction->bill->uuid) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class='bx bx-show'></i>
                                            </a>
                                        @endif
                                    @elseif ($transaction->type == 'payment')
                                        <a href="{{ route('vendors.payment-details', $transaction->uuid) }}"
                                            class="btn btn-sm btn-outline-info">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
