@extends('admin.layout.master')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Dashboard /</span>
        <a href="{{ route('customers.list') }}">Customers</a> /
        Edit Bill
    </h4>

    <div class="card">
        <h5 class="card-header">Edit Bill</h5>
        <div class="card-body">
            <form action="{{ route('bills.update', $bill->uuid) }}" method="POST" id="billForm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="bill_date">Bill Date</label>
                        <input type="datetime-local" class="form-control" id="bill_date" name="bill_date"
                            value="{{ old('bill_date', \Carbon\Carbon::parse($bill->bill_date)->format('Y-m-d\TH:i')) }}"
                            required />
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="customer_type">Customer Type</label>
                        <select class="form-select @error('customer_type') is-invalid @enderror" id="customer_type"
                                name="customer_type" required disabled>
                                <option value="existing" selected>Existing Customer</option>
                                <option value="walk_in" class="d-none">Walk-in Customer</option>
                            </select>
                    </div>
                </div>

                <!-- Existing Customer -->
                <div class="row mb-3" id="existing_customer_section"
                    style="{{ $bill->customer_id ? '' : 'display:none;' }}">
                    <div class="col-md-6">
                        <label class="form-label" for="customer_id">Select Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">Select a customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $bill->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Walk-in -->
                <div class="row mb-3" id="walk_in_customer_section"
                    style="{{ !$bill->customer_id ? '' : 'display:none;' }}">
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                            value="{{ old('customer_name', $bill->customer_name) }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Phone</label>
                        <input type="text" class="form-control" name="customer_phone"
                            value="{{ old('customer_phone', $bill->customer_phone) }}" />
                    </div>
                </div>

                <!-- Products -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="mb-3">Products</h6>
                        <div id="products-container">
                            @foreach ($bill->billProducts as $index => $bp)
                                <div class="product-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Select Product</label>
                                                <select class="form-select product-select" name="products[{{ $index }}][product_id]" required>
                                                    <option value="">Select a product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-sale-price="{{ $product->sale_price }}"
                                                            data-stock="{{ $product->stock }}"
                                                            {{ $product->id == $bp->product_id ? 'selected' : '' }}>
                                                            {{ $product->name }} (Stock: {{ $product->stock }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" class="form-control quantity-input"
                                                    name="products[{{ $index }}][quantity]"
                                                    value="{{ $bp->quantity }}" min="1" required />
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Price</label>
                                                <input type="number" class="form-control price-input"
                                                    name="products[{{ $index }}][price]"
                                                    value="{{ $bp->price }}" step="0.01" required />
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Total</label>
                                                <input type="text" class="form-control total-display"
                                                    value="{{ $bp->total }}" readonly />
                                            </div>
                                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-product">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary mt-3" id="add-product">
                            <i class="bx bx-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <!-- Extra Charges -->
                <div class="row mb-3">
                    <div class="col-12">
                        <h6 class="mb-3">Extra Charges</h6>
                        <div id="extra-charges-container">
                            @foreach ($bill->extraCharges as $index => $charge)
                                <div class="extra-charge-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-5 mb-3">
                                                <label class="form-label">Charge Name</label>
                                                <input type="text" class="form-control"
                                                    name="extra_charges[{{ $index }}][name]"
                                                    value="{{ $charge->name }}" required />
                                            </div>
                                            <div class="col-md-5 mb-3">
                                                <label class="form-label">Amount</label>
                                                <input type="number" class="form-control"
                                                    name="extra_charges[{{ $index }}][amount]"
                                                    value="{{ $charge->amount }}" step="0.01" required />
                                            </div>
                                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-danger remove-extra-charge">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary mt-3" id="add-extra-charge">
                            <i class="bx bx-plus"></i> Add Extra Charge
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">Update Bill</button>
                        <a href="{{ route('bills.list') }}" class="btn btn-secondary">Cancel</a>
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
                            <option value="{{ $product->id }}" 
                                data-sale-price="{{ $product->sale_price }}"
                                data-stock="{{ $product->stock }}">
                                {{ $product->name }} (Stock: {{ $product->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control quantity-input" 
                        name="products[INDEX][quantity]" min="1" value="1" required />
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" class="form-control price-input" 
                        name="products[INDEX][price]" step="0.01" required />
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

<!-- Extra Charge Template -->
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
                    <input type="number" class="form-control" name="extra_charges[INDEX][amount]" 
                        step="0.01" required />
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
        } else {
            existingCustomerSection.style.display = 'none';
            walkInCustomerSection.style.display = 'block';
            customerIdSelect.required = false;
            customerNameInput.required = true;
            customerIdSelect.value = '';
        }
    }

    customerTypeSelect.addEventListener('change', updateCustomerRequirements);
    updateCustomerRequirements();

    // Product Handling
    const productsContainer = document.getElementById('products-container');
    const addProductBtn = document.getElementById('add-product');
    const productTemplate = document.getElementById('product-row-template');
    let productIndex = {{ ($bill->billProducts ?? collect())->count() }};

    function updateProductTotal(row) {
        const q = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const p = parseFloat(row.querySelector('.price-input').value) || 0;
        row.querySelector('.total-display').value = (q * p).toFixed(2);
    }

    function attachProductEventListeners(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const priceInput = row.querySelector('.price-input');
        const removeBtn = row.querySelector('.remove-product');

        productSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                const salePrice = option.dataset.salePrice;
                const stock = parseInt(option.dataset.stock);
                priceInput.value = salePrice;
                quantityInput.max = stock;
                updateProductTotal(row);
            }
        });

        quantityInput.addEventListener('input', function() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.stock) {
                const stock = parseInt(selectedOption.dataset.stock);
                if (this.value > stock) {
                    this.value = stock;
                    alert('Quantity cannot exceed available stock');
                }
            }
            updateProductTotal(row);
        });

        priceInput.addEventListener('input', () => updateProductTotal(row));
        removeBtn.addEventListener('click', () => row.remove());
    }

    // Attach event listeners to existing product rows
    document.querySelectorAll('.product-row').forEach(row => {
        attachProductEventListeners(row);
    });

    function addProductRow() {
        const clone = productTemplate.content.cloneNode(true);
        const row = clone.querySelector('.product-row');

        row.querySelectorAll('[name]').forEach(i => {
            i.name = i.name.replace('INDEX', productIndex);
        });

        productsContainer.appendChild(row);
        
        // Attach event listeners to the newly added row
        const addedRow = productsContainer.lastElementChild;
        attachProductEventListeners(addedRow);
        
        productIndex++;
    }

    addProductBtn.addEventListener('click', addProductRow);

    // Extra Charges
    const extraChargesContainer = document.getElementById('extra-charges-container');
    const addExtraChargeBtn = document.getElementById('add-extra-charge');
    const extraChargeTemplate = document.getElementById('extra-charge-template');
    let extraChargeIndex = {{ ($bill->extraCharges ?? collect())->count() }};

    function attachExtraChargeEventListeners(row) {
        row.querySelector('.remove-extra-charge').addEventListener('click', () => row.remove());
    }

    // Attach event listeners to existing extra charge rows
    document.querySelectorAll('.extra-charge-row').forEach(row => {
        attachExtraChargeEventListeners(row);
    });

    function addExtraChargeRow() {
        const clone = extraChargeTemplate.content.cloneNode(true);
        const row = clone.querySelector('.extra-charge-row');
        
        row.querySelectorAll('[name]').forEach(i => {
            i.name = i.name.replace('INDEX', extraChargeIndex);
        });
        
        extraChargesContainer.appendChild(row);
        
        // Attach event listeners to the newly added row
        const addedRow = extraChargesContainer.lastElementChild;
        attachExtraChargeEventListeners(addedRow);
        
        extraChargeIndex++;
    }

    addExtraChargeBtn.addEventListener('click', addExtraChargeRow);
});
</script>
@endpush

@endsection