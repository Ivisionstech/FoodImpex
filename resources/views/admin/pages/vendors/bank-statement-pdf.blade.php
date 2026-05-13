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

        .badge-type {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 5px;
        }

        .badge-bill { background-color: #dc3545; color: white; }
        .badge-payment { background-color: #28a745; color: white; }
        .badge-balance { background-color: #17a2b8; color: white; }
        .badge-return { background-color: #ffc107; color: #333; }
        .badge-debit { background-color: #dc3545; color: white; }
        .badge-credit { background-color: #28a745; color: white; }

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

        .text-danger { color: #dc3545; }
        .text-success { color: #28a745; }
        .text-info { color: #17a2b8; }

        @media print {
            body { padding: 0; }
            .transactions-table th { -webkit-print-color-adjust: exact; }
            .summary-section { -webkit-print-color-adjust: exact; }
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
        
        // Sort transactions in ascending order by date
        $sortedTransactions = $vendorTransactions->sortBy(function($transaction) {
            return $transaction->date ? \Carbon\Carbon::parse($transaction->date) : \Carbon\Carbon::minValue();
        });
        
        // Calculate summary totals
        $totalBills = 0;
        $totalPayments = 0;
        $totalDebits = 0;
        $totalCredits = 0;
        
        foreach($sortedTransactions as $transaction) {
            // For bill transactions
            if($transaction->type == 'bill') {
                $totalBills += $transaction->amount;
                $totalCredits += $transaction->amount;
            }
            // For payment transactions
            elseif($transaction->type == 'payment') {
                $totalPayments += $transaction->amount;
                $totalDebits += $transaction->amount;
            }
            // For general entries with transaction_type
            elseif(isset($transaction->transaction_type)) {
                if($transaction->transaction_type == 'debit') {
                    $totalDebits += $transaction->amount;
                } elseif($transaction->transaction_type == 'credit') {
                    $totalCredits += $transaction->amount;
                }
            }
        }
        
        $netBalance = $totalCredits - $totalDebits;
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
        <div class="current-balance" style="margin-top: 8px; font-size: 14px;">
            Current Balance: 
            <strong style="color: {{ $vendor->balance < 0 ? '#dc3545' : '#28a745' }}">
                PKR {{ number_format(abs($vendor->balance), 2) }}{{ $vendor->balance < 0 ? ' DR' : ' CR' }}
            </strong>
        </div>
    </div>

    <table class="transactions-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-srno">SR.<br>NO</th>
                <th rowspan="2" class="col-date">DATE</th>
                <th>DETAILS</th>
                <th rowspan="2" class="col-debit">DEBIT<br><span style="font-size: 9px;">(Payment/Out)</span></th>
                <th rowspan="2" class="col-credit">CREDIT<br><span style="font-size: 9px;">(Purchase/In)</span></th>
                <th rowspan="2" style="width: 50px;">DR/CR</th>
                <th rowspan="2" class="col-balance">BALANCE (PKR)</th>
            </tr>
            <tr>
                <th>DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sortedTransactions as $index => $transaction)
                @php
                    // Determine transaction details
                    $debitAmount = 0;
                    $creditAmount = 0;
                    $transactionTypeText = '';
                    $badgeClass = '';
                    $badgeText = '';
                    $descriptionText = '';
                    
                    // Check for regular transaction type (bill, payment, return, balance)
                    if ($transaction->type == 'bill') {
                        $creditAmount = $transaction->amount;
                        $transactionTypeText = 'Bill';
                        $badgeClass = 'badge-bill';
                        $badgeText = 'BILL';
                        $descriptionText = 'Purchase Bill #' . ($transaction->bill->id ?? 'N/A');
                        if($transaction->description) {
                            $descriptionText .= ' - ' . $transaction->description;
                        }
                    } 
                    elseif ($transaction->type == 'payment') {
                        $debitAmount = $transaction->amount;
                        $transactionTypeText = 'Payment';
                        $badgeClass = 'badge-payment';
                        $badgeText = 'PAYMENT';
                        $descriptionText = 'Payment Sent';
                        if($transaction->send_via) {
                            $descriptionText .= ' via ' . ucfirst($transaction->send_via);
                        }
                        if($transaction->description) {
                            $descriptionText .= ' - ' . $transaction->description;
                        }
                    } 
                    elseif ($transaction->type == 'return') {
                        // Return - Credit amount (money coming back to vendor)
                        $creditAmount = $transaction->amount;
                        $transactionTypeText = 'Return';
                        $badgeClass = 'badge-return';
                        $badgeText = 'RETURN';
                        $descriptionText = 'Product Return';
                        if($transaction->description) {
                            $descriptionText .= ' - ' . $transaction->description;
                        }
                    } 
                    elseif ($transaction->type == 'balance') {
                        // Opening balance - show as credit if positive, debit if negative
                        if($transaction->amount > 0) {
                            $creditAmount = $transaction->amount;
                        } else {
                            $debitAmount = abs($transaction->amount);
                        }
                        $transactionTypeText = 'Opening Balance';
                        $badgeClass = 'badge-balance';
                        $badgeText = 'OPENING';
                        $descriptionText = 'Opening Balance';
                        if($transaction->description) {
                            $descriptionText .= ' - ' . $transaction->description;
                        }
                    }
                    // Check for general entry transactions (transaction_type field)
                    elseif (isset($transaction->transaction_type)) {
                        if ($transaction->transaction_type == 'debit') {
                            $debitAmount = $transaction->amount;
                            $transactionTypeText = 'Debit Entry';
                            $badgeClass = 'badge-debit';
                            $badgeText = 'DEBIT';
                            $descriptionText = 'General Transaction (Debit)';
                        } elseif ($transaction->transaction_type == 'credit') {
                            $creditAmount = $transaction->amount;
                            $transactionTypeText = 'Credit Entry';
                            $badgeClass = 'badge-credit';
                            $badgeText = 'CREDIT';
                            $descriptionText = 'General Transaction (Credit)';
                        }
                        if($transaction->description) {
                            $descriptionText .= ' - ' . $transaction->description;
                        }
                    }
                    // Fallback for any other type
                    else {
                        $creditAmount = $transaction->amount;
                        $transactionTypeText = ucfirst($transaction->type);
                        $badgeClass = 'badge-balance';
                        $badgeText = strtoupper($transaction->type);
                        $descriptionText = $transaction->description ?? $transactionTypeText;
                    }
                    
                    // Get the current balance for this transaction
                    $currentBalance = isset($transaction->current_balance) ? floatval($transaction->current_balance) : 0;
                    $balanceDisplay = number_format(abs($currentBalance), 2);
                    $drCrDisplay = $currentBalance < 0 ? 'DR' : 'CR';
                    
                    // Format balance with DR/CR suffix for negative balances
                    if ($currentBalance < 0) {
                        $finalBalanceDisplay = $balanceDisplay . ' DR';
                    } else {
                        $finalBalanceDisplay = $balanceDisplay . ' CR';
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        {{ $transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-' }}
                        @if(isset($transaction->created_at) && $transaction->date != $transaction->created_at)
                            <div style="font-size: 9px; color: #666;">
                                {{ \Carbon\Carbon::parse($transaction->date)->format('h:i A') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge-type {{ $badgeClass }}">{{ $badgeText }}</span>
                        <strong>{{ $descriptionText }}</strong>
                        @if(isset($transaction->bill) && $transaction->bill && $transaction->bill->date)
                            <div class="item-desc">Bill Date: {{ \Carbon\Carbon::parse($transaction->bill->date)->format('d-m-Y') }}</div>
                        @endif
                        @if(!empty($transaction->notes))
                            <div class="item-desc">{{ $transaction->notes }}</div>
                        @endif
                    </td>
                    <!-- DEBIT Column - Shows money going OUT (Payments, Debits) -->
                    <td class="amount-debit">
                        @if($debitAmount > 0)
                            {{ number_format($debitAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <!-- CREDIT Column - Shows money coming IN (Bills, Credits) -->
                    <td class="amount-credit">
                        @if($creditAmount > 0)
                            {{ number_format($creditAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <!-- DR/CR Column - Shows current balance status -->
                    <td class="dr-cr-cell">
                        {{ $drCrDisplay }}
                    </td>
                    <!-- BALANCE Column - Shows current balance with DR/CR suffix -->
                    <td class="balance">
                        {{ $finalBalanceDisplay }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        No transactions found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">STATEMENT SUMMARY</div>
        <div class="summary-row">
            <span class="summary-label">Total Purchases (Credit / Bills / Inward):</span>
            <span class="summary-value text-success">PKR {{ number_format($totalCredits, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Payments (Debit / Outward):</span>
            <span class="summary-value text-danger">PKR {{ number_format($totalDebits, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Net Balance:</span>
            <span class="summary-value {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">
                PKR {{ number_format(abs($netBalance), 2) }} {{ $netBalance >= 0 ? 'CR' : 'DR' }}
            </span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span class="summary-value">{{ $sortedTransactions->count() }}</span>
        </div>
    </div>

    <div class="footer">
        <p>This statement contains {{ $sortedTransactions->count() }} transaction(s) in chronological order.</p>
        <p><strong>{{ $companySettings->name ?? '' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p style="font-size: 10px; color: #999;">* This is a system-generated document. No signature required. *</p>
    </div>

    <script>
        window.onload = function() {
            // Uncomment to auto-print when PDF opens
            // window.print();
        };
    </script>
</body>
</html>