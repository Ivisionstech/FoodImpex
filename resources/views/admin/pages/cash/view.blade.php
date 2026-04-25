@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Cash</h4>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('cash.view') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary text-white w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('from_date') || request()->has('to_date'))
                                    <a href="{{ route('cash.view') }}" class="btn btn-secondary w-100">Clear</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cash Transactions</h5>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="cashActions" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-plus me-1"></i> Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="cashActions">
                            <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#addCashModal">Add Cash</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#deductCashModal">Deduct Cash</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Type</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $runningBalance = 0;
                        @endphp
                        @foreach ($filteredTransactions as $transaction)
                            @if ($transaction->transaction_type == 'credit')
                                @php
                                    $runningBalance += $transaction->amount;
                                @endphp
                            @else
                                @php
                                    $runningBalance -= $transaction->amount;
                                @endphp
                            @endif
                            <tr>
                                <td>
                                    {{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') : '-' }}
                                </td>
                                <td>PKR {{ number_format($transaction->amount, 0, '.', ',') }}</td>
                                <td>
                                    <span
                                        class="badge bg-label-{{ $transaction->transaction_type == 'debit' ? 'danger' : 'success' }}">
                                        {{ $transaction->transaction_type }}
                                    </span>
                                </td>
                                <td>PKR {{ number_format($runningBalance, 0, '.', ',') }}</td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<div class="modal fade" id="addCashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Cash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cash.add-cash') }}" method="POST" class="ajax-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" required placeholder="Enter amount">
                        <div id="amount-error" class="text-danger small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required placeholder="Enter description"></textarea>
                        <div id="description-error" class="text-danger small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Add Cash</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deductCashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deduct Cash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cash.deduct-cash') }}" method="POST" class="ajax-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" required placeholder="Enter amount">
                        <div id="amount-error" class="text-danger small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required placeholder="Enter description"></textarea>
                        <div id="description-error" class="text-danger small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Deduct Cash</button>
                </div>
            </form>
        </div>
    </div>
</div>