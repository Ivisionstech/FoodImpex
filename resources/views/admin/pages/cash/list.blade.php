@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Dashboard /</span> Cash</h4>
        
        <!-- Total Balance Card -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm hover-shadow transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-3">
                                    <i class="bx bx-money fs-4"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                <h6 class="text-muted mb-1">Total Cash Balance</h6>
                                <h3 class="mb-0 fw-bold text-success">PKR {{ number_format($totalBalance ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cash Entries</h5>
                <a class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addCashModal">
                    <i class="bx bx-plus me-1"></i> Add Cash
                </a>
            </div>
            
            @if ($cashes && $cashes->count() > 0)
                <div class="table-responsive text-nowrap" style="min-height: 320px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Balance</th>
                                <th>Type</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($cashes as $index => $cash)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('cash.view') }}">
                                            <span class="badge bg-label-success me-1">PKR
                                                {{ number_format($cash->balance, 2) }}
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info me-1">
                                            <i class="bx bx-arrow-up me-1"></i> CR (Credit)
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ date('d-m-Y', strtotime($cash->created_at)) }}</span>
                                            <small class="text-muted">{{ date('h:i A', strtotime($cash->created_at)) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('cash.view') }}">
                                                        <i class="bx bx-show-alt me-1"></i> View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addCashModal">
                                                        <i class="bx bx-plus-circle me-1"></i> Add Cash
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deductCashModal">
                                                        <i class="bx bx-minus-circle me-1"></i> Deduct Cash
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
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-money fs-1 text-muted"></i>
                    </div>
                    <h6 class="text-muted">No Cash Found</h6>
                    <p class="text-muted small mb-0">Click the "Add Cash" button to create a cash entry.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Cash Modal -->
    <div class="modal fade" id="addCashModal" tabindex="-1" aria-labelledby="addCashModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('cash.store') }}" method="POST" id="addCashForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title text-white" id="addCashModalLabel">
                            <i class="bx bx-plus-circle me-1 text-white"></i> Add Cash
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="balance" class="form-label fw-semibold">Balance <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" class="form-control" id="balance"
                                name="balance" placeholder="Enter cash balance" required>
                            <small class="text-muted">Enter the initial cash balance amount. (Credit by default)</small>
                        </div>
                        <div class="mb-3">
                            <label for="cash_date" class="form-label fw-semibold">Date <span class="text-muted">(Optional)</span></label>
                            <input type="date" class="form-control" id="cash_date" name="cash_date" value="{{ date('Y-m-d') }}">
                            <small class="text-muted">Leave empty to use current date.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Type</label>
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-1"></i> 
                                <strong>Credit (CR)</strong> - Cash will be added to your balance
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary-custom" id="addCashSubmit">
                            <i class="bx bx-check me-1"></i> Add Cash (CR)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Deduct Cash Modal -->
    <div class="modal fade" id="deductCashModal" tabindex="-1" aria-labelledby="deductCashModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('cash.deduct-cash') }}" method="POST" id="deductCashForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title text-white" id="deductCashModalLabel">
                            <i class="bx bx-minus-circle me-1 text-white"></i> Deduct Cash
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" class="form-control" id="amount"
                                name="amount" placeholder="Enter amount to deduct" required>
                            <small class="text-muted">Current Balance: PKR {{ number_format($totalBalance ?? 0, 2) }}</small>
                        </div>
                        <div class="mb-3">
                            <label for="cash_date" class="form-label fw-semibold">Date <span class="text-muted">(Optional)</span></label>
                            <input type="date" class="form-control" id="cash_date_deduct" name="cash_date" value="{{ date('Y-m-d') }}">
                            <small class="text-muted">Leave empty to use current date.</small>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Enter reason for deduction"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transaction Type</label>
                            <div class="alert alert-danger mb-0">
                                <i class="bx bx-info-circle me-1"></i> 
                                <strong>Debit (DR)</strong> - Cash will be deducted from your balance
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger-custom" id="deductCashSubmit">
                            <i class="bx bx-check me-1"></i> Deduct Cash (DR)
                        </button>
                    </div>
                </div>
            </form>
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
    
    .btn-danger-custom {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24) !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(255, 107, 107, 0.4) !important;
        transition: all 0.3s ease !important;
        padding: 0.5rem 1.2rem !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
    }
    
    .btn-danger-custom:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.5) !important;
        color: #ffffff !important;
        background: linear-gradient(45deg, #ee5a24, #d35400) !important;
    }
    
    .modal-header-custom {
        background: linear-gradient(135deg, #696cff, #5a5dff) !important;
        border-radius: 8px 8px 0 0 !important;
        border-bottom: none !important;
        padding: 1rem 1.5rem !important;
    }
    
    .modal-header-danger {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
        border-radius: 8px 8px 0 0 !important;
        border-bottom: none !important;
        padding: 1rem 1.5rem !important;
    }
    
    .dropdown-item:hover {
        background-color: rgba(105, 108, 255, 0.08) !important;
    }
    
    .form-control:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
    }

    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }
