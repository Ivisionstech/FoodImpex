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

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 10px;
            }
            .transactions-table th { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            .col-debit { background-color: #ffcccc !important; }
            .col-credit { background-color: #ccffcc !important; }
            .col-balance { background-color: #ffecb3 !important; }
            .badge-type, .badge-pending, .badge-approved {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
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

        /* =============================================
           HIGHLIGHTED CURRENT BALANCE
           ============================================= */
        .customer-details .balance-highlight {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            padding: 8px 16px;
            background: #fff3cd;
            border-radius: 8px;
            display: inline-block;
            border: 2px solid #ffc107;
        }
        
        .customer-details .balance-highlight .balance-label {
            font-size: 14px;
            color: #856404;
        }
        
        .customer-details .balance-highlight .balance-amount {
            font-size: 24px;
        }
        
        .customer-details .balance-highlight .balance-dr {
            color: #dc3545 !important;
            font-weight: bold;
        }
        
        .customer-details .balance-highlight .balance-cr {
            color: #28a745 !important;
            font-weight: bold;
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

        .amount-debit { color: #dc3545 !important; }
        .amount-credit { color: #28a745 !important; }

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
            padding-top: 2px;
        }

        .badge-type {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 5px;
        }

        .badge-payment { background-color: #dc3545; color: white; }
        .badge-balance { background-color: #17a2b8; color: white; }
        .badge-return { background-color: #dc3545; color: white; }
        .badge-general { background-color: #6c757d; color: white; }
        .badge-debit { background-color: #dc3545; color: white; }
        .badge-credit { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-approved { background-color: #28a745; color: white; }
        .badge-batch { background-color: #6f42c1; color: white; }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            margin: 5px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            gap: 8px;
            min-width: 100px;
        }

        .btn-print {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        }
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.5);
        }

        .btn-back {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 172, 254, 0.5);
        }

        .btn-download {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #333;
            box-shadow: 0 4px 15px rgba(67, 233, 123, 0.4);
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 233, 123, 0.5);
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }

        .action-buttons .left-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-buttons .right-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-buttons .btn {
            min-width: 120px;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            .action-buttons .left-group,
            .action-buttons .right-group {
                flex-direction: column;
                width: 100%;
            }
            .action-buttons .btn {
                width: 100%;
                margin: 5px 0;
            }
        }

        /* Balance DR/CR Badge */
        .balance-dr {
            color: #dc3545 !important;
            font-weight: bold;
        }
        .balance-cr {
            color: #28a745 !important;
            font-weight: bold;
        }
        
        .text-dr { color: #dc3545 !important; font-weight: bold; }
        .text-cr { color: #28a745 !important; font-weight: bold; }
        .badge-dr { background-color: #dc3545; color: white; }
        .badge-cr { background-color: #28a745; color: white; }
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
        
        // =============================================
        // USE transactionsWithBalance from controller
        // =============================================
        $transactions = collect($transactionsWithBalance ?? []);
        
        // Reverse for display (newest first)
        $transactionsWithBalance = $transactions->sortByDesc(function($transaction) {
            return $transaction->transaction_date;
        })->values();
        
        // Build back URL with filters
        $backUrl = route('customers.view', ['uuid' => $customer->uuid]);
        $params = [];
        if (isset($trans_from) && $trans_from) $params['trans_from'] = $trans_from;
        if (isset($trans_to) && $trans_to) $params['trans_to'] = $trans_to;
        if (!empty($params)) {
            $backUrl .= '?' . http_build_query($params);
        }
        
        // Build download URL with filters
        $downloadUrl = route('customers.bank-statement-pdf', ['uuid' => $customer->uuid]);
        if (!empty($params)) {
            $downloadUrl .= '?' . http_build_query($params);
        }
        
        // Calculate balance for highlight
        $highlightBalance = floatval($customer->balance ?? 0);
        $highlightClass = $highlightBalance < 0 ? 'balance-dr' : 'balance-cr';
        $highlightLabel = $highlightBalance < 0 ? 'DR' : 'CR';
    @endphp

    <!-- Action Buttons - Hidden when printing -->
    <div class="action-buttons no-print">
        <div class="left-group">
            <a href="{{ $backUrl }}" class="btn btn-back">
                <span class="icon">←</span> Back
            </a>
        </div>
        <div class="right-group">
            <a href="{{ $downloadUrl }}" class="btn btn-download">
                <span class="icon">⬇</span> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <span class="icon">🖨</span> Print
            </button>
        </div>
    </div>

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
        CUSTOMER LEDGER
    </div>

    <div class="customer-details">
        <div class="party-name">CUSTOMER NAME: {{ strtoupper($customer->name ?? 'N/A') }}</div>
        <div class="address">ADDRESS: {{ strtoupper($customer->address ?? 'N/A') }}</div>
        <div style="font-size: 12px; margin-top: 5px;">PHONE: {{ $customer->phone ?? 'N/A' }}</div>
        
        <!-- ============================================= -->
        <!-- HIGHLIGHTED CURRENT BALANCE -->
        <!-- ============================================= -->
        <div class="balance-highlight">
            <div class="balance-label">CURRENT BALANCE</div>
            <div class="balance-amount {{ $highlightClass }}">
                PKR {{ number_format(abs($highlightBalance), 2) }} {{ $highlightLabel }}
            </div>
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
            @forelse($transactionsWithBalance as $index => $transaction)
                @php
                    $type = $transaction->type;
                    $amount = floatval($transaction->amount);
                    $description = $transaction->description ?? '';
                    
                    // =============================================
                    // FIXED: Use running_balance from controller
                    // =============================================
                    $currentBalance = isset($transaction->running_balance) ? floatval($transaction->running_balance) : 0;
                    
                    $debitAmount = 0;
                    $creditAmount = 0;
                    $displayType = '';
                    $badgeClass = '';
                    $badgeText = '';
                    $descriptionText = '';
                    $drCrType = '';
                    
                    // =============================================
                    // DR/CR LOGIC
                    // =============================================
                    if ($type == 'bill' || $type == 'debit') {
                        $debitAmount = $amount;
                        $displayType = $type == 'bill' ? 'Sales Invoice' : 'Debit Entry';
                        $badgeClass = 'badge-debit';
                        $badgeText = $type == 'bill' ? 'SALES' : 'DEBIT';
                        $drCrType = 'DR';
                        $descriptionText = $description ?: ($type == 'bill' ? 'Sales invoice' : 'Debit transaction');
                    } elseif ($type == 'payment' || $type == 'credit') {
                        $creditAmount = $amount;
                        $displayType = $type == 'payment' ? 'Payment Received' : 'Credit Entry';
                        $badgeClass = 'badge-credit';
                        $badgeText = $type == 'payment' ? 'PAYMENT' : 'CREDIT';
                        $drCrType = 'CR';
                        $descriptionText = $description ?: ($type == 'payment' ? 'Payment received' : 'Credit transaction');
                    } elseif ($type == 'balance') {
                        // Opening Balance
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $drCrType = 'CR';
                            $displayType = 'Opening Balance (Credit)';
                            $badgeClass = 'badge-credit';
                            $badgeText = 'OPENING CR';
                        } else {
                            $debitAmount = abs($amount);
                            $drCrType = 'DR';
                            $displayType = 'Opening Balance (Debit)';
                            $badgeClass = 'badge-debit';
                            $badgeText = 'OPENING DR';
                        }
                        $descriptionText = $description ?: 'Initial opening balance';
                    } else {
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $drCrType = 'CR';
                            $displayType = 'Credit Entry';
                            $badgeClass = 'badge-credit';
                            $badgeText = 'CREDIT';
                        } else {
                            $debitAmount = abs($amount);
                            $drCrType = 'DR';
                            $displayType = 'Debit Entry';
                            $badgeClass = 'badge-debit';
                            $badgeText = 'DEBIT';
                        }
                        $descriptionText = $description ?: ucfirst($type);
                    }
                    
                    // =============================================
                    // CURRENT BALANCE - Negative = DR, Positive = CR
                    // =============================================
                    $drCrDisplay = $currentBalance < 0 ? 'DR' : 'CR';
                    $balanceClass = $currentBalance < 0 ? 'balance-dr' : 'balance-cr';
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
                        <span class="badge-type {{ $badgeClass }}">{{ $badgeText }}</span>
                        <strong>{{ $displayType }}</strong>
                        @if($descriptionText && $descriptionText != $displayType)
                            <div class="item-desc">{{ $descriptionText }}</div>
                        @endif
                        <br>
                        <span class="badge badge-approved" style="font-size: 8px; padding: 2px 6px; background-color: #28a745; color: white;">Approved</span>
                    </td>
                    <!-- DEBIT Column -->
                    <td class="amount-debit">
                        @if($debitAmount > 0)
                            {{ number_format($debitAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <!-- CREDIT Column -->
                    <td class="amount-credit">
                        @if($creditAmount > 0)
                            {{ number_format($creditAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <!-- DR/CR Column -->
                    <td class="dr-cr-cell {{ $drCrType == 'DR' ? 'text-danger' : 'text-success' }}">
                        {{ $drCrType }}
                    </td>
                    <!-- BALANCE Column - Shows the account balance after this transaction -->
                    <td class="balance {{ $balanceClass }}">
                        {{ number_format(abs($currentBalance), 2) }} {{ $drCrDisplay }}
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
        <p>This ledger contains {{ count($transactionsWithBalance) }} transaction(s) in chronological order.</p>
        <p><strong>{{ $companySettings->name ?? 'Food Impex' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p style="font-size: 10px; color: #999;">* This is a system-generated document. No signature required. *</p>
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                window.print();
            }
        };
    </script>
</body>
</html>