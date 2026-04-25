@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Dashboard /</span> Received Payments History
            </h4>
            <a href="{{ route('customers.receive-payment.general') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Receive New Payment
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('customers.receive-payment.list') }}" class="d-flex gap-2">
                    <input type="date" name="from_date" class="form-control" value="{{ $from_date }}">
                    <input type="date" name="to_date" class="form-control" value="{{ $to_date }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if (request()->has('from_date') || request()->has('to_date'))
                        <a href="{{ route('customers.receive-payment.list') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Method</th>
                            <th>Amount (CR)</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('d-m-Y h:i A') }}</td>
                                <td>
                                    @if($payment->customer)
                                        <a href="{{ route('customers.view', $payment->customer->uuid) }}">
                                            <strong>{{ $payment->customer->name }}</strong>
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info text-uppercase">
                                        {{ $payment->method ?? 'Cash' }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">PKR {{ number_format($payment->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($payment->description, 40) }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('customers.receive-payment.edit', $payment->uuid) }}">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>
                                            <button type="button" class="dropdown-item text-danger delete-payment-btn" data-payment-id="{{ $payment->uuid }}" data-customer-id="{{ $payment->customer->uuid ?? '' }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No payment history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete Payment Function
            document.querySelectorAll('.delete-payment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const paymentId = this.getAttribute('data-payment-id');
                    
                    if (confirm('Are you sure you want to delete this payment? This action will reverse the customer balance.')) {
                        const deleteUrl = `{{ route('customers.receive-payment.delete', ['uuid' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', paymentId);
                        
                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the payment.');
                        });
                    }
                });
            });
        });
    </script>
@endsection
