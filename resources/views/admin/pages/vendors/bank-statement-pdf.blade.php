<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ledger Statement - {{ $customer->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
        }

        /* Header Section Styles */
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

        /* Title and Info Box Styles */
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

        .party-details {
            background-color: #d3d3d3;
            padding: 12px;
            text-align: center;
            border: 2px solid #000;
            border-top: none;
            margin-bottom: 20px;
        }

        .party-details .party-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        /* Table Design */
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

        /* Column Specific Styling */
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

        .item-desc {
            font-size: 10px;
            color: #555;
            font-style: italic;
            display: block;
            margin-top: 2px;
            border-top: 1px dotted #ccc;
        }

        .general-entry-desc {
            font-size: 10px;
            color: #2c3e50;
            display: block;
            margin-top: 2px;
            background-color: #f9e79f;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Summary Section */
        .summary-box {
            margin-top: 20px;
            padding: 10px;
            border: 2px solid #000;
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

        @media print {
            body { padding: 0; }
            .transactions-table th { -webkit-print-color-adjust: exact; }
            .general-entry-desc { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>

<body>
    @php
        /** Dynamic Logo Handling **/
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
        
        // Calculate summary totals
        $totalDebit = 0;
        $totalCredit = 0;
        foreach($customerTransactions as $transaction) {
            $type = strtolower($transaction->type);
            if($type == 'bill' || $type == 'invoice' || $type == 'general_debit') {
                $totalDebit += $transaction->amount;
            } elseif($type == 'payment' || $type == 'general_credit') {
                $totalCredit += $transaction->amount;
            }
        }
        $closingBalance = $customerTransactions->last()->current_balance ?? 0;
    @endphp

    <!-- Logo and Company Information Header -->
    <div class="header">
        <div class="logo-container">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
            @endif
        </div>
        <h1>{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</h1>
        <div style="font-size: 14px; margin-top: 5px;">
            {{ $companySettings->address ?? 'Main Road, Sialkot, Pakistan' }} | {{ $companySettings->mobile ?? '+92 300 0000000' }}
        </div>
    </div>

    <!-- Statement Title Section -->
    <div class="statement-title">
        CUSTOMER ACCOUNTS STATEMENT LEDGER
    </div>

    <!-- Customer Details Section -->
    <div class="party-details">
        <div class="party-name">CUSTOMER NAME: {{ strtoupper($customer->name) }}</div>
        <div class="address">ADDRESS: {{ strtoupper($customer->address ?? 'N/A') }}</div>
        <div style="font-size: 12px; margin-top: 5px;">PHONE: {{ $customer->phone }}</div>
    </div>

    <!-- Transactions Table -->
    <table class="transactions-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-srno">SR.<br>NO</th>
                <th rowspan="2" class="col-date">DATE</th>
                <th>DETAILS & REMARKS</th>
                <th rowspan="2" class="col-debit">DEBIT (Sales/Bill/General)</th>
                <th rowspan="2" class="col-credit">CREDIT (Payment/General)</th>
                <th rowspan="2" style="width: 50px;">DR/CR</th>
                <th rowspan="2" class="col-balance">BALANCE (PKR)</th>
             </tr>
            <tr>
                <th>DESCRIPTION</th>
             </tr>
        </thead>
        <tbody>
    @forelse($customerTransactions as $index => $transaction)
        @php
            $type = strtolower($transaction->type);
            
            /**
             * DR / CR Logic:
             * For Customers:
             * - If balance is Positive: DR (They owe us)
             * - If balance is Negative: CR (We owe them / Advance)
             **/
            $drCr = ($transaction->current_balance >= 0) ? 'DR' : 'CR';
            
            // Determine if this is a debit or credit transaction for display
            $isDebit = in_array($type, ['bill', 'invoice', 'general_debit', 'debit']);
            $isCredit = in_array($type, ['payment', 'general_credit', 'credit']);
            
            // Transaction color coding
            $rowClass = '';
            if($type == 'general_debit' || $type == 'general_credit') {
                $rowClass = 'general-entry-row';
            }
        @endphp

        <tr class="{{ $rowClass }}">
            <td style="text-align: center;">{{ $index + 1 }}</td>

            <td style="text-align: center;">
                {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') : 
                   ($transaction->date ? \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') : '-') }}
            </td>

            <td>
                {{-- BILL / INVOICE TYPE --}}
                @if (in_array($type, ['bill', 'invoice']) && $transaction->bill)
                    <strong>Sales Bill #{{ $transaction->bill->id }}</strong>
                    @if(($transaction->bill->type ?? null) === 'new bill')
                        <span style="color: #0066cc;">(New Invoice)</span>
                    @endif
                    
                    {{-- Product Detail Loop --}}
                    @if($transaction->bill->billProducts)
                        @foreach($transaction->bill->billProducts as $item)
                            <span class="item-desc">
                                • {{ $item->product->name ?? 'Product' }}
                                (Qty: {{ number_format($item->quantity, 0) }} @ PKR {{ number_format($item->unit_price, 0) }})
                            </span>
                        @endforeach
                    @endif

                {{-- PAYMENT TYPE --}}
                @elseif ($type == 'payment')
                    <strong>Payment Received</strong>
                    @if($transaction->method)
                        <span style="color: #27ae60;">({{ strtoupper($transaction->method) }})</span>
                    @endif
                    @if($transaction->reference_no)
                        <br><small>Ref #: {{ $transaction->reference_no }}</small>
                    @endif

                {{-- GENERAL ENTRY - DEBIT --}}
                @elseif ($type == 'general_debit' || $type == 'debit')
                    <strong style="color: #c0392b;">⚡ General Entry (Debit)</strong>
                    <div class="general-entry-desc">
                        <strong>Type:</strong> {{ $transaction->entry_type ?? 'Debit Entry' }}<br>
                        @if($transaction->description)
                            <strong>Details:</strong> {{ $transaction->description }}
                        @endif
                        @if($transaction->reference)
                            <br><strong>Reference:</strong> {{ $transaction->reference }}
                        @endif
                    </div>

                {{-- GENERAL ENTRY - CREDIT --}}
                @elseif ($type == 'general_credit' || $type == 'credit')
                    <strong style="color: #2980b9;">📝 General Entry (Credit)</strong>
                    <div class="general-entry-desc">
                        <strong>Type:</strong> {{ $transaction->entry_type ?? 'Credit Entry' }}<br>
                        @if($transaction->description)
                            <strong>Details:</strong> {{ $transaction->description }}
                        @endif
                        @if($transaction->reference)
                            <br><strong>Reference:</strong> {{ $transaction->reference }}
                        @endif
                    </div>

                {{-- OPENING BALANCE --}}
                @elseif ($type == 'balance' || $type == 'opening_balance')
                    <strong>Opening Balance</strong>
                    @if($transaction->description)
                        <div style="margin-top: 5px; color: #7f8c8d;">
                            {{ $transaction->description }}
                        </div>
                    @endif

                {{-- ANY OTHER TYPE --}}
                @else
                    <strong>{{ ucfirst($transaction->type) }}</strong>
                    @if($transaction->description)
                        <div style="margin-top: 5px; color: #e74c3c; font-weight: bold;">
                            {{ $transaction->description }}
                        </div>
                    @endif
                @endif

                {{-- Additional Remarks --}}
                @if($transaction->remarks && !in_array($type, ['general_debit', 'general_credit']))
                    <div style="margin-top: 5px; color: #b71c1c; font-weight: bold;">
                        Remarks: {{ $transaction->remarks }}
                    </div>
                @endif
            </td>

            {{-- DEBIT AMOUNT (Sales, Bills, General Debit) --}}
            <td class="amount-debit">
                @if($isDebit)
                    {{ number_format($transaction->amount, 0) }}
                @else
                    —
                @endif
            </td>

            {{-- CREDIT AMOUNT (Payments, General Credit) --}}
            <td class="amount-credit">
                @if($isCredit)
                    {{ number_format($transaction->amount, 0) }}
                @else
                    —
                @endif
            </td>

            {{-- DR / CR Status --}}
            <td class="dr-cr-cell">
                {{ $drCr }}
            </td>

            {{-- Running Balance --}}
            <td class="balance">
                {{ number_format(abs($transaction->current_balance), 0) }}
            </td>
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

    <!-- Summary Section -->
    <div class="summary-box">
        <div class="summary-title">Transaction Summary</div>
        <div class="summary-row">
            <span><strong>Total Debit (Sales/General Debit):</strong></span>
            <span><strong>PKR {{ number_format($totalDebit, 0) }}</strong></span>
        </div>
        <div class="summary-row">
            <span><strong>Total Credit (Payments/General Credit):</strong></span>
            <span><strong>PKR {{ number_format($totalCredit, 0) }}</strong></span>
        </div>
        <div class="summary-row">
            <span><strong>Net Balance:</strong></span>
            <span><strong>PKR {{ number_format(abs($closingBalance), 0) }} {{ $closingBalance >= 0 ? 'DR' : 'CR' }}</strong></span>
        </div>
    </div>

    <!-- Footer Summary Section -->
    <div class="footer">
        <p>This statement contains {{ $customerTransactions->count() }} transaction(s). 
        @php
            $generalCount = $customerTransactions->filter(function($t) {
                return in_array(strtolower($t->type), ['general_debit', 'general_credit', 'debit', 'credit']);
            })->count();
        @endphp
        @if($generalCount > 0)
            (Includes {{ $generalCount }} General Entr{{ $generalCount == 1 ? 'y' : 'ies' }})
        @endif
        </p>
        <p><strong>{{ $companySettings->name ?? 'Intekhab Sanitary Fittings' }}</strong> - Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <script>
        // Optional: Auto-print when page loads
        window.onload = function() {
            // window.print(); // Uncomment if you want auto-print
        };
    </script>
</body>
</html>