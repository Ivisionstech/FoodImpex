<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bill #{{ $bill->id }}</title>
    <style>
        /* CSS resets and base styles */
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #34495e;
            margin-bottom: 20px;
        }

        .header-table td {
            padding-bottom: 10px;
            vertical-align: middle;
        }

        .logo-section {
            width: 50%;
        }

        .logo-img {
            max-height: 70px;
            max-width: 250px;
            display: block;
        }

        .bill-section {
            width: 50%;
            text-align: right;
        }

        .bill-title {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Info Boxes */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 10px 0;
            margin-left: -10px;
        }

        .info-table td {
            width: 50%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            color: #34495e;
            font-size: 14px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        .detail-item {
            margin-bottom: 3px;
            font-size: 12px;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 70px;
            display: inline-block;
        }

        /* Main Product Table */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .content-table th {
            background: #34495e;
            color: #ffffff;
            padding: 10px 8px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #2c3e50;
        }

        .content-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }

        .content-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        /* Summary Calculations */
        .summary-wrapper {
            width: 100%;
        }

        .totals-table {
            width: 280px;
            float: right;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 7px 12px;
            border: 1px solid #dee2e6;
        }

        .total-row {
            background: #34495e;
            color: white;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 11px;
            color: #7f8c8d;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>

<body>
    @php
        /**
         * IMAGE HANDLING LOGIC
         */
        $logoSrc = null;

        // Path 1: Database dynamic logo
        if (isset($companySettings) && $companySettings->logo) {
            $dynamicPath = storage_path('app/public/' . $companySettings->logo);
            if (file_exists($dynamicPath)) {
                $ext = pathinfo($dynamicPath, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($dynamicPath));
                $logoSrc = 'data:image/' . $ext . ';base64,' . $data;
            }
        }

        // Path 2: Fallback to default static logo if dynamic fails
        if (!$logoSrc) {
            $staticPath = public_path('images/Intikhab-logo-scaled-copy-2048x560-1.png');
            if (file_exists($staticPath)) {
                $ext = pathinfo($staticPath, PATHINFO_EXTENSION);
                $data = base64_encode(file_get_contents($staticPath));
                $logoSrc = 'data:image/' . $ext . ';base64,' . $data;
            }
        }
    @endphp

    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo-img">
                    @else
                        <h2 style="color: #34495e;">{{ $companySettings->name ?? 'Intekhab Sanitary' }}</h2>
                    @endif
                </td>
                <td class="bill-section">
                    <div class="bill-title">INVOICE</div>
                    <div style="font-size: 12px; color: #555;">
                        Invoice No: <strong>#{{ $bill->id }}</strong><br>
                        Date: {{ \Carbon\Carbon::parse($bill->bill_date)->format('d M, Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Info Boxes -->
        <table class="info-table">
            <tr>
                <td>
                    <div class="section-title">{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</div>
                    <div class="detail-item">{{ $companySettings->address ?? 'GT ROAD GUJRANWALA' }}</div>
                    <div class="detail-item"><span class="label">Phone:</span>
                        {{ $companySettings->mobile ?? '03024636875' }}</div>
                    <div class="detail-item"><span class="label">Email:</span>
                        {{ $companySettings->email ?? 'intekhabsanitryfiiting@gmail.com' }}</div>
                </td>
                <td>
                    <div class="section-title">
                        {{ $bill->customer ? $bill->customer->name : $bill->customer_name . ' (Walk-in)' }}</div>
                    <div class="detail-item"><span class="label">Phone:</span>
                        {{ $bill->customer?->phone ?? ($bill->customer_phone ?? 'N/A') }}</div>
                    <div class="detail-item"><span class="label">Email:</span> {{ $bill->customer->email ?? 'N/A' }}
                    </div>
                    <div class="detail-item"><span class="label">Address:</span>
                        {{ $bill->customer->address ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="content-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="45%">Product Description</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="12%" class="text-right">Price</th>
                    <th width="12%" class="text-right">Disc.</th>
                    <th width="16%" class="text-right">Total (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bill->billProducts as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                        <td class="text-right">
                            {{ number_format($item->quantity * $item->price - $item->discount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-wrapper">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">PKR
                        {{ number_format($bill->total_amount - $bill->extraCharges->sum('amount'), 2) }}</td>
                </tr>
                @foreach ($bill->extraCharges as $charge)
                    <tr>
                        <td>{{ $charge->name }}</td>
                        <td class="text-right">PKR {{ number_format($charge->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>NET TOTAL</td>
                    <td class="text-right">PKR {{ number_format($bill->total_amount, 2) }}</td>
                </tr>
            </table>
            <div class="clearfix"></div>
        </div>

        <!-- Ledger/Balance Info -->
        @if ($bill->customerTransaction)
            <div style="margin-top: 25px; border: 1px solid #dee2e6; padding: 10px; background-color: #fcfcfc;">
                <h4
                    style="font-size: 13px; color: #34495e; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 5px;">
                    Ledger Summary</h4>
                <div style="font-size: 12px;">Current Outstanding Balance: <strong>PKR
                        {{ number_format($bill->customerTransaction->current_balance, 2) }}</strong></div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This is a computer-generated invoice. No signature is required.</p>
            <p style="margin-top: 5px;">Printed on: {{ date('d-M-Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
