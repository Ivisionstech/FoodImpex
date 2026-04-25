@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('customers.list') }}">Customers</a> /
            Edit New Sales Bill
        </h4>

        <div class="card">
            <h5 class="card-header">Edit New Sales Bill</h5>
            <div class="card-body">
                <form action="{{ route('bills.update', $bill->uuid) }}" method="POST" id="billForm">
                    @csrf
                    <input type="hidden" name="type" value="new bill">
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
                            <label class="form-label" for="payment_terms">Payment Terms</label>
                            <input type="text" class="form-control @error('payment_terms') is-invalid @enderror"
                                id="payment_terms" name="payment_terms"
                                value="{{ old('payment_terms', $bill->payment_terms) }}" />
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                    <div class="row mb-3" id="existing_customer_section"
                        style="{{ $bill->customer_id ? '' : 'display:none;' }}">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_id">Select Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select a customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                        {{ $bill->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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

                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3">Products</h6>
                            <div id="products-container">
                                @foreach ($bill->billProducts as $index => $bp)
                                    <div class="product-row card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Select Product</label>
                                                    <select class="form-select product-select"
                                                        name="products[{{ $index }}][product_id]" required>
                                                        <option value="">Select a product</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}"
                                                                data-stock="{{ $product->stock }}"
                                                                {{ $product->id == $bp->product_id ? 'selected' : '' }}>
                                                                {{ $product->name }} (Stock: {{ $product->stock }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control"
                                                        name="products[{{ $index }}][description]"
                                                        value="{{ old('products.' . $index . '.description', $bp->description ?? '') }}" />
                                                </div>

                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" class="form-control quantity-input"
                                                        name="products[{{ $index }}][quantity]"
                                                        value="{{ $bp->quantity }}" min="1" required />
                                                </div>

                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Packing (KG)</label>
                                                    <input type="text" class="form-control"
                                                        name="products[{{ $index }}][packing]"
                                                        value="{{ old('products.' . $index . '.packing', $bp->packing ?? '') }}" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Total Weight </label>
                                                    <input type="number" step="0.01"
                                                        class="form-control total-weight-input"
                                                        name="products[{{ $index }}][total_weight]"
                                                        value="{{ old('products.' . $index . '.total_weight', $bp->total_weight ?? '') }}"
                                                        min="0" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Bardana Weight </label>
                                                    <input type="number" step="0.01"
                                                        class="form-control bardana-weight-input"
                                                        name="products[{{ $index }}][bardana_weight]"
                                                        value="{{ old('products.' . $index . '.bardana_weight', $bp->bardana_weight ?? '') }}"
                                                        min="0" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Net Weight </label>
                                                    <input type="number" step="0.01"
                                                        class="form-control net-weight-input"
                                                        name="products[{{ $index }}][net_weight]"
                                                        value="{{ old('products.' . $index . '.net_weight', $bp->net_weight ?? '') }}"
                                                        readonly />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Rate per 40 KGS</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control rate-40-input"
                                                        name="products[{{ $index }}][rate_per_40kg]"
                                                        value="{{ old('products.' . $index . '.rate_per_40kg', $bp->rate_per_40kg ?? '') }}"
                                                        min="0" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Total</label>
                                                    <input type="hidden" class="total-raw"
                                                        name="products[{{ $index }}][total_raw]"
                                                        value="{{ old('products.' . $index . '.total_raw', $bp->total ?? 0) }}" />
                                                    <input type="text" class="form-control total-display"
                                                        name="products[{{ $index }}][total]"
                                                        value="{{ number_format((float) ($bp->total ?? 0), 2, '.', ',') }}"
                                                        readonly />
                                                </div>

                                                <div class="col-md-1 mb-3 d-flex align-items-end">
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
                                <i class="bx bx-minus"></i> Add Extra Charge
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-end mb-3">
                            <h4 class="fw-bold">Grand Total: <span id="grand-total-display">0.00</span></h4>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-success">Update Bill</button>
                            <a href="{{ route('bills.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="product-row-template">
        <div class="product-row card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Select Product</label>
                        <select class="form-select product-select" name="products[INDEX][product_id]" required>
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-stock="{{ $product->stock }}">
                                    {{ $product->name }} (Stock: {{ $product->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="products[INDEX][description]" />
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity-input" name="products[INDEX][quantity]"
                            min="1" value="1" required />
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label">Packing (KG)</label>
                        <input type="text" class="form-control" name="products[INDEX][packing]" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total Weight </label>
                        <input type="number" step="0.01" class="form-control total-weight-input"
                            name="products[INDEX][total_weight]" min="0" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Bardana Weight </label>
                        <input type="number" step="0.01" class="form-control bardana-weight-input"
                            name="products[INDEX][bardana_weight]" min="0" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Net Weight </label>
                        <input type="number" step="0.01" class="form-control net-weight-input"
                            name="products[INDEX][net_weight]" readonly />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Rate per 40 KGS</label>
                        <input type="number" step="0.01" class="form-control rate-40-input"
                            name="products[INDEX][rate_per_40kg]" min="0" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total</label>
                        <input type="hidden" class="total-raw" name="products[INDEX][total_raw]" />
                        <input type="text" class="form-control total-display" name="products[INDEX][total]"
                            readonly />
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-product">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

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
                        <input type="number" class="form-control" name="extra_charges[INDEX][amount]" step="0.01"
                            required />
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
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <style>
            input[type=number]::-webkit-outer-spin-button,
            input[type=number]::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
        <script>
            $(document).ready(function() {
                $('#customer_type').select2({
                    placeholder: "Select Customer Type",
                    allowClear: true,
                    width: '100%'
                });

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

                const productsContainer = document.getElementById('products-container');
                const addProductBtn = document.getElementById('add-product');
                const productTemplate = document.getElementById('product-row-template');
                let productIndex = {{ ($bill->billProducts ?? collect())->count() }};

                function updateProductTotal(productRow) {
                    const totalWeight = parseFloat(productRow.querySelector('.total-weight-input').value) || 0;
                    const bardana = parseFloat(productRow.querySelector('.bardana-weight-input').value) || 0;
                    let net = totalWeight - bardana;
                    if (net < 0) net = 0;
                    productRow.querySelector('.net-weight-input').value = net.toFixed(2);

                    const ratePer40 = parseFloat(productRow.querySelector('.rate-40-input').value) || 0;
                    const total = net * (ratePer40 / 40);
                    const rawInput = productRow.querySelector('.total-raw');
                    if (rawInput) rawInput.value = total.toFixed(2);
                    const formatted = total.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    productRow.querySelector('.total-display').value = formatted;
                }

                function attachProductEventListeners(row) {
                    const productSelect = row.querySelector('.product-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    const totalWeightInput = row.querySelector('.total-weight-input');
                    const bardanaInput = row.querySelector('.bardana-weight-input');
                    const rateInput = row.querySelector('.rate-40-input');
                    const removeBtn = row.querySelector('.remove-product');

                    productSelect.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        if (option && option.dataset.stock) {
                            const stock = parseInt(option.dataset.stock);
                            quantityInput.max = stock;
                            if (parseFloat(quantityInput.value) > stock) quantityInput.value = stock;
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
                        calculateTotals();
                    });

                    [quantityInput, totalWeightInput, bardanaInput, rateInput].forEach(el => {
                        el.addEventListener('input', function() {
                            updateProductTotal(row);
                            calculateTotals();
                        });
                    });

                    removeBtn.addEventListener('click', () => {
                        row.remove();
                        calculateTotals();
                    });
                }

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
                    const addedRow = productsContainer.lastElementChild;
                    attachProductEventListeners(addedRow);
                    $(addedRow).find('.product-select').select2({
                        placeholder: 'Select a product',
                        allowClear: true,
                        width: '100%'
                    });
                    updateProductTotal(addedRow);
                    calculateTotals();
                    productIndex++;
                }

                addProductBtn.addEventListener('click', addProductRow);

                const extraChargesContainer = document.getElementById('extra-charges-container');
                const addExtraChargeBtn = document.getElementById('add-extra-charge');
                const extraChargeTemplate = document.getElementById('extra-charge-template');
                let extraChargeIndex = {{ ($bill->extraCharges ?? collect())->count() }};

                function calculateTotals() {
                    let productsTotal = 0;
                    document.querySelectorAll('.product-row').forEach(row => {
                        const raw = row.querySelector('.total-raw');
                        const val = parseFloat(raw ? raw.value : 0) || 0;
                        productsTotal += val;
                    });

                    let extraTotal = 0;
                    document.querySelectorAll('.extra-charge-row').forEach(row => {
                        const input = row.querySelector('input[name$="[amount]"]');
                        const val = parseFloat(input ? input.value : 0) || 0;
                        extraTotal += val;
                    });

                    const grandTotal = Math.max(0, productsTotal - extraTotal);
                    const display = document.getElementById('grand-total-display');
                    if (display) display.innerText = grandTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function attachExtraChargeEventListeners(row) {
                    row.querySelector('.remove-extra-charge').addEventListener('click', () => {
                        row.remove();
                        calculateTotals();
                    });
                    const amountInput = row.querySelector('input[name$="[amount]"]');
                    if (amountInput) {
                        amountInput.addEventListener('input', function() {
                            calculateTotals();
                        });
                    }
                }

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
                    const addedRow = extraChargesContainer.lastElementChild;
                    attachExtraChargeEventListeners(addedRow);
                    extraChargeIndex++;
                    calculateTotals();
                }

                addExtraChargeBtn.addEventListener('click', addExtraChargeRow);

                $('.product-select').select2({
                    placeholder: 'Select a product',
                    allowClear: true,
                    width: '100%'
                });

                document.querySelectorAll('.product-row').forEach(row => updateProductTotal(row));
                calculateTotals();
            });
        </script>
    @endpush
@endsection
