<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Bill #{{ $bill->id }}</title>
    <style>
        /* Basic reset for PDF consistency */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .header-table td {
            padding: 5px;
            vertical-align: middle;
        }

        .logo-section {
            width: 60%;
        }

        .logo {
            max-height: 60px;
            width: auto;
        }

        .bill-section {
            width: 40%;
            text-align: right;
        }

        .bill-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Information Boxes */
        .info-table {
            width: 100%;
            background: #f4f7f6;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .info-table td {
            padding: 15px;
            vertical-align: top;
            width: 50%;
        }

        .info-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 15px;
            border-bottom: 2px solid #34495e;
            display: inline-block;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        .info-detail {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            width: 70px;
            display: inline-block;
        }

        /* Main Items Table */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .content-table th {
            background: #34495e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #2c3e50;
        }

        .content-table td {
            padding: 8px;
            border: 1px solid #bdc3c7;
            font-size: 11px;
            vertical-align: top;
        }

        .content-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Summary and Totals Section */
        .summary-wrapper {
            width: 100%;
            margin-top: 10px;
        }

        .totals-table {
            width: 250px;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 7px 10px;
            border: 1px solid #dee2e6;
        }

        .total-row {
            background: #2c3e50;
            color: white;
            font-weight: bold;
        }

        /* Transaction / Ledger Info Box */
        .transaction-box {
            width: 100%;
            background: #eef2f3;
            margin-top: 20px;
            padding: 12px;
            border-left: 5px solid #34495e;
            clear: both;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #95a5a6;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
    @php
        /**
         * LOGO HANDLING
         */
        $logoSrc = "";
        $logoPath = public_path('images/Intikhab-logo-scaled-copy-2048x560-1.png');

        if(isset($companySettings) && $companySettings->logo) {
            $s_path = storage_path('app/public/' . $companySettings->logo);
            if(file_exists($s_path)) {
                $logoPath = $s_path;
            }
        }

        if(file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
        }
    @endphp

    <div class="container">
        <!-- HEADER SECTION -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                    @else
                        <h2 style="color: #34495e;">{{ $companySettings->name ?? 'Purchase Bill' }}</h2>
                    @endif
                </td>
                <td class="bill-section">
                    <div class="bill-title">Purchase Bill</div>
                    <div style="margin-top: 5px;">
                        <strong>Bill ID:</strong> #{{ $bill->id }}<br>
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($bill->date)->format('d-M-Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- PARTY DETAILS SECTION -->
        <table class="info-table">
            <tr>
                <td>
                    <!-- Purchaser Name in Title -->
                    <div class="info-title">{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</div>
                    <div class="info-detail">{{ $companySettings->address ?? 'GT Road, Gujranwala' }}</div>
                    <div class="info-detail">Phone: {{ $companySettings->mobile ?? '03024636875' }}</div>
                    <div class="info-detail">Mail: {{ $companySettings->email ?? 'N/A' }}</div>
                </td>
                <td>
                    <!-- Seller Name in Title -->
                    <div class="info-title">{{ $bill->vendor->company_name }}</div>
                    <div class="info-detail">Contact: {{ $bill->vendor->contact_person ?: 'N/A' }}</div>
                    <div class="info-detail">Phone: {{ $bill->vendor->phone }}</div>
                    <div class="info-detail">Address: {{ $bill->vendor->address ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>

        <!-- PRODUCT LIST SECTION -->
        <table class="content-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 25%;">Product</th>
                    <th style="width: 35%;">Description / Remarks</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Price</th>
                    <th class="text-right" style="width: 13%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach ($bill->billProducts as $index => $billProduct)
                    @php
                        $rowTotal = $billProduct->quantity * $billProduct->price;
                        $subtotal += $rowTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $billProduct->product->name }}</strong></td>
                        <td>{{ $billProduct->description ?? '--' }}</td>
                        <td class="text-center">{{ number_format($billProduct->quantity) }}</td>
                        <td class="text-right">{{ number_format($billProduct->price, 2) }}</td>
                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALS SECTION -->
        <div class="summary-wrapper clearfix">
            <table class="totals-table">
                <tr>
                    <td>Items Total</td>
                    <td class="text-right">PKR {{ number_format($subtotal, 2) }}</td>
                </tr>
                @foreach ($bill->extraCharges as $charge)
                    <tr>
                        <td>{{ $charge->name }}</td>
                        <td class="text-right">PKR {{ number_format($charge->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>GRAND TOTAL</strong></td>
                    <td class="text-right"><strong>PKR {{ number_format($bill->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- ACCOUNT IMPACT SECTION -->
        @if ($bill->vendorTransaction)
            <div class="transaction-box">
                <div style="font-weight: bold; margin-bottom: 5px; color: #2c3e50;">Account Summary</div>
                <div style="font-size: 11px;">
                    This transaction has been posted to the ledger.<br>
                    <strong>Outstanding Balance:</strong> PKR {{ number_format(abs($bill->vendorTransaction->current_balance), 2) }}
                    ({{ $bill->vendorTransaction->current_balance < 0 ? 'DR' : 'CR' }})
                </div>
            </div>
        @endif

        <div class="footer">
            <p>Computer-generated Purchase Bill. No signature required.</p>
            <p>Generated on {{ now()->format('d-M-Y H:i A') }}</p>
        </div>
    </div>
</body>
</html>
