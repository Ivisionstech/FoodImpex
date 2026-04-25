@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('customers.list') }}">Customers</a> /
            Create Bill
        </h4>

        <div class="card">
            <h5 class="card-header">Create New Bill</h5>
            <div class="card-body">
                <form action="{{ route('customers.bills.store') }}" method="POST" id="billForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="bill_date">Bill Date</label>
                            <input type="datetime-local" class="form-control @error('bill_date') is-invalid @enderror"
                                id="bill_date" name="bill_date" value="{{ old('bill_date', now()) }}" required />
                            @error('bill_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_type">Customer Type</label>
                            <select class="form-select @error('customer_type') is-invalid @enderror" id="customer_type"
                                name="customer_type" required disabled>
                                <option value="existing" selected>Existing Customer</option>
                                <option value="walk_in" class="d-none">Walk-in Customer</option>
                            </select>
                            @error('customer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" id="existing_customer_section">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_id">Select Customer <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id"
                                name="customer_id" required>
                                <option value="">Select a customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="invalid-feedback" id="customer-required-feedback" style="display: none;">
                                Please select a customer.
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3" id="walk_in_customer_section" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_name">Customer Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                id="customer_name" name="customer_name" value="{{ old('customer_name') }}" />
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="customer_phone">Customer Phone</label>
                            <input type="text" class="form-control @error('customer_phone') is-invalid @enderror"
                                id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" />
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    

                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3">Products</h6>
                            <div id="products-container">
                                <!-- Product rows will be added here -->
                            </div>
                            <button type="button" class="btn btn-primary mt-3" id="add-product">
                                <i class="bx bx-plus"></i> Add Product
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3">Extra Charges</h6>
                            <div id="extra-charges-container">
                                <!-- Extra charges will be added here -->
                            </div>
                            <button type="button" class="btn btn-primary mt-3" id="add-extra-charge">
                                <i class="bx bx-plus"></i> Add Extra Charge
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Create Bill</button>
                            <a href="{{ route('customers.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Row Template -->
    <template id="product-row-template">
        <div class="product-row card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Select Product</label>
                        <select class="form-select product-select" name="products[INDEX][product_id]" required>
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-sale-price="{{ $product->sale_price }}"
                                    data-stock="{{ $product->stock }}">
                                    {{ $product->name }} (Stock: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity-input" name="products[INDEX][quantity]"
                            min="1" value="1" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Sale Price</label>
                        <input type="number" step="0.01" class="form-control price-input"
                            name="products[INDEX][price]" min="0" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control total-display" readonly />
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-product">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Extra Charge Row Template -->
    <template id="extra-charge-template">
        <div class="extra-charge-row card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Charge Name</label>
                        <input type="text" class="form-control" name="extra_charges[INDEX][name]" required />
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="extra_charges[INDEX][amount]"
                            min="0" required />
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-extra-charge">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('styles')
    <style>
        .required-field {
            border-left: 3px solid #dc3545;
        }

        .required-field:focus {
            border-left: 3px solid #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@endpush

@push('scripts')
   <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
    // Initialize Select2 on customer type
    $('#customer_type').select2({
        placeholder: "Select Customer Type",
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 on customer select dropdown
    $('#customer_id').select2({
        placeholder: "Select a customer",
        allowClear: true,
        width: '100%'
    });
});
        document.addEventListener('DOMContentLoaded', function() {
            // Customer type handling
            const customerTypeSelect = document.getElementById('customer_type');
            const existingCustomerSection = document.getElementById('existing_customer_section');
            const walkInCustomerSection = document.getElementById('walk_in_customer_section');
            const customerIdSelect = document.getElementById('customer_id');
            const customerNameInput = document.getElementById('customer_name');
            const billForm = document.getElementById('billForm');

            function updateCustomerRequirements() {
                if (customerTypeSelect.value === 'existing') {
                    existingCustomerSection.style.display = 'block';
                    walkInCustomerSection.style.display = 'none';
                    customerIdSelect.required = true;
                    customerNameInput.required = false;
                    customerIdSelect.classList.add('required-field');
                    customerNameInput.classList.remove('required-field');
                } else {
                    existingCustomerSection.style.display = 'none';
                    walkInCustomerSection.style.display = 'block';
                    customerIdSelect.required = false;
                    customerNameInput.required = true;
                    customerIdSelect.classList.remove('required-field');
                    customerNameInput.classList.add('required-field');
                    // Clear customer_id value when switching to walk-in
                    customerIdSelect.value = '';
                }
            }

            customerTypeSelect.addEventListener('change', updateCustomerRequirements);

            // Form validation
            billForm.addEventListener('submit', function(e) {
                let isValid = true;

                // Reset previous validation states
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.style.display = 'none';
                });

                // Validate customer selection
                if (customerTypeSelect.value === 'existing') {
                    if (!customerIdSelect.value) {
                        customerIdSelect.classList.add('is-invalid');
                        document.getElementById('customer-required-feedback').style.display = 'block';
                        isValid = false;
                    }
                } else if (customerTypeSelect.value === 'walk_in') {
                    if (!customerNameInput.value.trim()) {
                        customerNameInput.classList.add('is-invalid');
                        isValid = false;
                    }
                }

                // Validate that at least one product is added
                const productRows = document.querySelectorAll('.product-row');
                if (productRows.length === 0) {
                    alert('Please add at least one product to the bill.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first invalid field
                    const firstInvalid = document.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }
                }
            });

            // Initialize on page load
            updateCustomerRequirements();

            // Product handling
            const productsContainer = document.getElementById('products-container');
            const addProductBtn = document.getElementById('add-product');
            const productTemplate = document.getElementById('product-row-template');
            let productIndex = 0;

            function updateProductTotal(productRow) {
                const quantity = parseFloat(productRow.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(productRow.querySelector('.price-input').value) || 0;
                const total = quantity * price;
                productRow.querySelector('.total-display').value = total.toFixed(2);
            }

            function addProductRow() {
                const clone = productTemplate.content.cloneNode(true);
                const productRow = clone.querySelector('.product-row');

                // Update all name attributes with the current index
                productRow.querySelectorAll('[name]').forEach(input => {
                    input.name = input.name.replace('INDEX', productIndex);
                });

                // Add event listeners
                const productSelect = productRow.querySelector('.product-select');
                const quantityInput = productRow.querySelector('.quantity-input');
                const priceInput = productRow.querySelector('.price-input');
                const removeBtn = productRow.querySelector('.remove-product');

                productSelect.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    if (option.value) {
                        const salePrice = option.dataset.salePrice;
                        const stock = parseInt(option.dataset.stock);
                        priceInput.value = salePrice;
                        quantityInput.max = stock;
                        updateProductTotal(productRow);
                    }
                });

                quantityInput.addEventListener('input', function() {
                    const stock = parseInt(productSelect.options[productSelect.selectedIndex].dataset
                        .stock);
                    if (this.value > stock) {
                        this.value = stock;
                        alert('Quantity cannot exceed available stock');
                    }
                    updateProductTotal(productRow);
                });

                priceInput.addEventListener('input', function() {
                    updateProductTotal(productRow);
                });

                removeBtn.addEventListener('click', function() {
                    productRow.remove();
                });

                productsContainer.appendChild(productRow);
                productIndex++;
            }

            // Add first product row by default
            addProductRow();

            // Add product button click handler
            addProductBtn.addEventListener('click', addProductRow);

            // Extra charges handling
            const extraChargesContainer = document.getElementById('extra-charges-container');
            const addExtraChargeBtn = document.getElementById('add-extra-charge');
            const extraChargeTemplate = document.getElementById('extra-charge-template');
            let extraChargeIndex = 0;

            function addExtraChargeRow() {
                const clone = extraChargeTemplate.content.cloneNode(true);
                const extraChargeRow = clone.querySelector('.extra-charge-row');

                // Update all name attributes with the current index
                extraChargeRow.querySelectorAll('[name]').forEach(input => {
                    input.name = input.name.replace('INDEX', extraChargeIndex);
                });

                // Add remove button event listener
                const removeBtn = extraChargeRow.querySelector('.remove-extra-charge');
                removeBtn.addEventListener('click', function() {
                    extraChargeRow.remove();
                });

                extraChargesContainer.appendChild(extraChargeRow);
                extraChargeIndex++;
            }

            // Add extra charge button click handler
            addExtraChargeBtn.addEventListener('click', addExtraChargeRow);
        });
    </script>
@endpush
