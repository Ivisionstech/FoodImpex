@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Expenses</h4>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('expenses.list') }}">
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
                                    <a href="{{ route('expenses.list') }}" class="btn btn-secondary w-100">Clear</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Expenses</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('expenses.create') }}" class="btn btn-primary">Add Expense</a>
                </div>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Amount</th>
                            {{-- <th>Description</th> --}}
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($expenses as $expense)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ date('d-m-Y', strtotime($expense->expense_date)) }}</td>
                                <td>{{ $expense->name }}</td>
                                <td>
                                    <span class="badge bg-label-danger">{{ number_format($expense->amount, 0) }}</span>
                                </td>
                                {{-- <td>{{ $expense->description }}</td> --}}
                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            {{-- <li><a class="dropdown-item"
                                                    href="{{ route('expenses.view', $expense->uuid) }}">View</a></li> --}}

                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $expenses->appends(request()->input())->links() }}
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
            .card.mb-4, /* Hide the filter card when printing */
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