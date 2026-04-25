@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-4">
            <span class="text-muted fw-light">Dashboard /</span>
            <a class="text-muted fw-light" href="{{ route('quotations.list') }}">Quotations</a> /
            Edit Quotation
        </h4>
        <div class="card">
            <h5 class="card-header">Edit Quotation</h5>
            <div class="card-body">
                <form class="ajax-form" action="{{ route('quotations.update', $quotation->uuid) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT') {{-- Use PUT method for update --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="consignee_name">Consignee Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="consignee_name" name="consignee_name"
                                value="{{ old('consignee_name', $quotation->consignee_name) }}" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="consignee_address">Consignee Address <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="consignee_address" name="consignee_address"
                                value="{{ old('consignee_address', $quotation->consignee_address) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="invoice_no">Invoice No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="invoice_no" name="invoice_no"
                                value="{{ old('invoice_no', $quotation->invoice_no) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="invoice_date">Invoice Date <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="invoice_date" name="invoice_date"
                                value="{{ old('invoice_date', \Carbon\Carbon::parse($quotation->invoice_date)->format('Y-m-d\TH:i')) }}"
                                required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="fi_no">F.I. No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fi_no" name="fi_no"
                                value="{{ old('fi_no', $quotation->fi_no) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="destination">Destination <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="destination" name="destination"
                                value="{{ old('destination', $quotation->destination) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payment_term">Payment Term <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payment_term" name="payment_term"
                                value="{{ old('payment_term', $quotation->payment_term) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="freight_term">Freight Term <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="freight_term" name="freight_term"
                                value="{{ old('freight_term', $quotation->freight_term) }}" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="hs_code">HS Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hs_code" name="hs_code"
                                value="{{ old('hs_code', $quotation->hs_code) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="brand">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brand" name="brand"
                                value="{{ old('brand', $quotation->description) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="rate_per_ton">Rate per Ton <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="rate_per_ton"
                                name="rate_per_ton" value="{{ old('rate_per_ton', $quotation->rate_per_ton) }}" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="currency">Currency <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control" required>
                                <option value="USD" {{ $quotation->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="PKR" {{ $quotation->currency == 'PKR' ? 'selected' : '' }}>PKR</option>
                                <option value="EUR" {{ $quotation->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ $quotation->currency == 'GBP' ? 'selected' : '' }}>GBP</option>
                                <option value="INR" {{ $quotation->currency == 'INR' ? 'selected' : '' }}>INR</option>
                                <option value="AED" {{ $quotation->currency == 'AED' ? 'selected' : '' }}>AED</option>
                                <option value="SAR" {{ $quotation->currency == 'SAR' ? 'selected' : '' }}>SAR</option>
                                <option value="QAR" {{ $quotation->currency == 'QAR' ? 'selected' : '' }}>QAR</option>
                                <option value="KWD" {{ $quotation->currency == 'KWD' ? 'selected' : '' }}>KWD</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="shipment_mode">Shipment Mode <span
                                    class="text-danger">*</span></label>
                            <select name="shipment_mode" id="shipment_mode" class="form-control" required>
                                <option value="">Select Mode</option>
                                <option value="By Sea" {{ $quotation->shipment_mode == 'By Sea' ? 'selected' : '' }}>By Sea</option>
                                <option value="By Road" {{ $quotation->shipment_mode == 'By Road' ? 'selected' : '' }}>By Road</option>
                            </select>
                        </div>


                        <div class="col-md-4">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="{{ old('address', $quotation->address) }}" required />
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
                                @forelse ($quotation->items as $index => $item)
                                    <tr>
                                        <td><input type="hidden" name="items[{{ $index }}][uuid]"
                                                value="{{ $item->uuid }}">
                                            <input type="number" name="items[{{ $index }}][no_of_bags]"
                                                class="form-control" value="{{ old('items.' . $index . '.no_of_bags', $item->no_of_bags) }}"
                                                required />
                                        </td>
                                        <td><input type="text" name="items[{{ $index }}][pack_details]"
                                                class="form-control" value="{{ old('items.' . $index . '.pack_details', $item->package_details) }}"
                                                required />
                                        </td>
                                        <td><input type="number" step="0.01"
                                                name="items[{{ $index }}][net_weight]" class="form-control"
                                                value="{{ old('items.' . $index . '.net_weight', $item->net_weight) }}"
                                                required />
                                        </td>
                                        <td><input type="number" step="0.01"
                                                name="items[{{ $index }}][gross_weight]" class="form-control"
                                                value="{{ old('items.' . $index . '.gross_weight', $item->gross_weight) }}"
                                                required />
                                        </td>
                                        <td><input type="number" step="0.01" name="items[{{ $index }}][price]"
                                                class="form-control" value="{{ old('items.' . $index . '.price', $item->total_value) }}"
                                                required readonly />
                                        </td>
                                        <td>
                                            @if ($loop->first)
                                                <button type="button" class="btn btn-sm btn-danger remove-item"
                                                    disabled>-</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger remove-item">-</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
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
                                                required readonly /></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-item" disabled>-</button></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bank Details -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="bank_account">Bank Account</label>
                            <input type="text" class="form-control" id="bank_account" name="bank_account"
                                value="{{ old('bank_account', $quotation->bank_account) }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="iban">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban"
                                value="{{ old('iban', $quotation->iban) }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="swift_code">SWIFT Code</label>
                            <input type="text" class="form-control" id="swift_code" name="swift_code"
                                value="{{ old('swift_code', $quotation->swift_code) }}" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="company_name">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                value="{{ old('company_name', $quotation->company_name) }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name"
                                value="{{ old('bank_name', $quotation->bank_name) }}" />
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label" for="total_bags">Total Master Bags</label>
                            <input type="number" class="form-control" id="total_bags" name="total_bags"
                                value="{{ old('total_bags', $quotation->total_bags) }}" readonly />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="total_net_weight">Total Net Weight (KG)</label>
                            <input type="number" step="0.01" class="form-control" id="total_net_weight"
                                name="total_net_weight" value="{{ old('total_net_weight', $quotation->total_net_weight) }}"
                                readonly />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="total_gross_weight">Total Gross Weight (KG)</label>
                            <input type="number" step="0.01" class="form-control" id="total_gross_weight"
                                name="total_gross_weight"
                                value="{{ old('total_gross_weight', $quotation->total_gross_weight) }}" readonly />
                        </div>
                        <div class="col-md-4 mt-3">
                            <label class="form-label" for="percentage">Percentage of Total Value (%)</label>
                            <input type="number" step="1" class="form-control" id="percentage" name="percentage"
                                value="{{ old('percentage', $quotation->percentage ?? 0) }}" placeholder="Enter % value">
                        </div>

                        <div class="row mb-3 mt-3">
                            <div class="col-md-4 offset-md-8">
                                <label class="form-label" for="total_value_usd">Total Value</label>
                                <input type="number" step="0.01" class="form-control" id="total_value_usd"
                                    name="total_value_usd" value="{{ old('total_value_usd', $quotation->total_value_usd) }}"
                                    readonly />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submitButton">Update Quotation</button>
                            <a href="{{ route('quotations.list') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemIndex = {{ count($quotation->items) > 0 ? count($quotation->items) : 1 }};

       
        function attachRowEvents(row) {
            const netWeightInput = row.querySelector('input[name*="[net_weight]"]');
            const priceInput = row.querySelector('input[name*="[price]"]');
            const ratePerTonInput = document.getElementById('rate_per_ton');

            function updatePrice() {
                const netWeight = parseFloat(netWeightInput.value) || 0;
                const ratePerTon = parseFloat(ratePerTonInput.value) || 0;
                const tons = netWeight / 1000;
                let calculatedPrice = (tons * ratePerTon).toFixed(2);
                priceInput.value = calculatedPrice;

                updateTotals();
            }

            netWeightInput.addEventListener('input', updatePrice);
            priceInput.addEventListener('input', updateTotals); 
            row.querySelectorAll('input:not([name*="[net_weight]"]):not([name*="[price]"])').forEach(function(input) {
                input.addEventListener('input', updateTotals);
            });
        }

        
        document.getElementById('add-item').addEventListener('click', function() {
            const tableBody = document.getElementById('items-table').getElementsByTagName('tbody')[0];
            const newRowHtml = `
                <tr>
                    <td><input type="number" name="items[${itemIndex}][no_of_bags]" class="form-control" required /></td>
                    <td><input type="text" name="items[${itemIndex}][pack_details]" class="form-control" required /></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][net_weight]" class="form-control" required /></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][gross_weight]" class="form-control" required /></td>
                    <td><input type="number" step="0.01" name="items[${itemIndex}][price]" class="form-control" required readonly /></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-item">-</button></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRowHtml);
            const newRow = tableBody.lastElementChild;
            attachRowEvents(newRow); 
            itemIndex++;
            updateRemoveButtons(); 
            updateTotals();
        });

        
        document.getElementById('items-table').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item')) {
                const rows = this.getElementsByTagName('tbody')[0].rows;
                if (rows.length > 1) {
                    e.target.closest('tr').remove();
                    updateRemoveButtons(); 
                    updateTotals(); 
                }
            }
        });

        
        function updateTotals() {
            let totalBags = 0;
            let totalNet = 0;
            let totalGross = 0;
            let totalCalculatedPrice = 0; 

            document.querySelectorAll('#items-table tbody tr').forEach(function(row) {
                totalBags += parseInt(row.querySelector('input[name*="[no_of_bags]"]').value) || 0;
                totalNet += parseFloat(row.querySelector('input[name*="[net_weight]"]').value) || 0;
                totalGross += parseFloat(row.querySelector('input[name*="[gross_weight]"]').value) || 0;
                totalCalculatedPrice += parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
            });

            document.getElementById('total_bags').value = totalBags;
            document.getElementById('total_net_weight').value = totalNet.toFixed(2);
            document.getElementById('total_gross_weight').value = totalGross.toFixed(2);

           
            document.getElementById('total_value_usd').value = totalCalculatedPrice.toFixed(2);
        }

        
        document.getElementById('rate_per_ton').addEventListener('input', function() {
            document.querySelectorAll('#items-table tbody tr').forEach(function(row) {
                const netWeightInput = row.querySelector('input[name*="[net_weight]"]');
                const priceInput = row.querySelector('input[name*="[price]"]');
                const ratePerTon = parseFloat(document.getElementById('rate_per_ton').value) || 0;
                const netWeight = parseFloat(netWeightInput.value) || 0;
                const tons = netWeight / 1000;
                priceInput.value = (tons * ratePerTon).toFixed(2);
            });
            updateTotals();
        });

      
        function updateRemoveButtons() {
            const rows = document.getElementById('items-table').getElementsByTagName('tbody')[0].rows;
            if (rows.length === 1) {
                rows[0].querySelector('.remove-item').setAttribute('disabled', 'true');
            } else {
                Array.from(rows).forEach(row => row.querySelector('.remove-item').removeAttribute('disabled'));
            }
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            
            document.querySelectorAll('#items-table tbody tr').forEach(attachRowEvents);
            updateRemoveButtons(); 
            updateTotals(); 
        });
    </script>
@endpush