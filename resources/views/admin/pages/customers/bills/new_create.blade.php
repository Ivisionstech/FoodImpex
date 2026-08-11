@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('customers.list') }}" class="text-decoration-none">Customers</a> /
            <span class="text-muted">Create Invoice</span>
        </h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i>
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-1"></i>
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                <h5 class="mb-0">
                    <i class="bx bx-receipt me-2 text-primary"></i>
                    Create New Sales Invoice
                </h5>
                <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
            </div>
            
            <div class="card-body">
                <form action="{{ route('customers.bills.store') }}" method="POST" id="billForm">
                    @csrf
                    <input type="hidden" name="type" value="new bill">

                    <!-- Bill Information Row -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="bill_date">
                                Invoice Date <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control @error('bill_date') is-invalid @enderror" 
                                   id="bill_date" name="bill_date" value="{{ old('bill_date', now()->format('Y-m-d\TH:i')) }}" required />
                            @error('bill_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="payment_terms">Payment Terms</label>
                            <input type="text" class="form-control" name="payment_terms" 
                                   value="{{ old('payment_terms', '100% IN 30 DAYS') }}" />
                            <div class="form-text">e.g., 100% IN 30 DAYS, Net 60, etc.</div>
                        </div>
                    </div>

                    <!-- Customer Selection Section -->
                    <input type="hidden" name="customer_type" value="existing">

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="customer_id">
                                Select Customer <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Choose a customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone ?? 'No Phone' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Products Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bx bx-package me-1 text-primary"></i>
                                    Products / Items
                                </h6>
                                <span class="badge bg-label-info rounded-pill" id="items-count">0 Items</span>
                            </div>
                            
                            <div id="products-container" class="mb-3">
                                <!-- Product rows will be added here -->
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary" id="add-product">
                                <i class="bx bx-plus"></i> Add Product
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Extra Charges Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bx bx-minus-circle me-1 text-danger"></i>
                                    Extra Charges / Deductions (Subtract)
                                </h6>
                                <span class="badge bg-label-warning rounded-pill" id="charges-count">0 Charges</span>
                            </div>
                            
                            <div id="extra-charges-container" class="mb-3">
                                <!-- Extra charges will be added here -->
                            </div>
                            
                            <button type="button" class="btn btn-outline-danger" id="add-extra-charge">
                                <i class="bx bx-minus"></i> Add Extra Charge
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Summary Section -->
                    <div class="row">
                        <div class="col-md-5 col-lg-4 ms-auto">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title mb-3 text-muted">Invoice Summary</h6>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal:</span>
                                        <span class="fw-semibold" id="subtotal-display">PKR 0.00</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Extra Charges (Subtract):</span>
                                        <span class="fw-semibold text-danger" id="extra-charges-total-display">PKR 0.00</span>
                                    </div>
                                    
                                    <hr class="my-2">
                                    
                                    <div class="d-flex justify-content-between mt-3 pt-2">
                                        <span class="fw-bold fs-5">Grand Total:</span>
                                        <h4 class="fw-bold text-primary mb-0" id="grand-total-display">PKR 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row mt-4 pt-3">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                <i class="bx bx-save me-1"></i> Create Invoice (Pending Approval)
                            </button>
                            <a href="{{ route('customers.list') }}" class="btn btn-outline-secondary px-4">
                                <i class="bx bx-x me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Row Template -->
    <template id="product-row-template">
        <div class="product-row card mb-3 border">
            <div class="card-body p-3">
                <div class="row g-3">
                    <!-- Row 1: Product, Description, Qty, Packing, Total Weight -->
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Product <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm product-select" name="products[INDEX][product_id]" required>
                                    <option value="">Select product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-stock="{{ $product->stock }}" data-price="{{ $product->sale_price }}">
                                            {{ $product->name }} (Stock: {{ $product->stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Description</label>
                                <input type="text" class="form-control form-control-sm" name="products[INDEX][description]" placeholder="Optional" />
                            </div>

                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm quantity-input" name="products[INDEX][quantity]" min="1" value="1" required />
                            </div>

                            <div class="col-md-1">
                                <label class="form-label small fw-semibold">Pack (KG)</label>
                                <input type="text" class="form-control form-control-sm packing-input" name="products[INDEX][packing]" placeholder="KG" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Total Weight (Gross)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm total-weight-input" name="products[INDEX][total_weight]" placeholder="0.00" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Bardana Wt, Net Weight, Rate per 40 kgs, Total Amount, Remove Button -->
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Bardana Wt</label>
                                <input type="number" step="0.01" class="form-control form-control-sm bardana-weight-input" name="products[INDEX][bardana_weight]" placeholder="0.00" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-success">Net Weight</label>
                                <input type="number" step="0.01" class="form-control form-control-sm net-weight-input bg-light" name="products[INDEX][net_weight]" readonly />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Rate per 40 kgs</label>
                                <input type="number" step="0.01" class="form-control form-control-sm rate-40-input" name="products[INDEX][rate_per_40kg]" placeholder="0.00" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Total Amount</label>
                                <input type="hidden" class="total-raw" name="products[INDEX][total]" />
                                <input type="text" class="form-control form-control-sm total-display bg-light" readonly placeholder="0.00" />
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger remove-product w-100" title="Remove Product">
                                    <i class="bx bx-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Extra Charge Template -->
    <template id="extra-charge-template">
        <div class="extra-charge-row card mb-2 border">
            <div class="card-body py-2 px-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Charge Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="extra_charges[INDEX][name]" placeholder="e.g., Shipping, Discount" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control form-control-sm extra-amount-input" name="extra_charges[INDEX][amount]" placeholder="0.00" required />
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger w-100 remove-extra-charge">
                            <i class="bx bx-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .card.border {
            transition: all 0.2s ease;
        }
        .card.border:hover {
            border-color: #696cff !important;
            box-shadow: 0 2px 8px rgba(105, 108, 255, 0.1);
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
        .form-control-sm, .form-select-sm {
            font-size: 0.875rem;
        }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }
        .badge {
            font-weight: 500;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for customer dropdown
            $('#customer_id').select2({
                placeholder: "Choose a customer",
                allowClear: true,
                width: '100%'
            });
            
            let productIndex = 0;
            let extraChargeIndex = 0;

            // Function to update net weight
            function updateNetWeight(productRow) {
                const totalWeight = parseFloat(productRow.find('.total-weight-input').val()) || 0;
                const bardana = parseFloat(productRow.find('.bardana-weight-input').val()) || 0;
                const netWeight = Math.max(0, totalWeight - bardana);
                productRow.find('.net-weight-input').val(netWeight.toFixed(2));
                return netWeight;
            }

            // Function to update line total
            function updateLineTotal(productRow) {
                const netWeight = parseFloat(productRow.find('.net-weight-input').val()) || 0;
                const rate = parseFloat(productRow.find('.rate-40-input').val()) || 0;
                const lineTotal = netWeight > 0 && rate > 0 ? (netWeight * rate) / 40 : 0;
                productRow.find('.total-raw').val(lineTotal.toFixed(2));
                productRow.find('.total-display').val(lineTotal.toFixed(2));
                return lineTotal;
            }

            // Function to calculate all totals
            function calculateTotals() {
                let subtotal = 0;
                let extraTotal = 0;
                
                $('.product-row').each(function() {
                    const lineTotal = parseFloat($(this).find('.total-raw').val()) || 0;
                    subtotal += lineTotal;
                });
                
                $('.extra-charge-row').each(function() {
                    const amount = parseFloat($(this).find('.extra-amount-input').val()) || 0;
                    extraTotal += amount;
                });
                
                const grandTotal = Math.max(0, subtotal - extraTotal);
                
                $('#subtotal-display').text('PKR ' + subtotal.toFixed(2));
                $('#extra-charges-total-display').text('PKR ' + extraTotal.toFixed(2));
                $('#grand-total-display').text('PKR ' + grandTotal.toFixed(2));
                
                $('#items-count').text($('.product-row').length + ' Item' + ($('.product-row').length !== 1 ? 's' : ''));
                $('#charges-count').text($('.extra-charge-row').length + ' Charge' + ($('.extra-charge-row').length !== 1 ? 's' : ''));
            }

            // Add product row
            function addProductRow() {
                const template = $('#product-row-template').html();
                const row = $(template.replace(/INDEX/g, productIndex));
                
                // Initialize Select2 for product dropdown
                row.find('.product-select').select2({
                    placeholder: 'Select product',
                    allowClear: true,
                    width: '100%'
                });
                
                // Stock validation on product select
                row.find('.product-select').on('change', function() {
                    const option = $(this).find('option:selected');
                    const stock = option.data('stock');
                    if (stock) {
                        row.find('.quantity-input').attr('max', stock);
                    }
                    calculateTotals();
                });
                
                // Net weight calculation
                row.find('.total-weight-input, .bardana-weight-input').on('input', function() {
                    updateNetWeight(row);
                    updateLineTotal(row);
                    calculateTotals();
                });
                
                // Rate calculation
                row.find('.rate-40-input').on('input', function() {
                    updateLineTotal(row);
                    calculateTotals();
                });
                
                // Quantity change
                row.find('.quantity-input').on('input', function() {
                    calculateTotals();
                });
                
                // Remove product
                row.find('.remove-product').on('click', function() {
                    row.remove();
                    calculateTotals();
                });
                
                $('#products-container').append(row);
                productIndex++;
                calculateTotals();
            }

            // Add extra charge
            function addExtraCharge() {
                const template = $('#extra-charge-template').html();
                const row = $(template.replace(/INDEX/g, extraChargeIndex));
                
                row.find('.extra-amount-input').on('input', function() {
                    calculateTotals();
                });
                
                row.find('.remove-extra-charge').on('click', function() {
                    row.remove();
                    calculateTotals();
                });
                
                $('#extra-charges-container').append(row);
                extraChargeIndex++;
                calculateTotals();
            }

            // Initialize with one product row
            addProductRow();
            
            // Event listeners for add buttons
            $('#add-product').on('click', addProductRow);
            $('#add-extra-charge').on('click', addExtraCharge);

            // Form validation before submit
            $('#billForm').on('submit', function(e) {
                // Check if at least one product is added
                if ($('.product-row').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one product to the invoice.');
                    return false;
                }

                // Check if customer is selected
                if (!$('#customer_id').val()) {
                    e.preventDefault();
                    alert('Please select a customer.');
                    $('#customer_id').focus();
                    return false;
                }

                // Check if any product has a total amount > 0
                let hasValidProduct = false;
                $('.product-row').each(function() {
                    const total = parseFloat($(this).find('.total-raw').val()) || 0;
                    if (total > 0) {
                        hasValidProduct = true;
                    }
                });

                if (!hasValidProduct) {
                    e.preventDefault();
                    alert('Please ensure at least one product has a valid amount.');
                    return false;
                }

                // Log form data for debugging
                console.log('Form submitted with', $('.product-row').length, 'products');
                console.log('Grand total:', $('#grand-total-display').text());
                
                // Show loading state
                $('#submitBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
            });
        });
    </script>
@endpush