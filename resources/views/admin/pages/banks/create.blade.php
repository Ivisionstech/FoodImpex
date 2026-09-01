@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold  mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('banks.list') }}">Banks</a> /
            Add Bank
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Bank</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('banks.store') }}" method="POST" enctype="multipart/form-data"
                    novalidate id="bankForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_title">Account Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_title') is-invalid @enderror"
                                id="account_title" name="account_title" value="{{ old('account_title') }}" />
                            <div class="invalid-feedback" id="account_title-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_number">Account Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                id="account_number" name="account_number" value="{{ old('account_number') }}" />
                            <div class="invalid-feedback" id="account_number-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="account_balance">Account Balance <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('account_balance') is-invalid @enderror"
                                id="account_balance" name="account_balance" value="0" />
                            <div class="invalid-feedback" id="account_balance-error"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bank_date">Date <span class="text-muted">(Optional)</span></label>
                            <input type="date" class="form-control" id="bank_date" name="bank_date" value="{{ date('Y-m-d') }}" />
                            <small class="text-muted">Leave empty to use current date.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transaction Type</label>
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-1"></i> 
                                <strong>Credit (CR)</strong> - Bank opening balance will be added as credit
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="submitButton" class="btn btn-primary-custom">
                                <i class="bx bx-check me-1"></i> Create Bank (CR)
                            </button>
                            <a href="{{ route('banks.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
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
    
    .form-control:focus {
        border-color: #696cff !important;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
    }
</style>

<script>
$(document).ready(function() {
    // ============================================
    // HANDLE FORM SUBMISSION - CLICK EVENT
    // ============================================
    $('#submitButton').on('click', function(e) {
        e.preventDefault();
        
        let form = $('#bankForm');
        let submitBtn = $(this);
        let originalText = submitBtn.html();
        
        // Prevent double submission
        if (submitBtn.data('submitting')) {
            return false;
        }
        
        // Validate required fields
        let name = $('#name').val();
        let account_title = $('#account_title').val();
        let account_number = $('#account_number').val();
        let account_balance = $('#account_balance').val();
        
        if (!name) {
            toastr.error('Please enter bank name');
            return false;
        }
        if (!account_title) {
            toastr.error('Please enter account title');
            return false;
        }
        if (!account_number) {
            toastr.error('Please enter account number');
            return false;
        }
        if (!account_balance || parseFloat(account_balance) < 0) {
            toastr.error('Please enter a valid account balance');
            return false;
        }
        
        // Disable button and show loading
        submitBtn.data('submitting', true);
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Creating...');
        submitBtn.prop('disabled', true);
        
        // Create FormData object
        let formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                submitBtn.data('submitting', false);
                
                if (response.status) {
                    toastr.success(response.message);
                    // Redirect after short delay
                    setTimeout(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            location.reload();
                        }
                    }, 500);
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
    // ENTER KEY SUPPORT
    // ============================================
    $('#bankForm input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#submitButton').click();
        }
    });
});
</script>
@endpush