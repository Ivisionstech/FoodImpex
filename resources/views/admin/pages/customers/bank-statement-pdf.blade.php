<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bank Statement - {{ $customer->name }}</title>
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

        .customer-details {
            background-color: #d3d3d3;
            padding: 12px;
            text-align: center;
            border: 2px solid #000;
            border-top: none;
            margin-bottom: 20px;
        }

        .customer-details .party-name {
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
            vertical-align: top;
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

        .negative-balance {
            color: #dc3545;
            font-weight: bold;
        }

        .positive-balance {
            color: #28a745;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            margin-top: 3px;
        }
        
        .badge-debit { background-color: #dc3545; color: white; }
        .badge-credit { background-color: #28a745; color: white; }
        .badge-sales { background-color: #dc3545; color: white; }
        .badge-payment { background-color: #28a745; color: white; }

        .summary-section {
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #000;
            background-color: #f8f9fa;
        }

        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            background-color: #d3d3d3;
            padding: 5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            font-size: 12px;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            font-weight: bold;
        }

        @media print {
            body { padding: 0; }
            .transactions-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body>
    @php
        // LOGO DYNAMIC HANDLING
        $logoSrc = "";
        $logoPath = public_path('images/logo.png');

        if(isset($companySettings) && $companySettings && $companySettings->logo) {
            $s_path = storage_path('app/public/' . $companySettings->logo);
            if(file_exists($s_path)) {
                $logoPath = $s_path;
            }
        }

        if(file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
        }
        
        // Use $allTransactions (passed from controller)
        $transactions = $allTransactions ?? collect();
        
        // Calculate summary totals
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach($transactions as $transaction) {
            $amount = floatval($transaction->amount);
            $type = $transaction->type;
            
            if ($type == 'bill' || $type == 'debit') {
                $totalDebits += $amount;
            } elseif ($type == 'payment' || $type == 'credit') {
                $totalCredits += $amount;
            }
        }
        
        $netBalance = $totalDebits - $totalCredits;
    @endphp

    <div class="header">
        <div class="logo-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
            @endif
        </div>
        <h1>{{ $companySettings->name ?? 'Food Impex' }}</h1>
        <div style="font-size: 14px; margin-top: 5px;">
            {{ $companySettings->address ?? '' }} | {{ $companySettings->mobile ?? '' }}
        </div>
    </div>

    <div class="statement-title">
        ACCOUNTS STATEMENT LEDGER
    </div>

    <div class="customer-details">
        <div class="party-name">CUSTOMER NAME: {{ strtoupper($customer->name ?? 'N/A') }}</div>
        <div class="address">ADDRESS: {{ strtoupper($customer->address ?? 'N/A') }}</div>
        <div style="font-size: 12px; margin-top: 5px;">PHONE: {{ $customer->phone ?? 'N/A' }}</div>
        <div style="font-size: 13px; margin-top: 8px; font-weight: bold;">
            Current Balance: 
            <span class="{{ ($customer->balance ?? 0) < 0 ? 'negative-balance' : 'positive-balance' }}">
                PKR {{ number_format(abs($customer->balance ?? 0), 2) }} {{ ($customer->balance ?? 0) < 0 ? 'DR' : 'CR' }}
            </span>
        </div>
    </div>

   <table class="transactions-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-srno">SR.<br>NO</th>
                <th rowspan="2" class="col-date">DATE</th>
                <th>DETAILS</th>
                <th rowspan="2" class="col-debit">DEBIT<br><span style="font-size: 9px;">(Sales/Out)</span></th>
                <th rowspan="2" class="col-credit">CREDIT<br><span style="font-size: 9px;">(Payment/In)</span></th>
                <th rowspan="2" style="width: 50px;">DR/CR</th>
                <th rowspan="2" class="col-balance">BALANCE (PKR)</th>
            </tr>
            <tr>
                <th>DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
                @php
                    $amount = floatval($transaction->amount);
                    $type = $transaction->type;
                    $description = $transaction->description ?? '';
                    $debitAmount = 0;
                    $creditAmount = 0;
                    $displayType = '';
                    $badgeClass = '';
                    $badgeText = '';
                    $currentBalance = isset($transaction->current_balance) ? floatval($transaction->current_balance) : 0;
                    
                    if ($type == 'bill' || $type == 'debit') {
                        $debitAmount = $amount;
                        $displayType = $type == 'bill' ? 'Sales Invoice' : 'Debit Entry';
                        $badgeClass = 'badge-debit';
                        $badgeText = $type == 'bill' ? 'SALES' : 'DEBIT';
                        $descriptionText = $description ?: ($type == 'bill' ? 'Sales invoice' : 'Debit transaction');
                    } elseif ($type == 'payment' || $type == 'credit') {
                        $creditAmount = $amount;
                        $displayType = $type == 'payment' ? 'Payment Received' : 'Credit Entry';
                        $badgeClass = 'badge-credit';
                        $badgeText = $type == 'payment' ? 'PAYMENT' : 'CREDIT';
                        $descriptionText = $description ?: ($type == 'payment' ? 'Payment received' : 'Credit transaction');
                    } elseif ($type == 'balance') {
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $displayType = 'Opening Balance (Credit)';
                            $badgeClass = 'badge-credit';
                            $badgeText = 'OPENING CR';
                        } else {
                            $debitAmount = abs($amount);
                            $displayType = 'Opening Balance (Debit)';
                            $badgeClass = 'badge-debit';
                            $badgeText = 'OPENING DR';
                        }
                        $descriptionText = $description ?: 'Initial opening balance';
                    } else {
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $displayType = 'Credit Entry';
                            $badgeClass = 'badge-credit';
                            $badgeText = 'CREDIT';
                        } else {
                            $debitAmount = abs($amount);
                            $displayType = 'Debit Entry';
                            $badgeClass = 'badge-debit';
                            $badgeText = 'DEBIT';
                        }
                        $descriptionText = $description ?: ucfirst($type);
                    }
                    
                    $balanceDisplay = number_format(abs($currentBalance), 2);
                    $drCrDisplay = $currentBalance < 0 ? 'DR' : 'CR';
                    $balanceClass = $currentBalance < 0 ? 'negative-balance' : ($currentBalance > 0 ? 'positive-balance' : '');
                    $transactionDate = $transaction->transaction_date ?? $transaction->date ?? $transaction->created_at ?? now();
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        {{ \Carbon\Carbon::parse($transactionDate)->format('d-m-Y') }}
                        <div style="font-size: 9px; color: #666;">
                            {{ \Carbon\Carbon::parse($transactionDate)->format('h:i A') }}
                        </div>
                    </td>
                    <td>
                        <strong>{{ $displayType }}</strong>
                        @if($descriptionText && $descriptionText != $displayType)
                            <div class="item-desc">{{ $descriptionText }}</div>
                        @endif
                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    </td>
                    <td class="amount-debit">
                        @if($debitAmount > 0)
                            {{ number_format($debitAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="amount-credit">
                        @if($creditAmount > 0)
                            {{ number_format($creditAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="dr-cr-cell">
                        {{ $drCrDisplay }}
                    </td>
                    <td class="balance {{ $balanceClass }}">
                        {{ $balanceDisplay }} {{ $drCrDisplay }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">STATEMENT SUMMARY</div>
        <div class="summary-row">
            <span class="summary-label">Total Debits (Sales / Outward):</span>
            <span class="summary-value" style="color: #dc3545;">PKR {{ number_format($totalDebits, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Credits (Payments / Inward):</span>
            <span class="summary-value" style="color: #28a745;">PKR {{ number_format($totalCredits, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Net Balance:</span>
            <span class="summary-value {{ $netBalance >= 0 ? 'positive-balance' : 'negative-balance' }}">
                PKR {{ number_format(abs($netBalance), 2) }} {{ $netBalance >= 0 ? 'CR' : 'DR' }}
            </span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span class="summary-value">{{ count($transactions) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>This statement contains {{ count($transactions) }} transaction(s) in chronological order.</p>
        <p><strong>{{ $companySettings->name ?? 'Food Impex' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p style="font-size: 10px; color: #999;">* This is a system-generated document. No signature required. *</p>
    </div>

    <script>
        window.onload = function() {
            // window.print();
        };
    </script>
</body>
</html>