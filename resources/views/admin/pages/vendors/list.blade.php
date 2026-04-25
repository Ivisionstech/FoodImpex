@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Purchaser List</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Purchaser</h5>
                <a href="{{ route('vendors.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>New Purchaser</a>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Person Name</th>
                            <th>Balance</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><a href="{{ route('vendors.view', $vendor->uuid) }}">{{ $vendor->company_name }}</a>
                                </td>
                                <td>{{ $vendor->person_name }}</td>
                                <td><span class="badge bg-label-primary me-1">PKR
                                        {{ number_format($vendor->balance, 2) }}</span></td>
                                <td>{{ $vendor->phone }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('vendors.view', $vendor->uuid) }}">View</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('vendors.edit', $vendor->uuid) }}">Edit</a></li>

                                            <li> <a class="dropdown-item action-confirm1"
                                                    data-url="{{ route('vendors.delete', $vendor->uuid) }}"
                                                    data-text="You want to delete this vendor!"
                                                    data-button-text="Yes, Delete it!" href="#">Delete</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('vendors.bills.create', $vendor->uuid) }}">Add
                                                    Bill</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('vendors.send-payment', $vendor->uuid) }}">Make
                                                    Payment</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $vendors->links() }}
            </div>
        </div>
    </div>
@endsection