</style>

<script>
$(document).ready(function() {
    // ============================================
    // ADD CASH - Handle form submission
    // ============================================
    $('#addCashSubmit').on('click', function(e) {
        e.preventDefault();
        
        let form = $('#addCashForm');
        let submitBtn = $(this);
        let originalText = submitBtn.html();
        
        // Prevent double submission
        if (submitBtn.data('submitting')) {
            return false;
        }
        
        // Validate
        let balance = $('#balance').val();
        if (!balance || parseFloat(balance) <= 0) {
            toastr.error('Please enter a valid balance amount');
            return false;
        }
        
        // Disable button and show loading
        submitBtn.data('submitting', true);
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                submitBtn.data('submitting', false);
                
                if (response.status) {
                    toastr.success(response.message);
                    // Close modal
                    $('#addCashModal').modal('hide');
                    // Redirect to list page
                    window.location.href = response.redirect;
                } else {
                    toastr.error(response.message || 'Something went wrong');
                }
            },
            error: function(xhr) {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                submitBtn.data('submitting', false);
                
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            }
        });
    });

    // ============================================
    // DEDUCT CASH - Handle form submission
    // ============================================
    $('#deductCashSubmit').on('click', function(e) {
        e.preventDefault();
        
        let form = $('#deductCashForm');
        let submitBtn = $(this);
        let originalText = submitBtn.html();
        
        // Prevent double submission
        if (submitBtn.data('submitting')) {
            return false;
        }
        
        // Validate
        let amount = $('#amount').val();
        if (!amount || parseFloat(amount) <= 0) {
            toastr.error('Please enter a valid amount');
            return false;
        }
        
        // Disable button and show loading
        submitBtn.data('submitting', true);
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                submitBtn.data('submitting', false);
                
                if (response.status) {
                    toastr.success(response.message);
                    // Close modal
                    $('#deductCashModal').modal('hide');
                    // Redirect to list page
                    window.location.href = response.redirect;
                } else {
                    toastr.error(response.message || 'Something went wrong');
                }
            },
            error: function(xhr) {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                submitBtn.data('submitting', false);
                
                let errorMsg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            }
        });
    });

    // ============================================
    // RESET FORM STATES WHEN MODALS ARE CLOSED
    // ============================================
    $('#addCashModal').on('hidden.bs.modal', function() {
        let form = $('#addCashForm');
        form[0].reset();
        // Set default date
        let today = new Date().toISOString().split('T')[0];
        form.find('input[name="cash_date"]').val(today);
        let submitBtn = $('#addCashSubmit');
        submitBtn.html('<i class="bx bx-check me-1"></i> Add Cash (CR)');
        submitBtn.prop('disabled', false);
        submitBtn.data('submitting', false);
        $('.is-invalid').removeClass('is-invalid');
    });

    $('#deductCashModal').on('hidden.bs.modal', function() {
        let form = $('#deductCashForm');
        form[0].reset();
        // Set default date
        let today = new Date().toISOString().split('T')[0];
        form.find('input[name="cash_date"]').val(today);
        let submitBtn = $('#deductCashSubmit');
        submitBtn.html('<i class="bx bx-check me-1"></i> Deduct Cash (DR)');
        submitBtn.prop('disabled', false);
        submitBtn.data('submitting', false);
        $('.is-invalid').removeClass('is-invalid');
    });

    // ============================================
    // ENTER KEY SUPPORT FOR FORMS
    // ============================================
    $('#balance').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#addCashSubmit').click();
        }
    });

    $('#amount').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#deductCashSubmit').click();
        }
    });
});
</script>
@endpush