@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Daybooks</h4>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('daybooks.list') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $from_date }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $to_date }}">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary text-white w-100">
                                    <i class="bx bx-filter-alt me-1"></i> Filter
                                </button>
                                @if (request()->has('from_date') || request()->has('to_date'))
                                    <a href="{{ route('daybooks.list') }}" class="btn btn-secondary w-100">Clear</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daybooks</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('daybooks.create') }}" class="btn btn-primary">Add Expense</a>
                </div>
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
                            <th>Vendor</th>
                            <th>Customer</th>
                            <th>Expense</th>
                            <th>Amount</th>
                            <th>Description</th>
                            {{-- <th>Status</th> --}}
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($daybooks as $daybook)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('d-m-Y', strtotime($daybook->transaction_date)) }}</td>
                                <td>{{ $daybook->vendorTransaction->vendor->company_name ?? 'N/A' }}</td>
                                <td>{{ $daybook->customerTransaction->customer->name ?? 'N/A' }}</td>
                                <td>{{ $daybook->expense->name ?? 'N/A' }}</td>
                                <td>
                                    @if ($daybook->status == 1)
                                        <span class="badge bg-label-danger">{{ number_format($daybook->amount, 0) }}</span>
                                    @else
                                        <span class="badge bg-label-success">{{ number_format($daybook->amount, 0) }}</span>
                                    @endif
                                </td>
                                <td>{{ $daybook->description }}</td>
                                {{-- <td></td> --}}

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                @if (method_exists($daybooks, 'links'))
                    {{ $daybooks->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        @media print {
            .layout-menu,
            .layout-navbar,
            .btn,
            .card.mb-4, /* Hide the new filter card on print */
            form,
            .card-footer,
            .content-backdrop,
            footer {
                display: none !important;
            }

            .layout-page {
                padding: 0 !important;
                margin: 0 !important;
            }

            .content-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }

            .container-xxl {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .table {
                width: 100% !important;
                border: 1px solid #ddd !important;
            }

            .table th,
            .table td {
                border: 1px solid #ddd !important;
                padding: 8px !important;
            }

            .card-header h5 {
                font-size: 1.5rem !important;
                margin-bottom: 20px !important;
                text-align: center;
                display: block !important;
            }

            /* Ensure text colors are black for printing */
            body {
                color: #000 !important;
                background: #fff !important;
            }

            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
        }
    </style>
@endpush