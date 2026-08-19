@extends('admin.layout.master')

@section('content')
    <style>
        /* 3D Depth & Elevation Enhancements */
        .card-3d {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: #ffffff;
        }

        .card-3d:hover {
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.12), 0 10px 15px -5px rgba(0, 0, 0, 0.08);
        }

        .btn-3d {
            box-shadow: 0 4px 6px -1px rgba(105, 108, 255, 0.3), 0 2px 4px -1px rgba(105, 108, 255, 0.18);
            transition: all 0.2s ease-in-out;
        }

        .btn-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px -2px rgba(105, 108, 255, 0.4), 0 3px 6px -2px rgba(105, 108, 255, 0.2);
        }

        .btn-3d:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px -1px rgba(105, 108, 255, 0.3);
        }

        .badge-3d {
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 2px 4px rgba(105, 108, 255, 0.15);
            border: 1px solid rgba(105, 108, 255, 0.2);
        }

        .table-3d tbody tr {
            transition: all 0.2s ease;
        }

        .table-3d tbody tr:hover {
            background-color: rgba(105, 108, 255, 0.03);
            transform: scale(1.002);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .dropdown-menu-3d {
            border: none;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
        }

        .dropdown-menu-3d .dropdown-item {
            border-radius: 0.375rem;
            margin: 2px 6px;
            width: auto;
            transition: all 0.15s ease;
        }

        .dropdown-menu-3d .dropdown-item:hover {
            transform: translateX(3px);
        }
    </style>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Purchaser List</h4>
        <div class="card card-3d">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">Purchaser</h5>
                <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-3d"><i class="bx bx-plus me-1"></i>New Purchaser</a>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table table-3d">
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
                                <td>
                                    <a href="{{ route('vendors.view', $vendor->uuid) }}" class="fw-semibold text-primary">
                                        <i class="bx bx-building-house me-1 align-middle"></i>{{ $vendor->company_name }}
                                    </a>
                                </td>
                                <td><i class="bx bx-user me-1 text-muted align-middle"></i>{{ $vendor->person_name }}</td>
                                <td>
                                    <span class="badge bg-label-primary badge-3d me-1">
                                        PKR {{ number_format($vendor->balance, 2) }}
                                    </span>
                                </td>
                                <td><i class="bx bx-phone me-1 text-muted align-middle"></i>{{ $vendor->phone }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-label-secondary dropdown-toggle hide-arrow" type="button" id="dropdownMenuButton1"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-3d dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                            <li>
                                                <a class="dropdown-item text-info" href="{{ route('vendors.view', $vendor->uuid) }}">
                                                    <i class="bx bx-show me-2"></i>View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-primary" href="{{ route('vendors.edit', $vendor->uuid) }}">
                                                    <i class="bx bx-edit-alt me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger action-confirm1"
                                                    data-url="{{ route('vendors.delete', $vendor->uuid) }}"
                                                    data-text="You want to delete this vendor!"
                                                    data-button-text="Yes, Delete it!" href="#">
                                                    <i class="bx bx-trash me-2"></i>Delete
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-success" href="{{ route('vendors.bills.create', $vendor->uuid) }}">
                                                    <i class="bx bx-receipt me-2"></i>Add Bill
                                                </a>
                                            </li>
                                            {{-- Download Ledger Button --}}
                                            <li>
                                                <a class="dropdown-item text-warning" 
                                                    href="{{ route('vendors.bank-statement', ['uuid' => $vendor->uuid]) }}" 
                                                    target="_blank">
                                                    <i class="bx bx-download me-2"></i>Download Ledger
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
                {{ $vendors->links() }}
            </div>
        </div>
    </div>
@endsection