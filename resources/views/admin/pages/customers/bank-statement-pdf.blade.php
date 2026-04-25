<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Statement - {{ $customer->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
        }
        
        .statement-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .action-buttons {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .action-buttons button, 
        .action-buttons a {
            padding: 10px 20px;
            margin-left: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-print {
            background: #4CAF50;
            color: white;
        }
        
        .btn-pdf {
            background: #2196F3;
            color: white;
        }
        
        .btn-back {
            background: #9E9E9E;
            color: white;
        }
        
        .statement-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        
        .logo-img {
            max-width: 250px;
            max-height: 150px;
            object-fit: contain;
        }
        
        .company-info {
            font-size: 14px;
            margin-top: 5px;
            color: #666;
        }
        
        .statement-title {
            background-color: #d3d3d3;
            text-align: center;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
            margin-bottom: 0;
        }
        
        .party-details {
            background-color: #d3d3d3;
            padding: 12px;
            text-align: center;
            border: 1px solid #000;
            border-top: none;
            margin-bottom: 20px;
        }
        
        .party-details .party-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 1px solid #000;
        }
        
        .transactions-table th {
            padding: 10px 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 13px;
            background-color: #c8b4e8;
        }
        
        .transactions-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            font-size: 12px;
            vertical-align: top;
        }
        
        .amount-debit, .amount-credit, .balance {
            text-align: right !important;
            font-weight: bold;
        }
        
        .dr-cr-cell {
            text-align: center !important;
            font-weight: bold;
            background-color: #e3f2fd;
        }
        
        .payment-desc {
            font-size: 10px;
            color: #555;
            display: block;
            margin-top: 2px;
            padding: 2px 4px;
        }
        
        .general-entry-desc {
            font-size: 10px;
            color: #2c3e50;
            display: block;
            margin-top: 2px;
            padding: 2px 4px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .summary-box {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            background-color: #f8f9fa;
        }
        
        .summary-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        /* Hide action buttons when printing */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .action-buttons {
                display: none;
            }
            .statement-container {
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="statement-wrapper">
        <!-- Action buttons - Visible on screen, hidden when printing/PDF -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn-print">
                Print
            </button>
            <a href="{{ url('customer-statement-pdf/' . $customer->uuid) }}?{{ http_build_query(request()->all()) }}" class="btn-pdf" target="_blank">
                Download PDF
            </a>
            <a href="{{ url('customers/view/' . $customer->uuid) }}" class="btn-back">
                ← Back to Customer
            </a>
        </div>
        
        <div class="statement-container">
            @php
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
                
                $totalDebit = 0;
                $totalCredit = 0;
                $generalEntriesCount = 0;
                
                if(isset($customerTransactions) && count($customerTransactions) > 0) {
                    foreach($customerTransactions as $transaction) {
                        $type = isset($transaction->type) ? strtolower($transaction->type) : '';
                        
                        if(in_array($type, ['bill', 'general_debit', 'debit'])) {
                            $totalDebit += $transaction->amount ?? 0;
                        } elseif(in_array($type, ['payment', 'general_credit', 'credit'])) {
                            $totalCredit += $transaction->amount ?? 0;
                        }
                        
                        if(in_array($type, ['general_debit', 'general_credit', 'debit', 'credit'])) {
                            $generalEntriesCount++;
                        }
                    }
                    $closingBalance = isset($customerTransactions->last()->current_balance) ? $customerTransactions->last()->current_balance : 0;
                    $openingBalance = isset($customerTransactions->first()->current_balance) ? $customerTransactions->first()->current_balance : 0;
                } else {
                    $closingBalance = 0;
                    $openingBalance = 0;
                }
            @endphp

            <div class="header">
                <div class="logo-container">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
                    @endif
                </div>
                <div class="company-info">
                    {{ $companySettings->address ?? 'Main Road, Sialkot, Pakistan' }} | {{ $companySettings->mobile ?? '+92 300 0000000' }}
                </div>
            </div>

            <div class="statement-title">
                CUSTOMER ACCOUNTS STATEMENT LEDGER
            </div>

            <div class="party-details">
                <div class="party-name">CUSTOMER NAME: {{ strtoupper($customer->name) }}</div>
                <div class="address">ADDRESS: {{ strtoupper($customer->address ?? 'N/A') }}</div>
                <div style="font-size: 12px; margin-top: 5px;">PHONE: {{ $customer->phone }}</div>
                @if(isset($fromDate) && isset($toDate) && $fromDate && $toDate)
                    <div style="margin-top: 10px;">
                        Period: {{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}
                    </div>
                @endif
            </div>

            <table class="transactions-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 40px;">SR.<br>NO</th>
                        <th rowspan="2" style="width: 90px;">DATE</th>
                        <th>DETAILS & REMARKS</th>
                        <th rowspan="2" style="width: 110px;">DEBIT</th>
                        <th rowspan="2" style="width: 110px;">CREDIT</th>
                        <th rowspan="2" style="width: 50px;">DR/CR</th>
                        <th rowspan="2" style="width: 130px;">BALANCE (PKR)</th>
                    </tr>
                    <tr>
                        <th>DESCRIPTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerTransactions as $index => $transaction)
                        @php
                            $type = isset($transaction->type) ? strtolower($transaction->type) : '';
                            $drCr = (isset($transaction->current_balance) && $transaction->current_balance >= 0) ? 'DR' : 'CR';
                            $isDebit = in_array($type, ['bill', 'invoice', 'general_debit', 'debit']);
                            $isCredit = in_array($type, ['payment', 'general_credit', 'credit']);
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td style="text-align: center;">
                                {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td>
                                @if (in_array($type, ['bill', 'invoice']) && $transaction->bill)
                                    <strong>Sales Bill #{{ $transaction->bill->id }}</strong>

                                @elseif ($type == 'payment')
                                    <strong>Payment Received</strong>
                                    @if($transaction->description)
                                        <div class="payment-desc">
                                            {{ $transaction->description }}
                                        </div>
                                    @endif
                                    @if(isset($transaction->method) && $transaction->method)
                                        <div class="payment-desc">
                                            Via: {{ ucfirst($transaction->method) }}
                                        </div>
                                    @endif

                                @elseif ($type == 'general_debit' || $type == 'debit')
                                    <strong>⚡ General Entry (Debit)</strong>
                                    <div class="general-entry-desc">
                                        @if($transaction->description)
                                            {{ $transaction->description }}
                                        @endif
                                        @if(isset($transaction->reference) && $transaction->reference)
                                            <br>Ref: {{ $transaction->reference }}
                                        @endif
                                    </div>

                                @elseif ($type == 'general_credit' || $type == 'credit')
                                    <strong>General Entry (Credit)</strong>
                                    <div class="general-entry-desc">
                                        @if($transaction->description)
                                            {{ $transaction->description }}
                                        @endif
                                        @if(isset($transaction->reference) && $transaction->reference)
                                            <br>Ref: {{ $transaction->reference }}
                                        @endif
                                    </div>

                                @elseif ($type == 'balance')
                                    <strong>Opening Balance</strong>
                                    @if($transaction->description)
                                        <div style="margin-top: 5px; color: #7f8c8d;">
                                            {{ $transaction->description }}
                                        </div>
                                    @endif

                                @else
                                    <strong>{{ ucfirst($transaction->type) }}</strong>
                                    @if($transaction->description)
                                        <div style="margin-top: 5px; color: #7f8c8d;">
                                            {{ $transaction->description }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="amount-debit">
                                @if($isDebit)
                                    {{ number_format($transaction->amount, 0) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="amount-credit">
                                @if($isCredit)
                                    {{ number_format($transaction->amount, 0) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="dr-cr-cell">{{ $drCr }}</td>
                            <td class="balance">{{ number_format(abs($transaction->current_balance), 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No transactions found for this customer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="summary-box">
                <div class="summary-title">Transaction Summary</div>
                <div class="summary-row">
                    <span><strong>Opening Balance:</strong></span>
                    <span><strong>PKR {{ number_format(abs($openingBalance), 0) }} {{ $openingBalance >= 0 ? 'DR' : 'CR' }}</strong></span>
                </div>
                <div class="summary-row">
                    <span><strong>Total Debit (Sales + General Debit):</strong></span>
                    <span><strong>PKR {{ number_format($totalDebit, 0) }}</strong></span>
                </div>
                <div class="summary-row">
                    <span><strong>Total Credit (Payments + General Credit):</strong></span>
                    <span><strong>PKR {{ number_format($totalCredit, 0) }}</strong></span>
                </div>
                <div class="summary-row">
                    <span><strong>Closing Balance:</strong></span>
                    <span><strong>PKR {{ number_format(abs($closingBalance), 0) }} {{ $closingBalance >= 0 ? 'DR' : 'CR' }}</strong></span>
                </div>
            </div>

            <div class="footer">
                <p>This statement contains {{ $customerTransactions->count() }} transaction(s).
                @if($generalEntriesCount > 0)
                    (Includes {{ $generalEntriesCount }} General Entr{{ $generalEntriesCount == 1 ? 'y' : 'ies' }})
                @endif
                </p>
                <p><strong>{{ $companySettings->name ?? 'Food Impex' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Auto-trigger print when PDF is generated (for PDF download) -->
    @if(isset($autoPrint) && $autoPrint)
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif
</body>
</html>