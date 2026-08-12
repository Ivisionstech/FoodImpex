@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('customers.list') }}">Customers</a> /
            Edit New Sales Bill
        </h4>

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
                    <i class="bx bx-edit me-2 text-warning"></i>
                    Edit Sales Invoice
                </h5>
                <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
            </div>

            <div class="card-body">
                <form action="{{ route('bills.update', $bill->uuid) }}" method="POST" id="billForm">
                    @csrf
                    <input type="hidden" name="type" value="new bill">

                    <!-- Bill Information Row -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="bill_date">
                                Invoice Date <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control @error('bill_date') is-invalid @enderror" 
                                   id="bill_date" name="bill_date" 
                                   value="{{ old('bill_date', \Carbon\Carbon::parse($bill->bill_date)->format('Y-m-d\TH:i')) }}" required />
                            @error('bill_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="payment_terms">Payment Terms</label>
                            <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" 
                                   id="payment_terms" name="payment_terms"
                                   value="{{ old('payment_terms', $bill->payment_terms) }}" />
                            <div class="form-text">e.g., 100% IN 30 DAYS, Net 60, etc.</div>
                            @error('payment_terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    <option value="{{ $customer->id }}" 
                                        {{ $bill->customer_id == $customer->id ? 'selected' : '' }}>
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
                                <span class="badge bg-label-info rounded-pill" id="items-count">{{ $bill->billProducts->count() }} Items</span>
                            </div>
                            
                            <div id="products-container" class="mb-3">
                                @foreach ($bill->billProducts as $index => $bp)
                                    <div class="product-row card mb-3 border" data-index="{{ $index }}">
                                        <div class="card-body p-3">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Product <span class="text-danger">*</span></label>
                                                            <select class="form-select form-select-sm product-select" 
                                                                    name="products[{{ $index }}][product_id]" required>
                                                                <option value="">Select product</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}" 
                                                                        data-stock="{{ $product->stock }}" 
                                                                        data-price="{{ $product->sale_price }}"
                                                                        {{ $product->id == $bp->product_id ? 'selected' : '' }}>
                                                                        {{ $product->name }} (Stock: {{ $product->stock }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label small fw-semibold">Description</label>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                   name="products[{{ $index }}][description]"
                                                                   value="{{ old('products.' . $index . '.description', $bp->description ?? '') }}" />
                                                        </div>

                                                        <div class="col-md-1">
                                                            <label class="form-label small fw-semibold">Qty <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control form-control-sm quantity-input" 
                                                                   name="products[{{ $index }}][quantity]" 
                                                                   value="{{ $bp->quantity }}" min="1" required />
                                                        </div>

                                                        <div class="col-md-1">
                                                            <label class="form-label small fw-semibold">Pack (KG)</label>
                                                            <input type="text" class="form-control form-control-sm packing-input" 
                                                                   name="products[{{ $index }}][packing]"
                                                                   value="{{ old('products.' . $index . '.packing', $bp->packing ?? '') }}" />
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small fw-semibold">Total Weight (Gross)</label>
                                                            <input type="number" step="0.01" 
                                                                   class="form-control form-control-sm total-weight-input" 
                                                                   name="products[{{ $index }}][total_weight]"
                                                                   value="{{ old('products.' . $index . '.total_weight', $bp->total_weight ?? '') }}" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        <div class="col-md-2">
                                                            <label class="form-label small fw-semibold">Bardana Wt</label>
                                                            <input type="number" step="0.01" 
                                                                   class="form-control form-control-sm bardana-weight-input" 
                                                                   name="products[{{ $index }}][bardana_weight]"
                                                                   value="{{ old('products.' . $index . '.bardana_weight', $bp->bardana_weight ?? '') }}" />
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small fw-semibold text-success">Net Weight</label>
                                                            <input type="number" step="0.01" 
                                                                   class="form-control form-control-sm net-weight-input bg-light" 
                                                                   name="products[{{ $index }}][net_weight]"
                                                                   value="{{ old('products.' . $index . '.net_weight', $bp->net_weight ?? '') }}"
                                                                   readonly />
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small fw-semibold">Rate per 40 kgs</label>
                                                            <input type="number" step="0.01" 
                                                                   class="form-control form-control-sm rate-40-input" 
                                                                   name="products[{{ $index }}][rate_per_40kg]"
                                                                   value="{{ old('products.' . $index . '.rate_per_40kg', $bp->rate_per_40kg ?? '') }}" />
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small fw-semibold">Total Amount</label>
                                                            <input type="hidden" class="total-raw" 
                                                                   name="products[{{ $index }}][total]" 
                                                                   value="{{ old('products.' . $index . '.total', $bp->total ?? 0) }}" />
                                                            <input type="text" class="form-control form-control-sm total-display bg-light" 
                                                                   readonly value="{{ number_format((float) ($bp->total ?? 0), 2) }}" />
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
                                @endforeach
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
                                <span class="badge bg-label-warning rounded-pill" id="charges-count">{{ $bill->extraCharges->count() }} Charges</span>
                            </div>
                            
                            <div id="extra-charges-container" class="mb-3">
                                @foreach ($bill->extraCharges as $index => $charge)
                                    <div class="extra-charge-row card mb-2 border">
                                        <div class="card-body py-2 px-3">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Charge Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="extra_charges[{{ $index }}][name]" 
                                                           value="{{ $charge->name }}" required />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-semibold">Amount (PKR) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" 
                                                           class="form-control form-control-sm extra-amount-input" 
                                                           name="extra_charges[{{ $index }}][amount]" 
                                                           value="{{ $charge->amount }}" required />
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-danger w-100 remove-extra-charge">
                                                        <i class="bx bx-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
                            <button type="submit" class="btn btn-warning px-4" id="submitBtn">
                                <i class="bx bx-save me-1"></i> Update Bill
                            </button>
                            <a href="{{ route('bills.list') }}" class="btn btn-outline-secondary px-4">
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

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

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
        });

        document.addEventListener('DOMContentLoaded', function() {
            let productIndex = {{ ($bill->billProducts ?? collect())->count() }};
            let extraChargeIndex = {{ ($bill->extraCharges ?? collect())->count() }};

            // Function to update net weight
            function updateNetWeight(productRow) {
                const totalWeight = parseFloat(productRow.querySelector('.total-weight-input').value) || 0;
                const bardana = parseFloat(productRow.querySelector('.bardana-weight-input').value) || 0;
                const netWeight = Math.max(0, totalWeight - bardana);
                productRow.querySelector('.net-weight-input').value = netWeight.toFixed(2);
                return netWeight;
            }

            // Function to update line total
            function updateLineTotal(productRow) {
                const netWeight = parseFloat(productRow.querySelector('.net-weight-input').value) || 0;
                const rate = parseFloat(productRow.querySelector('.rate-40-input').value) || 0;
                const lineTotal = netWeight > 0 && rate > 0 ? (netWeight * rate) / 40 : 0;
                productRow.querySelector('.total-raw').value = lineTotal.toFixed(2);
                productRow.querySelector('.total-display').value = lineTotal.toFixed(2);
                return lineTotal;
            }

            // Function to calculate all totals
            function calculateTotals() {
                let subtotal = 0;
                let extraTotal = 0;
                
                document.querySelectorAll('.product-row').forEach(row => {
                    const lineTotal = parseFloat(row.querySelector('.total-raw').value) || 0;
                    subtotal += lineTotal;
                });
                
                document.querySelectorAll('.extra-charge-row').forEach(row => {
                    const amount = parseFloat(row.querySelector('.extra-amount-input').value) || 0;
                    extraTotal += amount;
                });
                
                const grandTotal = Math.max(0, subtotal - extraTotal);
                
                document.getElementById('subtotal-display').textContent = 'PKR ' + subtotal.toFixed(2);
                document.getElementById('extra-charges-total-display').textContent = 'PKR ' + extraTotal.toFixed(2);
                document.getElementById('grand-total-display').textContent = 'PKR ' + grandTotal.toFixed(2);
                
                const itemsCount = document.querySelectorAll('.product-row').length;
                document.getElementById('items-count').textContent = itemsCount + ' Item' + (itemsCount !== 1 ? 's' : '');
                
                const chargesCount = document.querySelectorAll('.extra-charge-row').length;
                document.getElementById('charges-count').textContent = chargesCount + ' Charge' + (chargesCount !== 1 ? 's' : '');
            }

            // Attach event listeners to existing product rows
            function attachProductEventListeners(row) {
                const productSelect = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const totalWeightInput = row.querySelector('.total-weight-input');
                const bardanaInput = row.querySelector('.bardana-weight-input');
                const rateInput = row.querySelector('.rate-40-input');
                const removeBtn = row.querySelector('.remove-product');

                if (productSelect) {
                    // Initialize Select2 for product dropdown
                    $(productSelect).select2({
                        placeholder: 'Select product',
                        allowClear: true,
                        width: '100%'
                    });

                    productSelect.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        if (option && option.dataset.stock) {
                            const stock = parseInt(option.dataset.stock);
                            if (quantityInput) quantityInput.max = stock;
                        }
                        calculateTotals();
                    });
                }

                if (quantityInput) {
                    quantityInput.addEventListener('input', function() {
                        const selectedOption = productSelect?.options[productSelect.selectedIndex];
                        if (selectedOption && selectedOption.dataset.stock) {
                            const stock = parseInt(selectedOption.dataset.stock);
                            if (this.value > stock) {
                                this.value = stock;
                                alert('Quantity cannot exceed available stock');
                            }
                        }
                        calculateTotals();
                    });
                }

                const inputs = [quantityInput, totalWeightInput, bardanaInput, rateInput];
                inputs.forEach(input => {
                    if (input) {
                        input.addEventListener('input', function() {
                            if (totalWeightInput && bardanaInput) {
                                updateNetWeight(row);
                            }
                            if (rateInput) {
                                updateLineTotal(row);
                            }
                            calculateTotals();
                        });
                    }
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        row.remove();
                        calculateTotals();
                    });
                }
            }

            // Attach to existing product rows
            document.querySelectorAll('.product-row').forEach(row => {
                attachProductEventListeners(row);
                // Update calculations for existing rows
                const totalWeightInput = row.querySelector('.total-weight-input');
                const bardanaInput = row.querySelector('.bardana-weight-input');
                const rateInput = row.querySelector('.rate-40-input');
                if (totalWeightInput && bardanaInput) {
                    updateNetWeight(row);
                }
                if (rateInput) {
                    updateLineTotal(row);
                }
            });

            // Add product row
            function addProductRow() {
                const template = document.getElementById('product-row-template');
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.product-row');

                row.querySelectorAll('[name]').forEach(i => {
                    i.name = i.name.replace('INDEX', productIndex);
                });

                document.getElementById('products-container').appendChild(row);
                const addedRow = document.getElementById('products-container').lastElementChild;
                attachProductEventListeners(addedRow);
                
                // Update net weight and total for new row
                const totalWeightInput = addedRow.querySelector('.total-weight-input');
                const bardanaInput = addedRow.querySelector('.bardana-weight-input');
                const rateInput = addedRow.querySelector('.rate-40-input');
                if (totalWeightInput && bardanaInput) {
                    updateNetWeight(addedRow);
                }
                if (rateInput) {
                    updateLineTotal(addedRow);
                }
                
                calculateTotals();
                productIndex++;
            }

            // Add extra charge
            function addExtraCharge() {
                const template = document.getElementById('extra-charge-template');
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.extra-charge-row');

                row.querySelectorAll('[name]').forEach(i => {
                    i.name = i.name.replace('INDEX', extraChargeIndex);
                });

                const amountInput = row.querySelector('.extra-amount-input');
                if (amountInput) {
                    amountInput.addEventListener('input', function() {
                        calculateTotals();
                    });
                }

                const removeBtn = row.querySelector('.remove-extra-charge');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        row.remove();
                        calculateTotals();
                    });
                }

                document.getElementById('extra-charges-container').appendChild(row);
                extraChargeIndex++;
                calculateTotals();
            }

            // Attach to existing extra charge rows
            document.querySelectorAll('.extra-charge-row').forEach(row => {
                const amountInput = row.querySelector('.extra-amount-input');
                if (amountInput) {
                    amountInput.addEventListener('input', function() {
                        calculateTotals();
                    });
                }

                const removeBtn = row.querySelector('.remove-extra-charge');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        row.remove();
                        calculateTotals();
                    });
                }
            });

            // Event listeners for add buttons
            document.getElementById('add-product').addEventListener('click', addProductRow);
            document.getElementById('add-extra-charge').addEventListener('click', addExtraCharge);

            // Initial calculation
            calculateTotals();

            // Form validation before submit
            document.getElementById('billForm').addEventListener('submit', function(e) {
                // Check if at least one product is added
                if (document.querySelectorAll('.product-row').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one product to the invoice.');
                    return false;
                }

                // Check if customer is selected
                if (!document.getElementById('customer_id').value) {
                    e.preventDefault();
                    alert('Please select a customer.');
                    document.getElementById('customer_id').focus();
                    return false;
                }

                // Check if any product has a total amount > 0
                let hasValidProduct = false;
                document.querySelectorAll('.product-row').forEach(row => {
                    const total = parseFloat(row.querySelector('.total-raw').value) || 0;
                    if (total > 0) {
                        hasValidProduct = true;
                    }
                });

                if (!hasValidProduct) {
                    e.preventDefault();
                    alert('Please ensure at least one product has a valid amount.');
                    return false;
                }

                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...';
            });
        });
    </script>
@endpush