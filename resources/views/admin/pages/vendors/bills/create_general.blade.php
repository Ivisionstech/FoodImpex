@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumbs -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            Purchase / Create Bill
        </h4>

        <!-- ALERT MESSAGES START -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Validation Errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- ALERT MESSAGES END -->

        <div class="card">
            <h5 class="card-header">Create New Purchase Bill</h5>
            <div class="card-body">
                <form action="{{ route('vendors.bills.general_store') }}" method="POST" id="billForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <!-- Vendor Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="vendor_id">Select Vendor (Purchaser)</label>
                            <select class="form-select @error('vendor_id') is-invalid @enderror" name="vendor_id" id="vendor_id" required>
                                <option value="">Choose a vendor...</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bill Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="date">Bill Date</label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required />
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Products Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">Products Inventory</h6>
                            <div id="products-container">
                                <!-- Dynamic rows via JS -->
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
                                <!-- Extra charges via JS -->
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-3" id="add-extra-charge">
                                <i class="bx bx-plus"></i> Add Extra Charge
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end mb-3">
                            <h4 class="fw-bold">Grand Total: <span id="grand-total-display">0.00</span></h4>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-5">Create Bill</button>
                            <a href="{{ route('vendors.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <template id="product-row-template">
        <div class="product-row card mb-3 border shadow-none bg-light">
            <div class="card-body">
                <div class="row">
                    <!-- Product Type -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select product-type" name="products[INDEX][product_type]" required>
                            <option value="">Select</option>
                            <option value="existing">Existing</option>
                            <option value="new">New Product</option>
                        </select>
                    </div>

                    <!-- Product Selection (Existing) -->
                    <div class="col-md-3 mb-3 existing-product">
                        <label class="form-label">Select Product</label>
                        <select class="form-select" name="products[INDEX][product_id]">
                            <option value="">Choose product...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Product Name (New) -->
                    <div class="col-md-3 mb-3 new-product" style="display: none;">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="products[INDEX][name]" />
                    </div>

                    <!-- Image (New) -->
                    <div class="col-md-2 mb-3 new-product" style="display: none;">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="products[INDEX][image]" accept="image/*" />
                    </div>

                    <!-- Quantity -->
                    <div class="col-md-1 mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control quantity-input" name="products[INDEX][quantity]" min="1" value="1" required />
                    </div>

                    <!-- Purchase Price -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" class="form-control price-input" name="products[INDEX][price]" required />
                    </div>

                    <!-- Total Price -->
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total Price</label>
                        <input type="number" step="0.01" class="form-control total-price-input" name="products[INDEX][total_price]" readonly />
                    </div>
                </div>

                <div class="row">
                    <!-- Description -->
                    <div class="col-md-11 mb-3">
                        <label class="form-label">Description / Remarks</label>
                        <input type="text" class="form-control" name="products[INDEX][description]" placeholder="Enter product details..." />
                    </div>

                    <!-- Remove Button -->
                    <div class="col-md-1 mb-3 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-danger remove-product"><i class="bx bx-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="extra-charge-template">
        <div class="extra-charge-row card mb-2 border shadow-none bg-light">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="extra_charges[INDEX][name]" required />
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control extra-amount-input" name="extra_charges[INDEX][amount]" required />
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-extra-charge"><i class="bx bx-trash"></i></button>
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
            let productIndex = 0;

            // Function to calculate Grand Total
            function updateGrandTotal() {
                let grandTotal = 0;

                // Add all product totals
                document.querySelectorAll('.total-price-input').forEach(input => {
                    grandTotal += parseFloat(input.value) || 0;
                });

                // Add all extra charges
                document.querySelectorAll('.extra-amount-input').forEach(input => {
                    grandTotal += parseFloat(input.value) || 0;
                });

                document.getElementById('grand-total-display').innerText = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            function addProductRow() {
                const clone = productTemplate.content.cloneNode(true);
                const row = clone.querySelector('.product-row');

                // Replace INDEX placeholder
                row.querySelectorAll('[name]').forEach(i => i.name = i.name.replace('INDEX', productIndex));

                const typeSelect = row.querySelector('.product-type');
                const existingDiv = row.querySelector('.existing-product');
                const newDivs = row.querySelectorAll('.new-product');

                const qtyInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.price-input');
                const totalInput = row.querySelector('.total-price-input');

                // Calculation Logic
                const calculateRowTotal = () => {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    totalInput.value = (qty * price).toFixed(2);
                    updateGrandTotal();
                };

                qtyInput.addEventListener('input', calculateRowTotal);
                priceInput.addEventListener('input', calculateRowTotal);

                // Type Toggle Logic
                typeSelect.addEventListener('change', function() {
                    if (this.value === 'existing') {
                        existingDiv.style.display = 'block';
                        newDivs.forEach(d => d.style.display = 'none');
                    } else if (this.value === 'new') {
                        existingDiv.style.display = 'none';
                        newDivs.forEach(d => d.style.display = 'block');
                    } else {
                        existingDiv.style.display = 'none';
                        newDivs.forEach(d => d.style.display = 'none');
                    }
                });

                row.querySelector('.remove-product').addEventListener('click', () => {
                    row.remove();
                    updateGrandTotal();
                });

                productsContainer.appendChild(row);
                productIndex++;
            }

            // Extra Charges Logic
            const extraChargesContainer = document.getElementById('extra-charges-container');
            const addExtraChargeBtn = document.getElementById('add-extra-charge');
            const extraChargeTemplate = document.getElementById('extra-charge-template');
            let extraChargeIndex = 0;

            function addExtraChargeRow() {
                const clone = extraChargeTemplate.content.cloneNode(true);
                const row = clone.querySelector('.extra-charge-row');

                row.querySelectorAll('[name]').forEach(i => i.name = i.name.replace('INDEX', extraChargeIndex));

                row.querySelector('.extra-amount-input').addEventListener('input', updateGrandTotal);

                row.querySelector('.remove-extra-charge').addEventListener('click', () => {
                    row.remove();
                    updateGrandTotal();
                });

                extraChargesContainer.appendChild(row);
                extraChargeIndex++;
            }

            // Initial row and listeners
            addProductRow();
            addProductBtn.addEventListener('click', addProductRow);
            addExtraChargeBtn.addEventListener('click', addExtraChargeRow);
        });
    </script>
@endpush
