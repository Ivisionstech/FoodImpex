@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Header with breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <span class="text-muted fw-light">Dashboard /</span>
                    <a href="{{ route('products.list') }}" class="text-decoration-none">Products</a> /
                    <span class="text-muted">Add New Product</span>
                </h4>
                <p class="text-muted small mb-0">Create a new product in your inventory</p>
            </div>
            <div>
                <a href="{{ route('products.list') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">
                    <i class="bx bx-package me-2 text-primary"></i>
                    Product Information
                </h5>
                <p class="text-muted small mb-0 mt-1">Fields marked with <span class="text-danger">*</span> are required</p>
            </div>
            
            <div class="card-body p-4">
                <form novalidate class="ajax-form" action="{{ route('products.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Hidden fields for default values -->
                    <input type="hidden" name="purchase_price" value="0">
                    <input type="hidden" name="sale_price" value="0">
                    <input type="hidden" name="net_weight" value="0">
                    <input type="hidden" name="price_40kg" value="0">

                    <div class="row g-4 mb-4">
                        <!-- Product Name (Full Width) -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label fw-semibold" for="name">
                                    Product Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-package"></i></span>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name"
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Enter product name"
                                           required />
                                </div>
                                <div class="invalid-feedback" id="name-error"></div>
                                <small class="text-muted">This will be displayed in product listings</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <!-- Product Image -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label fw-semibold" for="image">Product Image</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bx bx-image"></i></span>
                                    <input type="file" 
                                           class="form-control form-control-lg @error('image') is-invalid @enderror" 
                                           id="image"
                                           name="image" 
                                           accept="image/*" />
                                </div>
                                <div class="invalid-feedback" id="image-error"></div>
                                <small class="text-muted">Optional: Upload product image (JPEG, PNG, JPG, WEBP)</small>
                                
                                <!-- Image Preview -->
                                <div id="image-preview-container" class="mt-3" style="display: none;">
                                    <img id="image-preview" 
                                         src="#" 
                                         alt="Preview" 
                                         style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid #dee2e6; padding: 5px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Description -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label fw-semibold" for="description">Description</label>
                                <textarea class="form-control form-control-lg @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description"
                                          rows="4"
                                          placeholder="Enter product description (optional)">{{ old('description') }}</textarea>
                                <div class="invalid-feedback" id="description-error"></div>
                                <small class="text-muted">Brief description of the product</small>
                            </div>
                        </div>
                    </div>

                    <!-- System Info Alert -->
                    <div class="alert alert-info bg-light border-0 d-flex align-items-center mb-4" style="border-radius: 12px;">
                        <i class="bx bx-info-circle fs-4 me-3 text-info"></i>
                        <div>
                            <strong>Note:</strong> Purchase price, sale price, net weight, and price per 40kg will be set to 0 by default. 
                            These values will be automatically updated when you create purchase bills for this product.
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" id="submitButton" class="btn btn-primary px-4">
                                <i class="bx bx-save me-2"></i> Create Product
                            </button>
                            <a href="{{ route('products.list') }}" class="btn btn-outline-secondary px-4">
                                <i class="bx bx-x me-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Custom styles for the form */
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .input-group-text {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .alert {
        background-color: #f0f9ff;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Image preview functionality
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('#image-preview').attr('src', event.target.result);
                    $('#image-preview-container').show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#image-preview-container').hide();
                $('#image-preview').attr('src', '#');
            }
        });

        // Form validation
        $('#submitButton').click(function(e) {
            const name = $('#name').val().trim();
            if (!name) {
                e.preventDefault();
                $('#name').addClass('is-invalid');
                $('#name-error').text('Product name is required').show();
            } else {
                $('#name').removeClass('is-invalid');
                $('#name-error').hide();
            }
        });

        // Remove validation on input
        $('#name').on('input', function() {
            $(this).removeClass('is-invalid');
            $('#name-error').hide();
        });
    });
</script>
@endpush