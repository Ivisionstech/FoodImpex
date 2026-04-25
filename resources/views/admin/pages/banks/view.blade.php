@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Banks</h4>
        <div class="card">
            <h5 class="card-header">Personal Details</h5>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-12">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Bank Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $bank->name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Account Title:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $bank->account_title }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Account Number:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $bank->account_number }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Account Balance:</strong>
                            </div>
                            <div class="col-md-8">
                                PKR {{ number_format((float)$bank->account_balance, 0, '.', ',') }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('banks.edit', $bank->uuid) }}" class="btn btn-primary">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {{-- <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bank Transactions</h5>
                <form method="GET" action="{{ route('cash.view') }}" class="d-flex gap-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if (request()->has('from_date') || request()->has('to_date'))
                        <a href="{{ route('cash.view') }}" class="btn btn-secondary"
                            onclick="event.preventDefault(); window.location.href = this.href;">
                            Clear
                        </a>
                    @endif
                </form>
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
                        @foreach ($bank->bankTransactions as $transaction)
                            <tr>
                                <td>
                                    {{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y, h:iA') : '-' }}
                                </td>
                                <td class="balance">
                                    PKR {{ number_format(abs($transaction->amount), 0, '.', ',') }}
                                </td>
                                <td>{{ $transaction->transaction_type }}</td>
                                <td>{{ number_format($transaction->balance, 0, '.', ',') }}</td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> --}}
        <div class="card mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bank Transactions</h5>
                <form method="GET" action="{{ route('banks.view', $bank->uuid) }}" class="d-flex gap-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if (request()->has('from_date') || request()->has('to_date'))
                        <a href="{{ route('banks.view', $bank->uuid) }}" class="btn btn-secondary">Clear</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table" style="min-height: 200px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Type</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($bank->bankTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
                                <td>
                                    {{ $transaction->created_at ? \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="balance">
                                    PKR {{ number_format(abs($transaction->amount), 0, '.', ',') }}
                                </td>
                                <td>{{ $transaction->transaction_type }}</td>
                                <td>PKR {{ number_format($transaction->balance, 0, '.', ',') }}</td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No transactions found for selected date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
