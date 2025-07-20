<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weighment Slip - {{ $sale->id }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            width: 21cm;
            height: 29.7cm;
            line-height: 1.2;
            display: flex;
            justify-content: space-between;
            flex-direction: column;
        }

        .weighment-slip {
            width: 100%;
            max-width: 60%;
            margin: auto;
            padding: 15px;
            background: white;
        }

        .header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .row-item {
            display: flex;
            align-items: center;
        }

        .label {
            font-weight: bold;
            margin-right: 5px;
        }

        .value {
            min-width: 80px;
            border-bottom: 1px solid #ccc;
            padding: 2px;
        }

        .weight-section {
            margin: 15px 0;
        }

        .weight-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0;
        }

        .weight-label {
            font-weight: bold;
            width: 60px;
        }

        .weight-value {
            font-weight: bold;
            margin-right: 20px;
        }

        .datetime {
            display: flex;
            gap: 15px;
        }

        .footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .signature {
            border-bottom: 1px solid #000;
            width: 100px;
            height: 20px;
        }

        .thank-you {
            text-align: center;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- First Copy -->
    <div class="weighment-slip">
        <div class="header">WEIGHMENT SLIP</div>

        <div class="dotted-line"></div>

        <div class="row">
            <div class="row-item">
                <span class="label">RST No.</span>
                <span>:</span>
                <span class="value">{{ $sale->id }}</span>
            </div>
            <div class="row-item">
                <span class="label">Vehicle No.</span>
                <span>:</span>
                <span class="value">{{ $sale->vehicle_no }}</span>
            </div>
        </div>

        <div class="row">
            <div class="row-item">
                <span class="label">Misc 1st Wt</span>
                <span>:</span>
                <span class="value"></span>
            </div>
            <div class="row-item">
                <span class="label">Supp/Cust</span>
                <span>:</span>
                <span class="value"></span>
            </div>
        </div>

        <div class="row">
            <div class="row-item">
                <span class="label">Misc 2nd Wt</span>
                <span>:</span>
                <span class="value"></span>
            </div>
            <div class="row-item">
                <span class="label">Product</span>
                <span>:</span>
                <span class="value">{{$sale->lastItemName()}}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <div class="weight-section">
            <div class="weight-row">
                <div>
                    <span class="weight-label">Gross</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->gross_wt) }} Kg</span>
                </div>
                <div class="datetime">
                    <span><strong>Date:</strong> {{ $sale->date->format('d/m/Y') }}</span>
                    <span><strong>Time:</strong> {{ $sale->date->format('h:i a') }}</span>
                </div>
            </div>

            <div class="weight-row">
                <div>
                    <span class="weight-label">Tare</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->tare_wt) }} Kg</span>
                </div>
            </div>

            <div class="weight-row">
                <div>
                    <span class="weight-label">Net</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->net_wt) }} Kg</span>
                </div>
            </div>
        </div>

        <div class="dotted-line"></div>

        <div class="footer">
            <div>
                <span class="label">Signature :</span>
                <span class="signature"></span>
            </div>
            <div>
                <strong>Thank you, visit again !</strong>
            </div>
        </div>
    </div>

    <!-- Second Copy (Duplicate) -->
    <div class="weighment-slip">
        <div class="header">WEIGHMENT SLIP</div>

        <div class="dotted-line"></div>

        <div class="row">
            <div class="row-item">
                <span class="label">RST No.</span>
                <span>:</span>
                <span class="value">{{ $sale->id }}</span>
            </div>
            <div class="row-item">
                <span class="label">Vehicle No.</span>
                <span>:</span>
                <span class="value">{{ $sale->vehicle_no }}</span>
            </div>
        </div>

        <div class="row">
            <div class="row-item">
                <span class="label">Misc 1st Wt</span>
                <span>:</span>
                <span class="value"></span>
            </div>
            <div class="row-item">
                <span class="label">Supp/Cust</span>
                <span>:</span>
                <span class="value"></span>
            </div>
        </div>

        <div class="row">
            <div class="row-item">
                <span class="label">Misc 2nd Wt</span>
                <span>:</span>
                <span class="value"></span>
            </div>
            <div class="row-item">
                <span class="label">Product</span>
                <span>:</span>
                <span class="value">{{$sale->lastItemName()}}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <div class="weight-section">
            <div class="weight-row">
                <div>
                    <span class="weight-label">Gross</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->gross_wt) }} Kg</span>
                </div>
                <div class="datetime">
                    <span><strong>Date:</strong> {{ $sale->date->format('d/m/Y') }}</span>
                    <span><strong>Time:</strong> {{ $sale->date->format('h:i a') }}</span>
                </div>
            </div>

            <div class="weight-row">
                <div>
                    <span class="weight-label">Tare</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->tare_wt) }} Kg</span>
                </div>
            </div>

            <div class="weight-row">
                <div>
                    <span class="weight-label">Net</span>
                    <span>:</span>
                    <span class="weight-value">{{ number_format($sale->net_wt) }} Kg</span>
                </div>
            </div>
        </div>

        <div class="dotted-line"></div>

        <div class="footer">
            <div>
                <span class="label">Signature :</span>
                <span class="signature"></span>
            </div>
            <div>
                <strong>Thank you, visit again !</strong>
            </div>
        </div>
    </div>
</body>
</html>
