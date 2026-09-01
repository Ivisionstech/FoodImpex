@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Banks</h4>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Banks</h5>
                <a href="{{ route('banks.create') }}" class="btn btn-primary-custom"> 
                    <i class="bx bx-plus me-1"></i> New Bank
                </a>
            </div>
            <div class="table-responsive text-nowrap" style="min-height: 320px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Account Title</th>
                            <th>Account Number</th>
                            <th>Account Balance</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($banks as $bank)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ date('d-m-Y', strtotime($bank->created_at)) }}</span>
                                        <small class="text-muted">{{ date('h:i A', strtotime($bank->created_at)) }}</small>
                                    </div>
                                </td>
                                <td><a href="{{ route('banks.view', $bank->uuid) }}">{{ $bank->name }}</a>
                                </td>
                                <td>{{ $bank->account_title }}</td>
                                <td>{{ $bank->account_number }}</td>
                                <td><span class="badge bg-label-primary me-1">PKR
                                        {{ number_format($bank->account_balance, 2) }}</span></td>
                                <td>
                                    <span class="badge bg-label-info me-1">
                                        <i class="bx bx-arrow-up me-1"></i> CR
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('banks.view', $bank->uuid) }}">View</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('banks.edit', $bank->uuid) }}">Edit</a></li>

                                            <li> <a class="dropdown-item action-confirm"
                                                    data-url="{{ route('banks.delete', $bank->uuid) }}"
                                                    data-text="You want to delete this bank!"
                                                    data-button-text="Yes, Delete it!" href="#">Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $banks->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
    /* Custom Button Schema */
    .btn-primary-custom {
        background: linear-gradient(45deg, #696cff, #5a5dff) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(105, 108, 255, 0.4) !important;
        transition: all 0.3s ease !important;
        padding: 0.5rem 1.2rem !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.5) !important;
        color: #ffffff !important;
        background: linear-gradient(45deg, #5a5dff, #4a4de0) !important;
    }
    
    .btn-primary-custom:active {
        transform: translateY(0px) !important;
        box-shadow: 0 2px 4px rgba(105, 108, 255, 0.3) !important;
    }
    
    .btn-primary-custom:disabled {
        opacity: 0.7 !important;
        cursor: not-allowed !important;
        transform: none !important;
    }
</style>
@endpush