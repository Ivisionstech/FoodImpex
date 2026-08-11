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
        }

        .badge-type {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 5px;
        }

        .badge-bill { background-color: #28a745; color: white; }
        .badge-payment { background-color: #dc3545; color: white; }
        .badge-balance { background-color: #17a2b8; color: white; }
        .badge-return { background-color: #dc3545; color: white; }
        .badge-general { background-color: #6c757d; color: white; }
        .badge-debit { background-color: #dc3545; color: white; }
        .badge-credit { background-color: #28a745; color: white; }
        .badge-pending { background-color: #ffc107; color: #333; }
        .badge-approved { background-color: #28a745; color: white; }

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
        .text-warning { color: #ffc107; }

        @media print {
            body { padding: 0; }
            .transactions-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .col-debit { background-color: #ffcccc !important; }
            .col-credit { background-color: #ccffcc !important; }
            .col-balance { background-color: #ffecb3 !important; }
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
        
        $netBalance = ($totalCredits ?? 0) - ($totalDebits ?? 0);
        
        // Calculate approved and pending counts
        $approvedCount = 0;
        $pendingCount = 0;
        foreach ($vendorTransactions as $transaction) {
            if (isset($transaction->is_approved) && $transaction->is_approved) {
                $approvedCount++;
            } else {
                $pendingCount++;
            }
        }
        
        // Filter info
        $filterInfo = '';
        if (isset($trans_from) && isset($trans_to) && $trans_from && $trans_to) {
            $filterInfo = 'Period: ' . \Carbon\Carbon::parse($trans_from)->format('d-m-Y') . ' to ' . \Carbon\Carbon::parse($trans_to)->format('d-m-Y');
        } elseif (isset($trans_from) && $trans_from) {
            $filterInfo = 'From: ' . \Carbon\Carbon::parse($trans_from)->format('d-m-Y');
        } elseif (isset($trans_to) && $trans_to) {
            $filterInfo = 'To: ' . \Carbon\Carbon::parse($trans_to)->format('d-m-Y');
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
        <div style="font-size: 12px; margin-top: 5px;">PHONE: {{ $vendor->phone ?? 'N/A' }}</div>
        @if($filterInfo)
            <div style="font-size: 12px; margin-top: 5px; color: #666;">{{ $filterInfo }}</div>
        @endif
        <div style="font-size: 13px; margin-top: 8px; font-weight: bold;">
            Current Balance: 
            <span style="color: {{ ($vendor->balance ?? 0) < 0 ? '#28a745' : '#dc3545' }}">
                PKR {{ number_format(abs($vendor->balance ?? 0), 2) }} {{ ($vendor->balance ?? 0) < 0 ? 'CR' : 'DR' }}
            </span>
            <span style="font-size: 10px; color: #666;">(Approved Only)</span>
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
            @forelse($vendorTransactions as $index => $transaction)
                @php
                    $type = strtolower($transaction->type ?? '');
                    $transactionType = $transaction->transaction_type ?? '';
                    $amount = floatval($transaction->amount ?? 0);
                    $description = $transaction->description ?? '';
                    $approvalStatus = $transaction->approval_status ?? 'pending';
                    $isApproved = ($approvalStatus == 'approved');
                    
                    // =============================================
                    // CURRENT BALANCE = calculated_balance from controller
                    // =============================================
                    $currentBalance = isset($transaction->calculated_balance) ? $transaction->calculated_balance : 0;
                    
                    $debitAmount = 0;
                    $creditAmount = 0;
                    $displayType = '';
                    $badgeClass = '';
                    $badgeText = '';
                    $descriptionText = '';
                    $drCrType = '';
                    $isGeneralEntry = false;
                    $isOpeningBalance = false;
                    $statusBadge = '';
                    $statusText = '';
                    
                    // Determine if Opening Balance or General Entry
                    if ($type == 'balance') {
                        if (stripos($description, 'Opening Balance') !== false) {
                            $isOpeningBalance = true;
                        } else {
                            $isGeneralEntry = true;
                        }
                    } elseif ($type == 'general' || $type == 'transaction' || $type == 'daybook' || $type == '') {
                        $isGeneralEntry = true;
                    }
                    
                    // Status Badge
                    if ($isApproved) {
                        $statusBadge = 'badge-approved';
                        $statusText = 'Approved';
                    } else {
                        $statusBadge = 'badge-pending';
                        $statusText = 'Pending';
                    }
                    
                    // =============================================
                    // DR/CR LOGIC FOR VENDOR
                    // =============================================
                    if ($type == 'bill') {
                        // Bill = CR (Money IN)
                        $creditAmount = $amount;
                        $displayType = 'Purchase Bill';
                        $badgeClass = 'badge-bill';
                        $badgeText = 'BILL';
                        $drCrType = 'CR';
                        $descriptionText = $description ?: 'Purchase from vendor';
                        if (isset($transaction->bill) && $transaction->bill) {
                            $descriptionText = 'Bill #' . $transaction->bill->id;
                        }
                    } elseif ($type == 'payment') {
                        // Payment = DR (Money OUT)
                        $debitAmount = $amount;
                        $displayType = 'Payment Sent';
                        $badgeClass = 'badge-payment';
                        $badgeText = 'PAYMENT';
                        $drCrType = 'DR';
                        $descriptionText = $description ?: 'Payment to vendor';
                        if (isset($transaction->send_via) && $transaction->send_via) {
                            $descriptionText .= ' via ' . ucfirst($transaction->send_via);
                        }
                    } elseif ($isOpeningBalance) {
                        // Opening Balance
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $drCrType = 'CR';
                        } else {
                            $debitAmount = abs($amount);
                            $drCrType = 'DR';
                        }
                        $displayType = 'Opening Balance';
                        $badgeClass = 'badge-balance';
                        $badgeText = 'OPENING';
                        $descriptionText = 'Opening Balance';
                    } elseif ($isGeneralEntry) {
                        // General Entry
                        $displayType = 'General Entry';
                        $badgeClass = 'badge-general';
                        $badgeText = 'GENERAL';
                        
                        if ($transactionType == 'credit') {
                            $creditAmount = $amount;
                            $drCrType = 'CR';
                        } elseif ($transactionType == 'debit') {
                            $debitAmount = $amount;
                            $drCrType = 'DR';
                        } else {
                            if ($amount > 0) {
                                $creditAmount = $amount;
                                $drCrType = 'CR';
                            } else {
                                $debitAmount = abs($amount);
                                $drCrType = 'DR';
                            }
                        }
                        $descriptionText = $description ?: 'General Entry';
                    } elseif ($type == 'return') {
                        $debitAmount = $amount;
                        $displayType = 'Return';
                        $badgeClass = 'badge-return';
                        $badgeText = 'RETURN';
                        $drCrType = 'DR';
                        $descriptionText = $description ?: 'Product return';
                    } elseif ($type == 'credit') {
                        $creditAmount = $amount;
                        $displayType = 'Credit Entry';
                        $badgeClass = 'badge-credit';
                        $badgeText = 'CREDIT';
                        $drCrType = 'CR';
                        $descriptionText = $description ?: 'Credit transaction';
                    } elseif ($type == 'debit') {
                        $debitAmount = $amount;
                        $displayType = 'Debit Entry';
                        $badgeClass = 'badge-debit';
                        $badgeText = 'DEBIT';
                        $drCrType = 'DR';
                        $descriptionText = $description ?: 'Debit transaction';
                    } else {
                        if ($amount > 0) {
                            $creditAmount = $amount;
                            $drCrType = 'CR';
                        } else {
                            $debitAmount = abs($amount);
                            $drCrType = 'DR';
                        }
                        $displayType = ucfirst($type) ?: 'Entry';
                        $badgeClass = 'badge-general';
                        $badgeText = strtoupper($type) ?: 'ENTRY';
                        $descriptionText = $description ?: $displayType;
                    }
                    
                    // Current Balance DR/CR
                    $drCrDisplay = $currentBalance >= 0 ? 'DR' : 'CR';
                    $balanceClass = $currentBalance < 0 ? 'text-success' : 'text-danger';
                    
                    // If pending, show warning
                    if (!$isApproved) {
                        $balanceClass = 'text-warning';
                    }
                    
                    $transactionDate = $transaction->date ?? $transaction->created_at ?? now();
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
                        @if(isset($transaction->send_via) && $transaction->send_via)
                            <span style="font-size: 9px; color: #666;">via {{ ucfirst($transaction->send_via) }}</span>
                        @endif
                        <br>
                        <span class="badge {{ $statusBadge }}" style="font-size: 8px; padding: 2px 6px;">{{ $statusText }}</span>
                        @if(!$isApproved)
                            <span style="font-size: 8px; color: #ffc107; margin-left: 5px;">(Not affecting balance)</span>
                        @endif
                    </td>
                    <!-- DEBIT Column - Money OUT -->
                    <td class="amount-debit">
                        @if($debitAmount > 0)
                            {{ number_format($debitAmount, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <!-- CREDIT Column - Money IN -->
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
                    <!-- BALANCE Column - Shows Vendor Account Balance -->
                    <td class="balance {{ $balanceClass }}">
                        {{ number_format(abs($currentBalance), 2) }} {{ $drCrDisplay }}
                        @if(!$isApproved)
                            <br><span style="font-size: 8px; color: #ffc107;">(Pending)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">
                        No transactions found for this vendor.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-title">STATEMENT SUMMARY</div>
        <div class="summary-row">
            <span class="summary-label">Total Debits (Payment/Outward):</span>
            <span class="summary-value text-danger">PKR {{ number_format($totalDebits ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Credits (Purchase/Inward):</span>
            <span class="summary-value text-success">PKR {{ number_format($totalCredits ?? 0, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Net Balance:</span>
            <span class="summary-value {{ $netBalance >= 0 ? 'text-danger' : 'text-success' }}">
                PKR {{ number_format(abs($netBalance), 2) }} {{ $netBalance >= 0 ? 'DR' : 'CR' }}
            </span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Transactions:</span>
            <span class="summary-value">{{ count($vendorTransactions) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Approved Transactions:</span>
            <span class="summary-value text-success">{{ $approvedCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Pending Transactions:</span>
            <span class="summary-value text-warning">{{ $pendingCount }}</span>
        </div>
    </div>

    <div class="footer">
        <p>This statement contains {{ count($vendorTransactions) }} transaction(s) in chronological order.</p>
        <p><strong>{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
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