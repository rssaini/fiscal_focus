<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ledger Statement - {{ $ledger->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #34495e;
        }

        .ledger-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            color: #212529;
        }

        .balance-info {
            background-color: #e3f2fd;
            padding: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .transactions-table th {
            background-color: #343a40;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #454d55;
        }

        .transactions-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .transactions-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .transactions-table tbody tr:hover {
            background-color: #e9ecef;
        }

        .opening-balance-row {
            background-color: #fff3cd !important;
            font-weight: bold;
        }

        .opening-balance-row td {
            border: 2px solid #ffeaa7;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .debit-amount {
            color: #dc3545;
        }

        .credit-amount {
            color: #28a745;
        }

        .balance-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .balance-type.debit {
            background-color: #007bff;
            color: white;
        }

        .balance-type.credit {
            background-color: #28a745;
            color: white;
        }

        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .summary-row.total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">Your Company Name</div>
        <div class="document-title">Ledger Statement</div>
    </div>

    <!-- Ledger Information -->
    <div class="ledger-info">
        <div class="info-row">
            <span class="info-label">Ledger Name:</span>
            <span class="info-value">{{ $ledger->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Folio:</span>
            <span class="info-value">{{ $ledger->folio }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period:</span>
            <span class="info-value">{{ $fromDate->format('d/m/Y') }} to {{ $toDate->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Generated On:</span>
            <span class="info-value">{{ $generatedAt->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <!-- Opening Balance -->
    <div class="balance-info">
        <strong>Opening Balance as on {{ $fromDate->format('d/m/Y') }}:</strong>
        <span class="amount">{{ number_format($openingBalance['balance'], 2) }}</span>
        <span class="balance-type {{ $openingBalance['type'] }}">{{ ucfirst($openingBalance['type']) }}</span>
    </div>

    <!-- Transactions Table -->
    <table class="transactions-table">
        <thead>
            <tr>
                <th style="width: 12%">Date</th>
                <th style="width: 35%">Particular</th>
                <th style="width: 15%">Debit</th>
                <th style="width: 15%">Credit</th>
                <th style="width: 15%">Balance</th>
                <th style="width: 8%">Type</th>
            </tr>
        </thead>
        <tbody>
            <!-- Opening Balance Row -->
            <tr class="opening-balance-row">
                <td>{{ $fromDate->format('d/m/Y') }}</td>
                <td><strong>Opening Balance</strong></td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-right amount">{{ number_format($openingBalance['balance'], 2) }}</td>
                <td class="text-center">
                    <span class="balance-type {{ $openingBalance['type'] }}">
                        {{ ucfirst($openingBalance['type']) }}
                    </span>
                </td>
            </tr>

            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->particular }}</td>
                    <td class="text-right amount {{ $transaction->debit > 0 ? 'debit-amount' : '' }}">
                        {{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}
                    </td>
                    <td class="text-right amount {{ $transaction->credit > 0 ? 'credit-amount' : '' }}">
                        {{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}
                    </td>
                    <td class="text-right amount">{{ number_format($transaction->running_balance, 2) }}</td>
                    <td class="text-center">
                        <span class="balance-type {{ $transaction->running_balance_type }}">
                            {{ ucfirst($transaction->running_balance_type) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #6c757d;">
                        No transactions found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <h4 style="margin-top: 0; margin-bottom: 15px; color: #495057;">Summary</h4>

        <div class="summary-row">
            <span>Total Debit Amount:</span>
            <span class="amount debit-amount">{{ number_format($totalDebit, 2) }}</span>
        </div>

        <div class="summary-row">
            <span>Total Credit Amount:</span>
            <span class="amount credit-amount">{{ number_format($totalCredit, 2) }}</span>
        </div>

        <div class="summary-row">
            <span>Number of Transactions:</span>
            <span>{{ $transactions->count() }}</span>
        </div>

        <div class="summary-row total">
            <span>Closing Balance as on {{ $toDate->format('d/m/Y') }}:</span>
            <span>
                <span class="amount">{{ number_format($closingBalance['balance'], 2) }}</span>
                <span class="balance-type {{ $closingBalance['type'] }}">
                    {{ ucfirst($closingBalance['type']) }}
                </span>
            </span>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document and does not require a signature.</p>
        <p>Generated on {{ $generatedAt->format('d/m/Y H:i:s') }} | Page 1 of 1</p>
    </div>
</body>
</html>
