@extends('admin.layout.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumbs -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            Purchase / Edit Bill
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
            <h5 class="card-header">Edit Purchase Bill</h5>
            <div class="card-body">
                <form action="{{ route('vendors.bills.general_update_2', $bill->uuid) }}" method="POST" id="billForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <!-- Vendor Selection (Read-only for edit) -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="vendor_id">Vendor (Purchaser)</label>
                            <input type="text" class="form-control" value="{{ $vendor->company_name }}" disabled />
                            <input type="hidden" name="vendor_id" value="{{ $vendor->id }}" />
                        </div>

                        <!-- Bill Date -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="date">Bill Date</label>
                            <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                id="date" name="date"
                                value="{{ old('date', $bill->date ? \Carbon\Carbon::parse($bill->date)->format('Y-m-d\TH:i') : '') }}"
                                required />
                        </div>

                        <!-- Payment Terms -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="payment_terms">Payment Terms</label>
                            <input type="text" class="form-control @error('payment_terms') is-invalid @enderror"
                                id="payment_terms" name="payment_terms"
                                value="{{ old('payment_terms', $bill->payment_terms) }}" />
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Products Section -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">Products Inventory</h6>
                            <div id="products-container">
                                <!-- Existing products will be loaded here -->
                                @foreach ($bill->billProducts as $billProduct)
                                    <div class="product-row card mb-3 border shadow-none bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Select Product</label>
                                                    <select class="form-select product-select"
                                                        name="products[{{ $loop->index }}][product_id]" required>
                                                        <option value="">Choose product...</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}"
                                                                {{ $billProduct->product_id == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden"
                                                        name="products[{{ $loop->index }}][bill_product_id]"
                                                        value="{{ $billProduct->id }}" />
                                                </div>

                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label">Description / Remarks</label>
                                                    <input type="text" class="form-control"
                                                        name="products[{{ $loop->index }}][description]"
                                                        value="{{ $billProduct->description }}"
                                                        placeholder="Enter product details..." />
                                                </div>

                                                <div class="col-md-1 mb-3 d-flex align-items-end justify-content-end">
                                                    <button type="button" class="btn btn-danger remove-product"><i
                                                            class="bx bx-trash"></i></button>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Qty</label>
                                                    <input type="number" class="form-control quantity-input"
                                                        name="products[{{ $loop->index }}][quantity]"
                                                        value="{{ $billProduct->quantity }}" required />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Packing (KG)</label>
                                                    <input type="number" step="0.01" class="form-control packing-input"
                                                        name="products[{{ $loop->index }}][packing]"
                                                        value="{{ $billProduct->packing }}" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Total Weight</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control total-weight-input"
                                                        name="products[{{ $loop->index }}][total_weight]"
                                                        value="{{ $billProduct->total_weight }}" />
                                                </div>

                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Bardana Wt</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control bardana-weight-input"
                                                        name="products[{{ $loop->index }}][bardana_weight]"
                                                        value="{{ $billProduct->bardana_weight }}" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Net Weight</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control net-weight-input"
                                                        name="products[{{ $loop->index }}][net_weight]"
                                                        value="{{ $billProduct->net_weight }}" />
                                                </div>

                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Rate per 40 kgs</label>
                                                    <input type="number" step="0.01" class="form-control price-input"
                                                        name="products[{{ $loop->index }}][price]"
                                                        value="{{ $billProduct->price }}" required />
                                                </div>

                                                <div class="col-md-1 mb-3">
                                                    <label class="form-label">Total</label>
                                                    <input type="number" class="form-control total-price-input"
                                                        style="width: 150%"
                                                        name="products[{{ $loop->index }}][total_price]"
                                                        value="{{ $billProduct->total_price }}" readonly />
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

                    <!-- Extra Charges & Additional Charges Section -->
                    <div class="row mb-3">
                        <!-- Extra Charges (Subtract) -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">Extra Charges (Subtract)</h6>
                            <div id="extra-charges-container">
                                @foreach ($bill->extraCharges as $charge)
                                    <div class="extra-charge-row card mb-2 border shadow-none bg-light">
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control"
                                                        name="extra_charges[{{ $loop->index }}][name]"
                                                        value="{{ $charge->name }}" required />
                                                    <input type="hidden" name="extra_charges[{{ $loop->index }}][id]"
                                                        value="{{ $charge->id }}" />
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control extra-amount-input"
                                                        name="extra_charges[{{ $loop->index }}][amount]"
                                                        value="{{ $charge->amount }}" required />
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-extra-charge"><i
                                                            class="bx bx-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-3" id="add-extra-charge">
                                <i class="bx bx-minus"></i> Add Extra Charge
                            </button>
                        </div>

                        <!-- Additional Charges (Add) -->
                        <div class="col-md-6">
                            <h6 class="mb-3 text-primary">Additional Charges (Add)</h6>
                            <div id="additional-charges-container">
                                @foreach ($bill->additionalCharges as $charge)
                                    <div class="additional-charge-row card mb-2 border shadow-none bg-light">
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control"
                                                        name="additional_charges[{{ $loop->index }}][name]"
                                                        value="{{ $charge->name }}" required />
                                                    <input type="hidden"
                                                        name="additional_charges[{{ $loop->index }}][id]"
                                                        value="{{ $charge->id }}" />
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control additional-amount-input"
                                                        name="additional_charges[{{ $loop->index }}][amount]"
                                                        value="{{ $charge->amount }}" required />
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <button type="button"
                                                        class="btn btn-danger remove-additional-charge"><i
                                                            class="bx bx-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary mt-3" id="add-additional-charge">
                                <i class="bx bx-plus"></i> Add Additional Charge
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12 text-end mb-3">
                            <h4 class="fw-bold">Grand Total: <span id="grand-total-display">0.00</span></h4>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-5">Update Bill</button>
                            <a href="{{ route('vendors.view', $vendor->uuid) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Product Template (for dynamically added rows) -->
    <template id="product-row-template">
        <div class="product-row card mb-3 border shadow-none bg-light">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select Product</label>
                        <select class="form-select product-select" name="products[INDEX][product_id]" required>
                            <option value="">Choose product...</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label class="form-label">Description / Remarks</label>
                        <input type="text" class="form-control" name="products[INDEX][description]"
                            placeholder="Enter product details..." />
                    </div>

                    <div class="col-md-1 mb-3 d-flex align-items-end justify-content-end">
                        <button type="button" class="btn btn-danger remove-product"><i class="bx bx-trash"></i></button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-1 mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control quantity-input" name="products[INDEX][quantity]"
                            required />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Packing (KG)</label>
                        <input type="number" step="0.01" class="form-control packing-input"
                            name="products[INDEX][packing]" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Total Weight</label>
                        <input type="number" step="0.01" class="form-control total-weight-input"
                            name="products[INDEX][total_weight]" />
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label">Bardana Wt</label>
                        <input type="number" step="0.01" class="form-control bardana-weight-input"
                            name="products[INDEX][bardana_weight]" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Net Weight</label>
                        <input type="number" step="0.01" class="form-control net-weight-input"
                            name="products[INDEX][net_weight]" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">Rate per 40 kgs</label>
                        <input type="number" step="0.01" class="form-control price-input"
                            name="products[INDEX][price]" required />
                    </div>

                    <div class="col-md-1 mb-3">
                        <label class="form-label">Total</label>
                        <input type="number" class="form-control total-price-input" style="width: 150%"
                            name="products[INDEX][total_price]" readonly />
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
                        <input type="number" step="0.01" class="form-control extra-amount-input"
                            name="extra_charges[INDEX][amount]" required />
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-extra-charge"><i
                                class="bx bx-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="additional-charge-template">
        <div class="additional-charge-row card mb-2 border shadow-none bg-light">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="additional_charges[INDEX][name]" required />
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control additional-amount-input"
                            name="additional_charges[INDEX][amount]" required />
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-additional-charge"><i
                                class="bx bx-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const productsContainer = $('#products-container');
            const addProductBtn = $('#add-product');
            const productTemplate = $('#product-row-template');
            let productIndex = {{ count($bill->billProducts) }};

            // Function to calculate Grand Total
            function updateGrandTotal() {
                let grandTotal = 0;

                // Add all product totals
                $('.total-price-input').each(function() {
                    grandTotal += parseFloat($(this).val()) || 0;
                });

                // Subtract all extra charges
                $('.extra-amount-input').each(function() {
                    grandTotal -= parseFloat($(this).val()) || 0;
                });

                // Add all additional charges
                $('.additional-amount-input').each(function() {
                    grandTotal += parseFloat($(this).val()) || 0;
                });

                $('#grand-total-display').text(grandTotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            // Initialize existing product rows
            $('.product-row').each(function() {
                const row = $(this);
                const netWeightInput = row.find('.net-weight-input');
                const priceInput = row.find('.price-input');
                const totalInput = row.find('.total-price-input');

                const calculateRowTotal = () => {
                    const netWeight = parseFloat(netWeightInput.val()) || 0;
                    const rate = parseFloat(priceInput.val()) || 0;
                    const totalAmount = (netWeight * rate) / 40;
                    totalInput.val(totalAmount.toFixed(2));
                    updateGrandTotal();
                };

                netWeightInput.on('input', calculateRowTotal);
                priceInput.on('input', calculateRowTotal);

                row.find('.remove-product').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });

                row.find('.product-select').select2();
            });

            function addProductRow() {
                const clone = productTemplate[0].content.cloneNode(true);
                const row = $(clone).find('.product-row');

                row.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('INDEX', productIndex));
                });

                const netWeightInput = row.find('.net-weight-input');
                const priceInput = row.find('.price-input');
                const totalInput = row.find('.total-price-input');

                const calculateRowTotal = () => {
                    const netWeight = parseFloat(netWeightInput.val()) || 0;
                    const rate = parseFloat(priceInput.val()) || 0;
                    const totalAmount = (netWeight * rate) / 40;
                    totalInput.val(totalAmount.toFixed(2));
                    updateGrandTotal();
                };

                netWeightInput.on('input', calculateRowTotal);
                priceInput.on('input', calculateRowTotal);

                row.find('.remove-product').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });

                productsContainer.append(row);
                row.find('.product-select').select2();
                productIndex++;
            }

            // Extra Charges Logic
            const extraChargesContainer = $('#extra-charges-container');
            const addExtraChargeBtn = $('#add-extra-charge');
            const extraChargeTemplate = $('#extra-charge-template');
            let extraChargeIndex = {{ count($bill->extraCharges) }};

            // Initialize existing extra charges
            $('.extra-charge-row').each(function() {
                const row = $(this);
                row.find('.extra-amount-input').on('input', updateGrandTotal);
                row.find('.remove-extra-charge').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });
            });

            function addExtraChargeRow() {
                const clone = extraChargeTemplate[0].content.cloneNode(true);
                const row = $(clone).find('.extra-charge-row');

                row.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('INDEX', extraChargeIndex));
                });

                row.find('.extra-amount-input').on('input', updateGrandTotal);

                row.find('.remove-extra-charge').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });

                extraChargesContainer.append(row);
                extraChargeIndex++;
            }

            // Additional Charges Logic
            const additionalChargesContainer = $('#additional-charges-container');
            const addAdditionalChargeBtn = $('#add-additional-charge');
            const additionalChargeTemplate = $('#additional-charge-template');
            let additionalChargeIndex = {{ count($bill->additionalCharges) }};

            // Initialize existing additional charges
            $('.additional-charge-row').each(function() {
                const row = $(this);
                row.find('.additional-amount-input').on('input', updateGrandTotal);
                row.find('.remove-additional-charge').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });
            });

            function addAdditionalChargeRow() {
                const clone = additionalChargeTemplate[0].content.cloneNode(true);
                const row = $(clone).find('.additional-charge-row');

                row.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('INDEX', additionalChargeIndex));
                });

                row.find('.additional-amount-input').on('input', updateGrandTotal);

                row.find('.remove-additional-charge').on('click', function() {
                    row.remove();
                    updateGrandTotal();
                });

                additionalChargesContainer.append(row);
                additionalChargeIndex++;
            }

            // Event listeners
            addProductBtn.on('click', addProductRow);
            addExtraChargeBtn.on('click', addExtraChargeRow);
            addAdditionalChargeBtn.on('click', addAdditionalChargeRow);

            // Calculate initial grand total on page load
            updateGrandTotal();
        });
    </script>
@endpush
