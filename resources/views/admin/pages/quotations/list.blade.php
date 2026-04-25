@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4"><span class="text-muted fw-light">Dashboard /</span> Products</h4>

        {{-- Filter Section --}}
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route(Route::currentRouteName()) }}" method="GET">
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
                                    <a href="{{ route(Route::currentRouteName()) }}" class="btn btn-secondary w-100">Clear</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Quotations</h5>
                <a href="{{ route('quotations.create') }}" class="btn btn-primary">Add Quotation</a>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice Date</th>
                            <th>Consignee Name</th>
                            <th>Invoice No</th>
                            <th>FI No</th>
                            <th>Destination</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($quotations as $quotation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $quotation->invoice_date }}</td>
                                <td>{{ $quotation->consignee_name }}</td>
                                <td>{{ $quotation->invoice_no }}</td>
                                <td>{{ $quotation->fi_no }}</td>
                                <td>{{ $quotation->destination }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('quotations.view', $quotation->uuid) }}">View</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('quotations.edit', $quotation->uuid) }}">Edit</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('quotations.download', $quotation->uuid) }}">Download
                                                    PDF</a></li>
                                            <li>                                      
                                                <a href="#" class="dropdown-item action-confirm"
                                                data-url="{{ route('quotations.delete', $quotation->uuid) }}"
                                                data-text="You want to delete this quotation!"
                                                data-button-text="Yes, Delete it!">
                                                Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                @if (method_exists($quotations, 'links'))
                    {{ $quotations->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </div>
    
@endsection