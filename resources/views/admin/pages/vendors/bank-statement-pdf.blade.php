<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Bank Statement - {{ $vendor->company_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            display: block;
        }

        .logo-container {
            margin-bottom: 10px;
        }

        .logo-img {
            max-width: 150px;
            max-height: 100px;
            object-fit: contain;
        }

        .header h1 {
            margin: 10px 0 0 0;
            color: #333;
            font-size: 36px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .statement-title {
            background-color: #d3d3d3;
            text-align: center;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid #000;
            margin-bottom: 0;
        }

        .vendor-details {
            background-color: #d3d3d3;
            padding: 12px;
            text-align: center;
            border: 2px solid #000;
            border-top: none;
            margin-bottom: 20px;
        }

        .vendor-details .party-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 2px solid #000;
        }

        .transactions-table th {
            padding: 10px 5px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid #000;
            font-size: 13px;
            background-color: #c8b4e8;
        }

        .transactions-table td {
            border: 2px solid #000;
            padding: 8px 5px;
            font-size: 12px;
            vertical-align: top; /* Added to keep long descriptions clean */
        }

        .col-srno { width: 40px; }
        .col-date { width: 90px; }
        .col-debit { background-color: #ffcccc !important; width: 110px; }
        .col-credit { background-color: #ccffcc !important; width: 110px; }
        .col-balance { background-color: #ffecb3 !important; width: 130px; }

        .amount-debit, .amount-credit, .balance {
            text-align: right !important;
            font-weight: bold;
        }

        .dr-cr-cell {
            text-align: center !important;
            font-weight: bold;
            background-color: #e3f2fd;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .item-desc {
            font-size: 10px;
            color: #555;
            font-style: italic;
            display: block;
            margin-top: 2px;
            border-top: 1px dotted #ccc;
        }

        @media print {
            body { padding: 0; }
            .transactions-table th { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body>
    @php
        // LOGO DYNAMIC HANDLING
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

    <div class="header">
        <div class="logo-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
            @endif
        </div>
        <h1>{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</h1>
        <div style="font-size: 14px; margin-top: 5px;">
            {{ $companySettings->address ?? '' }} | {{ $companySettings->mobile ?? '' }}
        </div>
    </div>

    <div class="statement-title">
        ACCOUNTS STATEMENT LEDGER
    </div>

    <div class="vendor-details">
        <div class="party-name">PARTY NAME: {{ strtoupper($vendor->company_name) }}</div>
        <div class="address">ADDRESS: {{ strtoupper($vendor->address ?? 'N/A') }}</div>
    </div>

   <table class="transactions-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-srno">SR.<br>NO</th>
                <th rowspan="2" class="col-date">DATE</th>
                <th>DETAILS</th>
                <th rowspan="2" class="col-debit">DEBIT (Payment)</th>
                <th rowspan="2" class="col-credit">CREDIT (Purchase)</th>
                <th rowspan="2" style="width: 50px;">DR/CR</th>
                <th rowspan="2" class="col-balance">BALANCE (PKR)</th>
            </tr>
            <tr>
                <th>DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendorTransactions as $index => $transaction)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}</td>
                    <td>
                        @if ($transaction->type == 'bill' && $transaction->bill)
                            Purchase Bill #{{ $transaction->bill->id }}
                        @elseif ($transaction->type == 'payment')
                            Payment Sent {{ $transaction->send_via ? 'via ' . $transaction->send_via : '' }}
                        @elseif ($transaction->type == 'Balance')
                            Opening Balance
                        @else
                            {{ ucfirst($transaction->type) }} - {{ $transaction->description ?? '-' }}
                        @endif
                    </td>
                    <td class="amount-debit">
                         {{ ($transaction->type == 'payment' || $transaction->type == 'Balance') ? number_format($transaction->amount, 0) : '' }}

                    </td>
                    <td class="amount-credit">
                        {{ $transaction->type == 'bill' ? number_format($transaction->amount, 0) : '' }}

                    </td>
                    <td class="dr-cr-cell">
                        {{ $transaction->current_balance < 0 ? 'DR' : 'CR' }}
                    </td>
                    <td class="balance">
                        {{ number_format(abs($transaction->current_balance), 0) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This statement contains {{ $vendorTransactions->count() }} transaction(s).</p>
        <p><strong>{{ $companySettings->name ?? '' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <script>
        window.onload = function() {
            // window.print();
        };
    </script>
</body>
</html>
