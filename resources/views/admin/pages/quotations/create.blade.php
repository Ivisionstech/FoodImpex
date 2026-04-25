@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="">Quotations</a> /
            Add Quotation
        </h4>
        <div class="card">
            <h5 class="card-header">Add New Quotation</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('quotations.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="consignee_name">Consignee Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="consignee_name" name="consignee_name" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="consignee_address">Consignee Address <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="consignee_address" name="consignee_address"
                                required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="invoice_no">Invoice No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="invoice_date">Invoice Date <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="invoice_date" name="invoice_date"
                                required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fi_no">F.I. No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fi_no" name="fi_no" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="destination">Destination <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="destination" name="destination" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payment_term">Payment Term <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payment_term" name="payment_term" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="freight_term">Freight Term <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="freight_term" name="freight_term" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="hs_code">HS Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hs_code" name="hs_code" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="brand">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brand" name="brand" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rate_per_ton">Rate per Ton <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="rate_per_ton"
                                name="rate_per_ton" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="currency">Currency <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control" required>
                                <option value="USD">USD</option>
                                <option value="PKR">PKR</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="INR">INR</option>
                                <option value="AED">AED</option>
                                <option value="SAR">SAR</option>
                                <option value="QAR">QAR</option>
                                <option value="KWD">KWD</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="shipment_mode">Shipment Mode <span class="text-danger">*</span></label>
                            <select name="shipment_mode" id="shipment_mode" class="form-control" required>
                                <option value="">Select Mode</option>
                                <option value="By Sea">By Sea</option>
                                <option value="By Road">By Road</option>
                            </select>
                        </div>

       
                        <div class="col-md-4">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" required />
                        </div>
                    </div>

                    <!-- Quotation Items Table -->
                    <div class="mb-3">
                        <label class="form-label">Quotation Items</label>
                        <table class="table table-bordered" id="items-table">
                            <thead>
                                <tr>
                                    <th>No of Bags</th>
                                    <th>Pack Details</th>
                                    <th>Net Weight (KG)</th>
                                    <th>Gross Weight (KG)</th>
                                    <th>Price</th>
                                    <th><button type="button" class="btn btn-sm btn-success" id="add-item">+</button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" name="items[0][no_of_bags]" class="form-control"
                                            required /></td>
                                    <td><input type="text" name="items[0][pack_details]" class="form-control"
                                            required /></td>
                                    <td><input type="number" step="0.01" name="items[0][net_weight]"
                                            class="form-control" required /></td>
                                    <td><input type="number" step="0.01" name="items[0][gross_weight]"
                                            class="form-control" required /></td>
                                    <td><input type="number" step="0.01" name="items[0][price]" class="form-control"
                                            required /></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bank Details -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="bank_account">Bank Account</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="iban">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="swift_code">SWIFT Code</label>
                            <input type="text" class="form-control" id="swift_code" name="swift_code" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="company_name">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" />
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="total_bags">Total Master Bags</label>
                            <input type="number" class="form-control" id="total_bags" name="total_bags" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="total_net_weight">Total Net Weight (KG)</label>
                            <input type="number" step="0.01" class="form-control" id="total_net_weight"
                                name="total_net_weight" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="total_gross_weight">Total Gross Weight (KG)</label>
                            <input type="number" step="0.01" class="form-control" id="total_gross_weight"
                                name="total_gross_weight" />
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label" for="percentage">Percentage of Total Value (%)</label>
                            <input type="number" step="1" class="form-control" id="percentage" name="percentage" placeholder="Enter % value">
                        </div>
                        

                        <div class="row mb-3 mt-3">
                            
   

                            <div class="col-md-4 offset-md-8">
                                <label class="form-label" for="total_value_usd">Total Value</label>
                                <input type="number" step="0.01" class="form-control" id="total_value_usd"
                                    name="total_value_usd" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitButton">Save Quotation</button>
                            <a href="{{ route('quotations.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

<!-- Add JS for dynamic rows -->
@push('scripts')
    <script>
        let itemIndex = 1;

        // Add new item row
        document.getElementById('add-item').addEventListener('click', function() {
            const table = document.getElementById('items-table').getElementsByTagName('tbody')[0];
            const newRow = table.rows[0].cloneNode(true);
            Array.from(newRow.querySelectorAll('input')).forEach(input => {
                input.value = '';
                const name = input.getAttribute('name').replace(/\d+/, itemIndex);
                input.setAttribute('name', name);
            });
            table.appendChild(newRow);
            itemIndex++;
            attachRowEvents(newRow);
        });

        // Remove item row
        document.getElementById('items-table').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const rows = this.getElementsByTagName('tbody')[0].rows;
                if (rows.length > 1) {
                    e.target.closest('tr').remove();
                    updateTotals();
                }
            }
        });

        // Attach calculation events to all rows
        function attachRowEvents(row) {
            const netWeightInput = row.querySelector('input[name*="[net_weight]"]');
            const priceInput = row.querySelector('input[name*="[price]"]');
            const ratePerTonInput = document.getElementById('rate_per_ton');

            // When net weight or rate per ton changes, update price
            function updatePrice() {
                const netWeight = parseFloat(netWeightInput.value) || 0;
                const ratePerTon = parseFloat(ratePerTonInput.value) || 0;
                const tons = netWeight / 1000;
                priceInput.value = tons > 0 && ratePerTon > 0 ? (tons * ratePerTon).toFixed(2) : '';
                updateTotals();
            }

            netWeightInput.addEventListener('input', updatePrice);
            ratePerTonInput.addEventListener('input', function() {
                // Update all prices when rate per ton changes
                document.querySelectorAll('input[name*="[net_weight]"]').forEach(function(input) {
                    input.dispatchEvent(new Event('input'));
                });
            });

            // When any relevant field changes, update totals
            row.querySelectorAll('input').forEach(function(input) {
                input.addEventListener('input', updateTotals);
            });
        }

        // Attach to initial row
        document.querySelectorAll('#items-table tbody tr').forEach(attachRowEvents);

        // Update totals
        function updateTotals() {
            let totalBags = 0,
                totalNet = 0,
                totalGross = 0;
            document.querySelectorAll('#items-table tbody tr').forEach(function(row) {
                totalBags += parseInt(row.querySelector('input[name*="[no_of_bags]"]').value) || 0;
                totalNet += parseFloat(row.querySelector('input[name*="[net_weight]"]').value) || 0;
                totalGross += parseFloat(row.querySelector('input[name*="[gross_weight]"]').value) || 0;
            });
            document.getElementById('total_bags').value = totalBags;
            document.getElementById('total_net_weight').value = totalNet;
            document.getElementById('total_gross_weight').value = totalGross;
        }

        function updateTotals() {
            let totalBags = 0,
                totalNet = 0,
                totalGross = 0;
            document.querySelectorAll('#items-table tbody tr').forEach(function(row) {
                totalBags += parseInt(row.querySelector('input[name*="[no_of_bags]"]').value) || 0;
                totalNet += parseFloat(row.querySelector('input[name*="[net_weight]"]').value) || 0;
                totalGross += parseFloat(row.querySelector('input[name*="[gross_weight]"]').value) || 0;
            });
            document.getElementById('total_bags').value = totalBags;
            document.getElementById('total_net_weight').value = totalNet;
            document.getElementById('total_gross_weight').value = totalGross;

            // Calculate total value in USD
            const ratePerTon = parseFloat(document.getElementById('rate_per_ton').value) || 0;
            const totalValue = (totalNet / 1000) * ratePerTon;
            document.getElementById('total_value_usd').value = totalNet > 0 && ratePerTon > 0 ? totalValue.toFixed(2) : '';
        }

        document.getElementById('rate_per_ton').addEventListener('input', function() {
            // Update all prices when rate per ton changes
            document.querySelectorAll('input[name*="[net_weight]"]').forEach(function(input) {
                input.dispatchEvent(new Event('input'));
            });
            updateTotals();
        });
    </script>
@endpush
