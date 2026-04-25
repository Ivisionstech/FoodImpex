@php
    $items = $items ?? ($quotation->items ?? []);
    if (is_string($items)) {
        $items = json_decode($items, true);
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Quotation PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 26px;
        }

        .sub-header {
            text-align: center;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        .no-border {
            border: none !important;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header" style="text-decoration: underline;">SYED FOOD IMPEX</div>
    <div class="sub-header">RANA SHOUKAT PLAZA OFFICE NO 1 MAIN GHALA MANDI KAMOKI, GUJRANWALA</div>
    <div class="section-title center" style="font-size: 26px !important; ">INVOICE/PACKINGLIST</div>
    <table>
        <tr>
            <td class="no-border" colspan="2"><b>CONSIGNEE</b><br>
                {{ $quotation->consignee_name }}<br>
                {{ $quotation->consignee_address }}
            </td>
            <td class="no-border" colspan="3">
                <b>INVOICE NO:</b> {{ $quotation->invoice_no }}<br>
                <b>DATE:</b> {{ \Carbon\Carbon::parse($quotation->invoice_date)->format('d-m-Y') }}<br>
                <b>F.I NO:</b> {{ $quotation->fi_no }}<br>
                <b>DESTINATIONS:</b>{{ $quotation->destination }}<br>
                <b>PAYMENT TERM:</b> {{ $quotation->payment_term }}<br>
                <b>FREIGHT TERM:</b> {{ $quotation->freight_term }}<br>
                {{-- <b>H.S.CODE:</b> {{ $quotation->hs_code }} --}}
            </td>

        </tr>
        <tr>
            <td colspan="5"
                style="background: #000; color: #fff; font-weight: bold; text-transform: uppercase; padding: 8px 20px 8px 12px; border: none;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 120px;">
                    <span style="margin: 0; padding: 0;">(KARACHI PAKISTAN TO
                        {{ strtoupper($quotation->destination) }})</span>
                    <span style="margin: 0; padding: 0;">H.S.CODE:{{ $quotation->hs_code }}</span>
                </div>
            </td>
        </tr>
    </table>
    <table>
        <tr style="background: #d9e7f7; font-weight: bold;">
            <td>ITEM DESCRIPTION OF GOODS QUANTITY</td>
            <td>RATE {{ $quotation->currency }}</td>
            <td>TOTAL VALUE IN {{ $quotation->currency }}<br></td>
        </tr>

        <tr>
            <td>
                <P style="text-decoration: underline; font-weight: bold;" class="p-0 m-0">1X20FCL CONTAINER SAID TO
                    CONTAIN</P>
                &nbsp; &nbsp; &nbsp; &nbsp;{{ $quotation->total_bags }} MASTER BAGS OF
                {{ number_format($quotation->total_net_weight, 2) }} M.TONS<br>
                &nbsp; &nbsp; &nbsp; &nbsp;1121 SELLA RICE <br>
                &nbsp; &nbsp; &nbsp; &nbsp;BRAND:SYED
            </td>
            <td>{{ $quotation->currency }} {{ number_format($quotation->rate_per_ton, 2) }}</td>
            <td>{{ $quotation->currency }} {{ number_format($quotation->total_value_usd, 2) }}</td>
        </tr>

    </table>
    {{-- <div class="section-title" style="font-size: 15px; text-decoration: underline; font-weight: bold;">1X20FCL CONTAINER
        SAID TO CONTAIN</div>
    <table>
        <tr>
            <td class="no-border" colspan="4">
                {{ $quotation->total_bags }} MASTER BAGS OF {{ number_format($quotation->total_net_weight, 2) }}
                M.TONS<br>
                OF {{ $quotation->description }}<br>
                BRAND: {{ $quotation->brand ?? $quotation->description }}
            </td>
        </tr>
    </table> --}}
    <table style="margin-bottom: 0; border: none;">
        <tr>
            <td class="no-border" style="font-weight: bold; font-size: 14px; text-align: center; border: none;">TOTAL
                VALUE(C&amp;F) in {{ $quotation->currency }}</td>
            <td class="no-border" style="font-weight: bold; font-size: 14px; text-align: center; border: none;">
                {{ $quotation->shipment_mode }}
            </td>
            <td class="no-border" style="font-weight: bold; font-size: 14px; text-align: center; border: none;">
                {{ $quotation->currency }}:{{ number_format($quotation->total_value_usd, 2) }}</td>
        </tr>
    </table>
    <div class="center small" style="margin-bottom: 10px;">(TOTAL VALUE C&F IN
        {{ $quotation->total_value_usd }}
        ONLY)</div>
    <table>
        <tr>
            <th>CONTAINER#</th>
            <th>NO OF BAGS</th>
            <th>PACK DETAILS</th>
            <th>Price/Bag</th>
            <th>NET WEIGHT</th>
            <th>GR WEIGHT</th>
            <th>Total Price</th>
        </tr>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item['container_no'] ?? '-' }}</td>
                <td>{{ $item['no_of_bags'] ?? '-' }}BAGS</td>
                <td>{{ $item['pack_details'] ?? ($item['package_details'] ?? '-') }}</td>
                <td>{{ $item['price'] ?? '-' }}</td>
                <td>{{ $item['net_weight'] ?? '-' }} KGS</td>
                <td>{{ $item['gross_weight'] ?? '-' }} KGS</td>
                <td>{{ $item['total_value'] ?? '-' }} {{ $quotation->currency }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="6" class="right"><b>TOTAL VALUE IN {{ $quotation->currency }}</b></td>
            <td><b>{{ $quotation->currency }} {{ number_format($quotation->total_value_usd, 2) }}</b></td>
        </tr>
    </table>
    <div class="section-title">PAYMENT TERMS</div>
    @php
        $percent = $quotation->percentage ?? 50;
        $advanceValue = $quotation->total_value_usd * ($percent / 100);
        $balanceValue = $quotation->total_value_usd - $advanceValue;
    @endphp
    <table>
        <tr>
            <td>
                {{ $percent }}% ADVANCE {{ $quotation->currency }}
                {{ number_format($advanceValue, 2) }}
            </td>
            <td>
                {{ 100 - $percent }}% BL {{ $quotation->currency }}
                {{ number_format($balanceValue, 2) }}
            </td>
        </tr>
    </table>
    <div class="section-title">BANK ACCOUNT DETAIL</div>
    <table>
        <tr>
            <td>ACCOUNT NO: {{ $quotation->bank_account }}</td>
            <td>IBAN: {{ $quotation->iban }}</td>
        </tr>
        <tr>
            <td>SWIFT CODE: {{ $quotation->swift_code }}</td>
            <td>COMPANY NAME: {{ $quotation->company_name }}</td>
        </tr>
        <tr>
            <td>BANK: {{ $quotation->bank_name }}</td>
            <td></td>
        </tr>
    </table>
    <div class="section-title">TOTAL MASTER BAGS: {{ $quotation->total_bags }} MASTER BAGS</div>
    <div class="section-title">TOTAL NET WEIGHT: {{ number_format($quotation->total_net_weight, 2) }} KGS</div>
    <div class="section-title">TOTAL GROSS WEIGHT: {{ number_format($quotation->total_gross_weight, 2) }} KGS</div>
    <div class="small">*CERTIFYING ORIGIN OF GOODS AND CONTENTS TO BE TRUE AND CORRECT MADE OF PAKISTAN ORIGIN*</div>
    <br><br>
    <div>FOR <b>Intekhabsanitary</b></div>
    <!-- Stamp image -->
    <div style="margin-top: 20px;">
        <img src="{{ public_path('images/stamp.png') }}" alt="Company Stamp"
            style="width: 25%; height: auto; object-fit: contain;">
    </div>
</body>

</html>
