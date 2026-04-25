@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('vendors.view', $vendor->uuid) }}">{{ $vendor->company_name }}</a> /
            Edit Purchase Bill
        </h4>

        <div class="card">
            <h5 class="card-header">Edit Bill Details: #{{ $bill->id }}</h5>
            <div class="card-body">
                <form action="{{ route('vendors.bills.update', $bill->uuid) }}" method="POST" enctype="multipart/form-data" id="editBillForm">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bill Date</label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                name="date" value="{{ old('date', \Carbon\Carbon::parse($bill->date)->format('Y-m-d\TH:i')) }}" required />
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Products Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">Products Inventory</h6>
                            <div id="products-container">
                                @foreach ($bill->billProducts as $index => $bp)
                                    <div class="product-row card mb-3 border shadow-none bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <input type="hidden" name="products[{{ $index }}][bill_product_id]" value="{{ $bp->id }}">

                                                <!-- Product Type Selection -->
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select product-type" name="products[{{ $index }}][product_type]" required>
                                                        <option value="existing" selected>Existing</option>
                                                        <option value="new">New Product</option>
                                                    </select>
                                                </div>

                                                <!-- Select Product (Existing) -->
                                                <div class="col-md-3 mb-3 existing-product">
                                                    <label class="form-label">Select Product</label>
                                                    <select class="form-select" name="products[{{ $index }}][product_id]">
                                                        <option value="">Choose product...</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}"
                                                                {{ $bp->product_id == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Fields for New Product (Hidden by default) -->
                                                <div class="col-md-3 mb-3 new-product" style="display: none;">
                                                    <label class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" name="products[{{ $index }}][name]" />
                                                </div>

                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Qty</label>
                                                    <input type="number" class="form-control qty-input" name="products[{{ $index }}][quantity]"
                                                        value="{{ old('products.' . $index . '.quantity', $bp->quantity) }}" min="1" required />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Price</label>
                                                    <input type="number" step="0.01" class="form-control price-input" name="products[{{ $index }}][price]"
                                                        value="{{ old('products.' . $index . '.price', $bp->price) }}" required />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Total</label>
                                                    <input type="number" step="0.01" class="form-control total-input" name="products[{{ $index }}][total_price]"
                                                        value="{{ $bp->quantity * $bp->price }}" readonly />
                                                </div>

                                                <div class="col-md-2 mb-3 d-flex align-items-end justify-content-end">
                                                    <button type="button" class="btn btn-danger remove-product">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Description Field -->
                                                <div class="col-md-12">
                                                    <label class="form-label">Description / Remarks</label>
                                                    <input type="text" class="form-control" name="products[{{ $index }}][description]"
                                                        value="{{ old('products.' . $index . '.description', $bp->description) }}" placeholder="Enter details...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-outline-primary mt-3" id="add-product">
                                <i class="bx bx-plus"></i> Add Product
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Extra Charges Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">Extra Charges</h6>
                            <div id="extra-charges-container">
                                @foreach ($bill->extraCharges as $i => $charge)
                                    <div class="extra-charge-row card mb-2 border shadow-none bg-light">
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <input type="hidden" name="extra_charges[{{ $i }}][id]" value="{{ $charge->id }}">
                                                <div class="col-md-5">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control"
                                                        name="extra_charges[{{ $i }}][name]"
                                                        value="{{ old('extra_charges.' . $i . '.name', $charge->name) }}" required />
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" step="0.01" class="form-control charge-amount-input"
                                                        name="extra_charges[{{ $i }}][amount]"
                                                        value="{{ old('extra_charges.' . $i . '.amount', $charge->amount) }}" required />
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-extra-charge">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-3" id="add-extra-charge">
                                <i class="bx bx-plus"></i> Add Extra Charge
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end mb-3">
                            <h4 class="fw-bold">Grand Total: PKR <span id="grand-total-display">{{ number_format($bill->total_amount, 2) }}</span></h4>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success px-5">Update Bill</button>
                            <a href="{{ route('vendors.view', $vendor->uuid) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PRODUCT TEMPLATE -->
    <template id="product-row-template">
        <div class="product-row card mb-3 border shadow-none bg-light">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="products[INDEX][bill_product_id]" value="">

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select product-type" name="products[INDEX][product_type]" required>
                            <option value="existing" selected>Existing</option>
                            <option value="new">New Product</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 existing-product">
                        <label class="form-label">Select Product</label>
                        <select class="form-select" name="products[INDEX][product_id]">
                            <option value="">Choose product...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 new-product" style="display: none;">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="products[INDEX][name]" />
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control qty-input" name="products[INDEX][quantity]" value="1" min="1" required />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control price-input" name="products[INDEX][price]" required />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total</label>
                        <input type="number" step="0.01" class="form-control total-input" name="products[INDEX][total_price]" readonly />
                    </div>

                    <div class="col-md-2 mb-3 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-danger remove-product">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Description / Remarks</label>
                        <input type="text" class="form-control" name="products[INDEX][description]" placeholder="Enter details...">
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- EXTRA CHARGE TEMPLATE -->
    <template id="extra-charge-template">
        <div class="extra-charge-row card mb-2 border shadow-none bg-light">
            <div class="card-body py-2">
                <div class="row">
                    <input type="hidden" name="extra_charges[INDEX][id]" value="">
                    <div class="col-md-5">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="extra_charges[INDEX][name]" required />
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control charge-amount-input" name="extra_charges[INDEX][amount]" value="0.00" required />
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-extra-charge">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productsContainer = document.getElementById('products-container');
        const addProductBtn = document.getElementById('add-product');
        const productTemplate = document.getElementById('product-row-template');
        let productIndex = {{ count($bill->billProducts) }};

        // Function to update the final Grand Total
        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('.total-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.querySelectorAll('.charge-amount-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('grand-total-display').innerText = total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Logic for each product row (Type toggle and calculation)
        function initProductRow(row) {
            const typeSelect = row.querySelector('.product-type');
            const existingDiv = row.querySelector('.existing-product');
            const newDiv = row.querySelector('.new-product');
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const totalInput = row.querySelector('.total-input');
            const removeBtn = row.querySelector('.remove-product');

            const calculate = () => {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (qty * price).toFixed(2);
                updateGrandTotal();
            };

            typeSelect.addEventListener('change', function() {
                existingDiv.style.display = this.value === 'existing' ? 'block' : 'none';
                newDiv.style.display = this.value === 'new' ? 'block' : 'none';
            });

            qtyInput.addEventListener('input', calculate);
            priceInput.addEventListener('input', calculate);
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateGrandTotal();
            });

            // Initial calculation for existing rows
            calculate();
        }

        // Add new Product row
        addProductBtn.addEventListener('click', function() {
            const clone = productTemplate.content.cloneNode(true);
            const row = clone.querySelector('.product-row');
            row.querySelectorAll('[name*="INDEX"]').forEach(input => {
                input.name = input.name.replace('INDEX', productIndex);
            });
            initProductRow(row);
            productsContainer.appendChild(row);
            productIndex++;
        });

        // Initialize already existing rows on page load
        document.querySelectorAll('.product-row').forEach(row => initProductRow(row));

        // EXTRA CHARGES LOGIC
        const extraChargesContainer = document.getElementById('extra-charges-container');
        const addExtraChargeBtn = document.getElementById('add-extra-charge');
        const extraChargeTemplate = document.getElementById('extra-charge-template');
        let extraChargeIndex = {{ count($bill->extraCharges) }};

        function initExtraChargeRow(row) {
            const amountInput = row.querySelector('.charge-amount-input');
            const removeBtn = row.querySelector('.remove-extra-charge');

            amountInput.addEventListener('input', updateGrandTotal);
            removeBtn.addEventListener('click', () => {
                row.remove();
                updateGrandTotal();
            });
        }

        addExtraChargeBtn.addEventListener('click', function() {
            const clone = extraChargeTemplate.content.cloneNode(true);
            const row = clone.querySelector('.extra-charge-row');
            row.querySelectorAll('[name*="INDEX"]').forEach(input => {
                input.name = input.name.replace('INDEX', extraChargeIndex);
            });
            initExtraChargeRow(row);
            extraChargesContainer.appendChild(row);
            extraChargeIndex++;
        });

        // Initialize existing extra charges
        document.querySelectorAll('.extra-charge-row').forEach(row => initExtraChargeRow(row));

        // Final Grand Total check on load
        updateGrandTotal();
    });
</script>
@endpush
