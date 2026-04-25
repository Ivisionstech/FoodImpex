@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a href="{{ route('vendors.view', $vendor->uuid) }}">{{ $vendor->company_name }}</a> /
            Create Bill
        </h4>

        <div class="card">
            <h5 class="card-header">Create New Bill</h5>
            <div class="card-body">
                <form action="{{ route('vendors.bills.store', $vendor->uuid) }}" method="POST" id="billForm"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="date">Bill Date</label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date" value="{{ old('date', now()) }}" required />
                            @error('date')
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
                            <a href="{{ route('vendors.view', $vendor->uuid) }}" class="btn btn-secondary">Cancel</a>
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
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Product Type</label>
                        <select class="form-select product-type" name="products[INDEX][product_type]" required>
                            <option value="">Select a product type</option>
                            <option value="existing">Existing Product</option>
                            <option value="new">New Product</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 existing-product">
                        <label class="form-label">Select Product</label>
                        <select class="form-select" name="products[INDEX][product_id]">
                            <option value="">Select a product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 new-product" style="display: none;">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="products[INDEX][name]" required />
                    </div>
                    <div class="col-md-4 mb-3 new-product" style="display: none;">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="products[INDEX][image]" accept="image/*" />
                    </div>
                    <div class="col-md-3 mb-3 new-product" style="display: none;">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="products[INDEX][description]" />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="products[INDEX][quantity]" min="1"
                            value="1" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Purchase Price</label>
                        <input type="number" step="0.01" class="form-control" name="products[INDEX][price]"
                            min="0" required />
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Sale Price</label>
                        <input type="number" step="0.01" class="form-control" name="products[INDEX][sale_price]"
                            min="0" required />
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Product handling
            const productsContainer = document.getElementById('products-container');
            const addProductBtn = document.getElementById('add-product');
            const productTemplate = document.getElementById('product-row-template');
            let productIndex = 0;

            function addProductRow() {
                const clone = productTemplate.content.cloneNode(true);
                const productRow = clone.querySelector('.product-row');

                // Update all name attributes with the current index
                productRow.querySelectorAll('[name]').forEach(input => {
                    input.name = input.name.replace('INDEX', productIndex);
                });

                // Add event listeners
                const productTypeSelect = productRow.querySelector('.product-type');
                const existingProductDiv = productRow.querySelector('.existing-product');
                const newProductDivs = productRow.querySelectorAll('.new-product');
                const removeBtn = productRow.querySelector('.remove-product');

                productTypeSelect.addEventListener('change', function() {
                    if (this.value === 'existing') {
                        existingProductDiv.style.display = 'block';
                        newProductDivs.forEach(div => div.style.display = 'none');
                        existingProductDiv.querySelector('select').required = true;
                        newProductDivs.forEach(div => {
                            const input = div.querySelector('input');
                            if (input) input.required = false;
                        });
                    } else {
                        existingProductDiv.style.display = 'none';
                        newProductDivs.forEach(div => div.style.display = 'block');
                        existingProductDiv.querySelector('select').required = false;
                        newProductDivs.forEach(div => {
                            const input = div.querySelector('input');
                            // if (input && input.type !== 'file') input.required = true;
                        });
                    }
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
