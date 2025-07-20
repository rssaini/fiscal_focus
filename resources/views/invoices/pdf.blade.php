<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice - {{ $sale->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
        }

        .invoice-container {
            width: 100%;
            border: 2px solid #000;
            padding: 0;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 8px;
            background-color: #f8f9fa;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 10px;
            margin-bottom: 3px;
        }

        .upi-id {
            font-size: 10px;
            font-weight: bold;
        }

        .invoice-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 5px;
            border-bottom: 1px solid #000;
            background-color: #e9ecef;
        }

        .invoice-details {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }

        .invoice-left, .invoice-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        .invoice-right {
            border-left: 1px solid #000;
        }

        .detail-row {
            margin-bottom: 3px;
        }

        .detail-label {
            display: inline-block;
            width: 140px;
            font-weight: bold;
        }

        .billing-section {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }

        .bill-to, .ship-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        .ship-to {
            border-left: 1px solid #000;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
        }

        .address-block {
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #000;
        }

        .products-table th,
        .products-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .products-table .text-left {
            text-align: left;
        }

        .products-table .text-right {
            text-align: right;
        }

        .tax-section {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }

        .tax-left, .tax-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        .tax-right {
            border-left: 1px solid #000;
        }

        .tax-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tax-table th,
        .tax-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            font-size: 9px;
        }

        .tax-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .amount-summary {
            text-align: right;
            padding: 5px 8px;
        }

        .amount-row {
            margin-bottom: 2px;
        }

        .amount-label {
            display: inline-block;
            width: 120px;
            text-align: left;
        }

        .amount-value {
            display: inline-block;
            width: 80px;
            text-align: right;
        }

        .total-amount {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .amount-words {
            padding: 8px;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .footer-section {
            display: table;
            width: 100%;
        }

        .bank-details, .payment-details {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 8px;
        }

        .payment-details {
            border-left: 1px solid #000;
            text-align: center;
        }

        .bank-row {
            margin-bottom: 3px;
        }

        .bank-label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            margin: 10px auto;
            display: block;
        }

        .signature-area {
            text-align: right;
            margin-top: 20px;
            font-weight: bold;
        }

        .original-copy {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 8px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="original-copy">Original for Recipient</div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-address">{{ $company['address'] }}</div>
            <div class="upi-id">UPI ID: {{ $company['upi_id'] }}</div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">TAX INVOICE</div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-left">
                <div class="detail-row">
                    <span class="detail-label">INVOICE NO :</span>
                    <span>{{ $sale->invoice_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">INVOICE DATE</span>
                    <span>{{ $sale->date->format('d-M-Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">STATE</span>
                    <span>{{ $company['state'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">GSTN/UIN :</span>
                    <span>{{ $company['gstn'] }}</span>
                </div>
            </div>
            <div class="invoice-right">
                <div class="detail-row">
                    <span class="detail-label">Purchase Order No.</span>
                    <span>-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Vehicle No</span>
                    <span>{{ $sale->vehicle_no }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Purchase Order No.</span>
                    <span>-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">E-way Bill No.</span>
                    <span>-</span>
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                @if($sale->customer)
                    <div class="address-block">{{ strtoupper($sale->customer->name) }}</div>
                    @if($sale->customer->billing_address)
                        <div class="address-block">{{ $sale->customer->billing_address }}</div>
                    @endif
                    @if($sale->customer->city && $sale->customer->state && $sale->customer->pincode)
                        <div class="address-block">{{ $sale->customer->city }}, {{ $sale->customer->state }}, {{ $sale->customer->pincode }}</div>
                    @endif
                @else
                    <div class="address-block">KHEWAT NO 155, KILLA 61//22/2/2, 82//2/2, VILLAGE</div>
                    <div class="address-block">BAMNOLI, Bahadurgarh, Jhajjar, Haryana, 124507</div>
                @endif
                <div class="detail-row" style="margin-top: 10px;">
                    <span class="detail-label">GSTN/UIN</span>
                    <span>{{ $sale->customer->gstin ?? '06DTVPA7505K1ZB' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Place Of Supply</span>
                    <span>{{ $customer_state }}</span>
                </div>
            </div>
            <div class="ship-to">
                <div class="section-title">Ship To</div>
                @if($sale->consignee_name && $sale->consignee_address)
                    <div class="address-block">{{ strtoupper($sale->consignee_name) }}</div>
                    <div class="address-block">{{ $sale->consignee_address }}</div>
                @else
                    <div class="address-block">KHEWAT NO 155, KILLA 61//22/2/2, 82//2/2, VILLAGE BAMNOLI, Bahadurgarh,</div>
                    <div class="address-block">Jhajjar, Haryana, 124507</div>
                @endif
                <div class="detail-row" style="margin-top: 10px;">
                    <span class="detail-label">GSTN/UIN</span>
                    <span>{{ $sale->customer->gstin ?? '06DTVPA7505K1ZB' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">State</span>
                    <span>{{ $customer_state }}</span>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 8%;">S.N</th>
                    <th style="width: 25%;">Description of Goods</th>
                    <th style="width: 12%;">HSN/SAC</th>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 10%;">UNIT</th>
                    <th style="width: 12%;">PRICE</th>
                    <th style="width: 13%;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $item->product->name }}</td>
                    <td>251710</td>
                    <td>{{ number_format($item->net_wt, 2) }}</td>
                    <td>Tonnes</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach

                <!-- Empty rows to match format -->
                @for($i = $sale->items->count(); $i < 4; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor

                <!-- Total Row -->
                <tr style="font-weight: bold;">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($sale->items->sum('net_wt'), 2) }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-right">{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Tax and Amount Section -->
        <div class="tax-section">
            <div class="tax-left">
                <div style="font-weight: bold; margin-bottom: 5px;">Remarks</div>
            </div>
            <div class="tax-right">
                <div class="amount-summary">
                    <div class="amount-row">
                        <span class="amount-label">GST Amount</span>
                        <span class="amount-value">{{ number_format($tax_amount, 2) }}</span>
                    </div>
                    @if($cgst_amount > 0)
                    <div class="amount-row">
                        <span class="amount-label">CGST @ 2.5%</span>
                        <span class="amount-value">{{ number_format($cgst_amount, 2) }}</span>
                    </div>
                    @else
                    <div class="amount-row">
                        <span class="amount-label">CGST @ 2.5%</span>
                        <span class="amount-value">0.00</span>
                    </div>
                    @endif
                    @if($sgst_amount > 0)
                    <div class="amount-row">
                        <span class="amount-label">SGST @2.5%</span>
                        <span class="amount-value">{{ number_format($sgst_amount, 2) }}</span>
                    </div>
                    @else
                    <div class="amount-row">
                        <span class="amount-label">SGST @2.5%</span>
                        <span class="amount-value">0.00</span>
                    </div>
                    @endif
                    @if($igst_amount > 0)
                    <div class="amount-row">
                        <span class="amount-label">IGST @5%</span>
                        <span class="amount-value">{{ number_format($igst_amount, 2) }}</span>
                    </div>
                    @else
                    <div class="amount-row">
                        <span class="amount-label">IGST @5%</span>
                        <span class="amount-value">0.00</span>
                    </div>
                    @endif
                    <div class="amount-row total-amount">
                        <span class="amount-label">Total</span>
                        <span class="amount-value">{{ number_format($total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="amount-words">
            <strong>Amount INR (In Words)</strong><br>
            {{ $amount_in_words }}
        </div>

        <!-- HSN Tax Table -->
        <table class="tax-table" style="margin-bottom: 10px;">
            <thead>
                <tr>
                    <th rowspan="2">HSN/SAC</th>
                    <th rowspan="2">TAXABLE VALUE</th>
                    <th colspan="2">CGST</th>
                    <th colspan="2">SGST</th>
                    <th colspan="2">IGST</th>
                </tr>
                <tr>
                    <th>Rate</th>
                    <th>Amount</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>251710</td>
                    <td>{{ number_format($tax_amount, 2) }}</td>
                    <td>{{ $cgst_amount > 0 ? '2.50%' : '0%' }}</td>
                    <td>{{ number_format($cgst_amount, 2) }}</td>
                    <td>{{ $sgst_amount > 0 ? '2.50%' : '0%' }}</td>
                    <td>{{ number_format($sgst_amount, 2) }}</td>
                    <td>{{ $igst_amount > 0 ? '5%' : '0%' }}</td>
                    <td>{{ number_format($igst_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="bank-details">
                <div style="font-weight: bold; margin-bottom: 5px;">Company Bank Details</div>
                <div class="bank-row">
                    <span class="bank-label">Account Holder Name</span>
                    <span>{{ $company['bank_details']['account_holder'] }}</span>
                </div>
                <div class="bank-row">
                    <span class="bank-label">Bank Name</span>
                    <span>{{ $company['bank_details']['bank_name'] }}</span>
                </div>
                <div class="bank-row">
                    <span class="bank-label">Account Number</span>
                    <span>{{ $company['bank_details']['account_number'] }}</span>
                </div>
                <div class="bank-row">
                    <span class="bank-label">IFSC Code</span>
                    <span>{{ $company['bank_details']['ifsc_code'] }}</span>
                </div>
            </div>
            <div class="payment-details">
                <div style="font-weight: bold; margin-bottom: 5px;">Payment Details</div>
                <div style="margin-bottom: 10px;">
                    @if($sale->customer)
                        {{ strtoupper($sale->customer->name) }}
                    @else
                        ANUP STOCK
                    @endif
                </div>
                <div style="margin-bottom: 5px;">Mode of Payment: UPI</div>

                <!-- QR Code Placeholder -->
                <div class="qr-code" style="background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                    Scan to Pay<br>
                    <small style="font-size: 8px;">{{ $company['upi_id'] }}</small>
                </div>

                <div style="margin-top: 10px; font-size: 10px;">
                    for {{ $company['name'] }}
                </div>

                <div class="signature-area">
                    Authorised Signatory
                </div>
            </div>
        </div>
    </div>
</body>
</html>
