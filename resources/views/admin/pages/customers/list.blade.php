@extends('admin.layout.master')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bx bx-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="bx bx-error-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Dashboard /</span> Customers
        </h4>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> New Customer
        </a>
    </div>

    <!-- Customers Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted text-uppercase small" style="background-color: #f8f9fa; border-bottom: 1px solid #eef2f0;">
                            <th class="px-4 py-3 fw-semibold">NAME</th>
                            <th class="py-3 fw-semibold">PERSON NAME</th>
                            <th class="py-3 fw-semibold">BALANCE</th>
                            <th class="py-3 fw-semibold">PHONE</th>
                            <th class="py-3 fw-semibold text-end pe-4">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr style="border-bottom: 1px solid #eef2f0;">
                            <td class="px-4 py-3">
                                <span class="fw-semibold text-dark">{{ $customer->name }}</span>
                            </td>
                            <td class="py-3">
                                {{ $customer->person_name ?? '-' }}
                            </td>
                            <td class="py-3">
                                <span class="fw-bold {{ $customer->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    PKR {{ number_format(abs($customer->balance), 2) }}
                                    @if($customer->balance != 0)
                                        <small class="text-muted">{{ $customer->balance >= 0 ? 'DR' : 'CR' }}</small>
                                    @endif
                                </span>
                            </td>
                            <td class="py-3">
                                {{ $customer->phone ?? '-' }}
                            </td>
                            <td class="py-3 text-end pe-4">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-icon rounded-circle text-muted" data-bs-toggle="dropdown" style="background: transparent; border: none;">
                                        <i class="bx bx-dots-vertical-rounded fs-5"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius: 10px;">
                                        <a class="dropdown-item py-2" href="{{ route('customers.view', $customer->uuid) }}">
                                            <i class="bx bx-show me-2 fs-6"></i> View
                                        </a>
                                        <a class="dropdown-item py-2" href="{{ route('customers.edit', $customer->uuid) }}">
                                            <i class="bx bx-edit-alt me-2 fs-6"></i> Edit
                                        </a>
                                        <a class="dropdown-item py-2" href="{{ route('customers.receive-payment', $customer->uuid) }}">
                                            <i class="bx bx-money me-2 fs-6"></i> Receive Payment
                                        </a>
                                        <a class="dropdown-item py-2" href="{{ route('customers.bank-statement', $customer->uuid) }}" target="_blank">
                                            <i class="bx bx-download me-2 fs-6"></i> Bank Statement
                                        </a>
                                        <hr class="my-1">
                                        <a class="dropdown-item py-2 text-danger action-confirm" href="javascript:void(0);" 
                                           data-url="{{ route('customers.delete', $customer->uuid) }}"
                                           data-text="You want to delete this customer!" 
                                           data-button-text="Yes, Delete it!">
                                            <i class="bx bx-trash me-2 fs-6"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bx bx-user-x bx-lg text-muted mb-3"></i>
                                <h6 class="text-muted">No customers found</h6>
                                <p class="text-muted small">Click "New Customer" to add your first customer.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap">
            <div class="text-muted small mb-2 mb-sm-0">
                Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
            </div>
            <div>
                {{ $customers->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endpush