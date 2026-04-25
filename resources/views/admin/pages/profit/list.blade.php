@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Daybooks</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Profit History</h5>
                {{-- <a href="{{ route('daybooks.create') }}" class="btn btn-primary">Add Expense</a> --}}
            </div>
            {{-- <div class="card-body border-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-wallet fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">Total In Hand</h6>
                                <span class="text-muted">Current Balance</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h4 class="mb-1 text-success">PKR: {{ number_format(Auth::user()->in_hand, 2) }}</h4>
                        <small class="text-muted">Available Balance</small>
                    </div>
                </div>
            </div> --}}
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Profit</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($customerBills as $customerBill)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('d-m-Y h:i A', strtotime($customerBill->bill_date)) }}</td>
                                <td>{{ $customerBill->customer->name ?? 'N/A' }}</td>
                                <td>{{ $customerBill->profit ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-label-success">{{ $customerBill->total_amount }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('customers.bills.show', $customerBill->uuid) }}"
                                        class="btn btn-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $customerBills->links() }}
            </div>
        </div>
    </div>
@endsection
