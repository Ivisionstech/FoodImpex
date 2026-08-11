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
            width: 100%;
            text-align: center;
        }

        .logo {
            max-height: 70px;
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
            margin-bottom: 0px;
            font-size: 20px;
            border-bottom: none;
            display: block;
            padding-bottom: 2px;
            text-transform: uppercase;
        }

        .info-title-underline {
            width: 100%;
            height: 2px;
            background: linear-gradient(to right, #dc2526, transparent);
            margin: 3px 0 0 0;
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

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

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
        $logoSrc = '';
        $logoPath = public_path('images/logo.png');

        if (isset($companySettings) && $companySettings->logo) {
            $s_path = storage_path('app/public/' . $companySettings->logo);
            if (file_exists($s_path)) {
                $logoPath = $s_path;
            }
        }

        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
        }
    @endphp

    <div class="container">
        <!-- HEADER SECTION - Logo Centered -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                    @else
                        <div style="color: #34495e; font-weight: bold; text-align: center;">Logo</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- PARTY DETAILS SECTION -->
        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <!-- LEFT TABLE: COMPANY DETAILS -->
                <td style="width: 48%; padding-right: 20px; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; min-height: 160px;">
                        <tr>
                            <td
                                style="background: #f4f7f6; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; color: #000;">
                                CONSIGNEE NAME
                            </td>
                        </tr>
                        <tr>
                            <td style="background: #fff; border: 1px solid #dee2e6; padding: 8px;">
                                {{ $companySettings->name ?? 'Food Impex' }}
                            </td>
                        </tr>
                        <tr>
                            <td
                                style="background: #f4f7f6; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; color: #000;">
                                ADDRESS
                            </td>
                        </tr>
                        <tr>
                            <td style="background: #fff; border: 1px solid #dee2e6; padding: 8px;">
                                {{ $companySettings->address ?? 'GT Road, Gujranwala' }}
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- RIGHT TABLE: INVOICE DETAILS -->
                <td style="width: 52%; padding-left: 20px; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; min-height: 160px;">
                        <tr>
                            <td
                                style="background: #f4f7f6; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; color: #000; width: 40%;">
                                INVOICE NO.
                            </td>
                            <td style="background: #fff; border: 1px solid #dee2e6; padding: 8px;">
                                {{ 'SFIP' . str_pad($bill->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                        </tr>
                        <tr>
                            <td
                                style="background: #f4f7f6; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; color: #000;">
                                DATE
                            </td>
                            <td style="background: #fff; border: 1px solid #dee2e6; padding: 8px;">
                                {{ \Carbon\Carbon::parse($bill->date)->format('d-m-Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td
                                style="background: #f4f7f6; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; color: #000;">
                                PAYMENT TERMS
                            </td>
                            <td style="background: #fff; border: 1px solid #dee2e6; padding: 8px;">
                                {{ $bill->payment_terms ?? '100% IN 30 DAYS' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- PRODUCT LIST SECTION -->
        <table class="content-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 3%;">#</th>
                    <th style="width: 15%;">Product</th>
                    <th style="width: 18%;">Description </th>
                    <th class="text-center" style="width: 6%;">Qty</th>
                    <th class="text-center" style="width: 8%;">Packing</th>
                    <th class="text-center" style="width: 8%;">Total Wt</th>
                    <th class="text-center" style="width: 7%;">Bardana Wt</th>
                    <th class="text-center" style="width: 8%;">Net Wt</th>
                    <th class="text-right" style="width: 8%;">Rate/40kg</th>
                    <th class="text-right" style="width: 9%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach ($bill->billProducts as $index => $billProduct)
                    @php
                        $rowTotal = $billProduct->total_price ?? $billProduct->quantity * $billProduct->price;
                        $subtotal += $rowTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $billProduct->product->name }}</strong></td>
                        <td>{{ $billProduct->description ?? '--' }}</td>
                        <td class="text-center">{{ number_format($billProduct->quantity) }}</td>
                        <td class="text-center">{{ number_format($billProduct->packing ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($billProduct->total_weight ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($billProduct->bardana_weight ?? 0, 2) }}</td>
                        <td class="text-center">{{ number_format($billProduct->net_weight ?? 0, 2) }}</td>
                        <td class="text-right">{{ number_format($billProduct->price, 2) }}</td>
                        <td class="text-right">{{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
                <!-- Extra Charges Row: show charge NAMES centered and total in last column -->
                @php
                    $totalExtraCharges = 0;
                    $extraNames = [];
                    foreach ($bill->extraCharges as $charge) {
                        $totalExtraCharges += $charge->amount;
                        $extraNames[] = $charge->name;
                    }
                    $extraNamesStr = implode(', ', $extraNames);
                @endphp
                @if ($totalExtraCharges > 0)
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="9" class="text-center">{{ $extraNamesStr ?: 'Extra Charges (Subtract)' }}</td>
                        <td class="text-right"> {{ number_format($totalExtraCharges, 2) }}</td>
                    </tr>
                @endif
                <!-- Net Amount Row -->
                @php
                    // Net amount = subtotal - Extra Charges (Subtract)
                    $netAmount = $subtotal - $totalExtraCharges;
                @endphp
                <tr style="background-color: #e8e8e8; font-weight: bold;">
                    <td colspan="9" class="text-center">NET AMOUNT</td>
                    <td class="text-right"> {{ number_format($netAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTALS SECTION -->
        <div class="summary-wrapper clearfix">
            <table class="totals-table">
                <tr>
                    <td colspan="2"
                        style="background: #f4f7f6; text-align: center; font-weight: bold; padding: 8px;">
                        ADDITIONAL CHARGES
                    </td>
                </tr>
                @foreach ($bill->additionalCharges as $charge)
                    <tr>
                        <td>{{ $charge->name }} </td>
                        <td class="text-right"> {{ number_format($charge->amount, 2) }}</td>
                    </tr>
                @endforeach

            </table>
        </div>

        @php
            $amount = number_format($bill->total_amount ?? 0, 2, '.', '');
            [$intPart, $decPart] = explode('.', $amount);
            $fmt = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
            $intWords = $fmt->format((int) $intPart);
            $intWords = preg_replace('/\s+/', ' ', trim($intWords));
            if ($decPart === '00') {
                $amountInWords = strtoupper($intWords) . ' ONLY';
            } else {
                $amountInWords = strtoupper($intWords) . ' AND ' . $decPart . '/100 ONLY';
            }
        @endphp

        <div style="width:100%; margin-top:10px;">
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr>
                    <td
                        style="padding: 10px; text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; border: 2px solid #000;">
                        SUB TOTAL PAYABLE AFTER ADDING ADDITIONAL CHARGE
                    </td>
                    <td
                        style="padding: 10px; text-align: right; font-weight: bold; font-size: 14px; border: 2px solid #000;">
                        {{ number_format($bill->total_amount, 2) }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"
                        style="padding: 8px; text-align: center; font-weight: bold; border: 2px solid #000;">
                        ({{ $amountInWords }})
                    </td>
                </tr>
            </table>
        </div>

        @php
            $stampSrc = '';
            $stampPath = public_path('images/stamp.png');
            if (file_exists($stampPath)) {
                $stampData = base64_encode(file_get_contents($stampPath));
                $stampSrc = 'data:image/' . pathinfo($stampPath, PATHINFO_EXTENSION) . ';base64,' . $stampData;
            }
        @endphp

        @if ($stampSrc)
            <div style="width:100%; margin-top:30px; text-align: left;">
                <img src="{{ $stampSrc }}" alt="Stamp" style="max-height:150px; width:auto;">
            </div>
        @endif
        <div style="width:100%; text-align:center; margin-top:8px; font-size:12px; font-weight:bold;">
            RANA SHOUKAT PLAZA OFFICE NO 1 GHALLA MANDI MAIN G.T ROAD KAMOKE,GUJRANWALA
        </div>
        <div style="width:100%; text-align:center; margin-top:6px; font-size:12px; color:#333;">
            <span style="display:inline-flex; align-items:center; gap:6px; margin:0 8px;">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='1' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='5' width='18' height='14' rx='2' ry='2'%3E%3C/rect%3E%3Cpolyline points='3,7 12,13 21,7'%3E%3C/polyline%3E%3C/svg%3E"
                    alt="email" style="width:10px; height:10px; display:block;">
                <a href="mailto:info@syedfoodimpex.com"
                    style="color:inherit; text-decoration:none;">info@syedfoodimpex.com</a>
            </span>
            <span style="display:inline-flex; align-items:center; gap:6px; margin:0 8px;">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='1' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'%3E%3C/circle%3E%3Cpath d='M2 12h20'%3E%3C/path%3E%3Cpath d='M12 2a15 15 0 0 0 0 20'%3E%3C/path%3E%3C/svg%3E"
                    alt="web" style="width:10px; height:10px; display:block;">
                <a href="https://syedfoodimpex.com" target="_blank"
                    style="color:inherit; text-decoration:none;">https://syedfoodimpex.com</a>
            </span>
            <span style="display:inline-flex; align-items:center; gap:6px; margin:0 8px;">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='1' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.72c.12 1.05.35 2.07.68 3.03a2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.05-1.05a2 2 0 0 1 2.11-.45c.96.33 1.98.56 3.03.68A2 2 0 0 1 22 16.92z'%3E%3C/path%3E%3C/svg%3E"
                    alt="phone" style="width:10px; height:10px; display:block;">
                <a href="tel:+92556816969" style="color:inherit; text-decoration:none;">+92 55 6816969</a>
            </span>
        </div>
    </div>
</body>

</html>