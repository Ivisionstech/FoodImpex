@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Breadcrumb -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Settings /</span> Company Information
        </h4>

        <div class="card">
            <h5 class="card-header">General Business Settings</h5>
            <div class="card-body">
                <!-- Form uses Ajax handling via 'company-ajax-form' class -->
                <form novalidate class="company-ajax-form" action="{{ route('company.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Row 1: Name and Email -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $company->name ?? '') }}" placeholder="Enter business name"
                                required />
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email">Official Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $company->email ?? '') }}" placeholder="info@company.com" />
                            <div class="invalid-feedback" id="email-error"></div>
                        </div>
                    </div>

                    <!-- Row 2: Contact Number and Address -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="mobile">Contact Number</label>
                            <input type="text" class="form-control" id="mobile" name="mobile"
                                value="{{ old('mobile', $company->mobile ?? '') }}" placeholder="+1 234 567 890" />
                            <div class="invalid-feedback" id="mobile-error"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="address">Physical Address</label>
                            <textarea class="form-control" id="address" name="address" rows="1" placeholder="Street, City, Country">{{ old('address', $company->address ?? '') }}</textarea>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>
                    </div>



                    <!-- Row 4: Logo Upload and Preview -->
                    <div class="row mb-3 align-items-start">
                        <div class="col-md-6">
                            <label class="form-label" for="logo">Company Logo</label>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*"
                                onchange="previewLogo(this)" />
                            <div class="invalid-feedback" id="logo-error"></div>
                            <p class="mt-2 small text-muted">Upload a high-quality logo (Max 2MB). Recommended square or
                                landscape format.</p>
                        </div>

                        <div class="col-md-6 text-center text-md-start">
                            <p class="mb-1 small text-muted fw-bold">Logo Preview:</p>
                            <div class="d-flex align-items-center justify-content-center border rounded bg-light shadow-sm"
                                style="width: 150px; height: 150px; overflow: hidden; padding: 10px;">

                                @php
                                    $logoUrl = null;
                                    if (
                                        isset($company) &&
                                        $company->logo &&
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo)
                                    ) {
                                        $logoUrl = \Illuminate\Support\Facades\Storage::url($company->logo);
                                    }
                                @endphp

                                @php
                                    $logoFullUrl = null;
                                    if (
                                        isset($company) &&
                                        $company->logo &&
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($company->logo)
                                    ) {
                                        $logoFullUrl = url(\Illuminate\Support\Facades\Storage::url($company->logo));
                                    }
                                @endphp

                                @if ($logoFullUrl)
                                    <img src="{{ $logoFullUrl }}" id="logo-preview" alt="Company Logo"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                @else
                                    <!-- Default placeholder -->
                                    <img src="{{ asset('assets/img/avatars/1.png') }}" id="logo-preview" alt="Default Logo"
                                        style="max-width: 100%; max-height: 100%; object-fit: contain; opacity: 0.5;">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="row mt-4">
                        <div class="col-12 text-start">
                            <button type="submit" id="submitButton" class="btn btn-primary shadow">
                                <i class="bx bx-check-circle me-1"></i> Save Company Information
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Image Preview Function
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').attr('src', e.target.result).css('opacity', '1');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 2. Ajax Form Submission
        $(document).ready(function() {
            $('.company-ajax-form').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let submitBtn = $('#submitButton');
                let formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    dataType: 'json', // Zaroori: batayein ke data JSON format mein hai
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        submitBtn.prop('disabled', true).html(
                            '<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
                        $('.invalid-feedback').text('');
                        $('.form-control').removeClass('is-invalid');
                    },
                    success: function(response) {
                        console.log(response); // Debugging ke liye check karein console mein
                        submitBtn.prop('disabled', false).html(
                            '<i class="bx bx-check-circle me-1"></i> Save Company Information'
                            );

                        // Condition ko asaan kar diya: status check karein
                        if (response.status === 'success' || response.success) {
                            Swal.fire({
                                icon: 'success', // GREEN CHECKMARK
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // Agar controller se status success nahi aata
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(
                            '<i class="bx bx-check-circle me-1"></i> Save Company Information'
                            );

                        if (xhr.status === 422) { // Validation Errors
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key).addClass('is-invalid');
                                $('#' + key + '-error').text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error', // RED CROSS
                                title: 'Error',
                                text: 'Something went wrong. Please check console.',
                            });
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
    </script>
@endsection
