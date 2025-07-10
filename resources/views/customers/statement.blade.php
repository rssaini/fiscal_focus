@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Customer Statement</h1>
        <p class="text-muted mb-0">{{ $customer->display_name }} ({{ $customer->customer_code }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.statement', $customer) }}?from_date={{ $fromDate }}&to_date={{ $toDate }}&format=pdf"
           class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customer
        </a>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.statement', $customer) }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date"
                       value="{{ $fromDate }}">
            </div>
            <div class="col-md-4">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date"
                       value="{{ $toDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Generate Statement
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Customer Information -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Customer Details:</h6>
                <p class="mb-1"><strong>{{ $customer->display_name }}</strong></p>
                <p class="mb-1">{{ $customer->full_address }}</p>
                @if($customer->email)
                    <p class="mb-1">Email: {{ $customer->email }}</p>
                @endif
                @if($customer->mobile)
                    <p class="mb-1">Mobile: {{ $customer->mobile }}</p>
                @endif
            </div>
            <div class="col-md-6">
                <h6>Statement Period:</h6>
                <p class="mb-1">From: {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</p>
                <p class="mb-1">To: {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}</p>
                <p class="mb-1">Generated: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Statement Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Account Statement</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Voucher No.</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                        <th>Balance Type</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance -->
                    <tr class="table-info">
                        <td>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</td>
                        <td><strong>Opening Balance</strong></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td><strong>₹{{ number_format($openingBalance['balance'], 2) }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $openingBalance['type'] == 'credit' ? 'success' : 'primary' }}">
                                {{ ucfirst($openingBalance['type']) }}
                            </span>
                        </td>
                    </tr>

                    <!-- Transactions -->
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    @forelse($transactions as $transaction)
                        @php
                            $totalDebit += $transaction->debit;
                            $totalCredit += $transaction->credit;
                        @endphp
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                            <td>{{ $transaction->particular }}</td>
                            <td>
                                @if($transaction->uuid)
                                    <small class="text-muted">{{ $transaction->uuid }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($transaction->debit > 0)
                                    <span class="text-danger">₹{{ number_format($transaction->debit, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($transaction->credit > 0)
                                    <span class="text-success">₹{{ number_format($transaction->credit, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">₹{{ number_format($transaction->running_balance, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->running_balance_type == 'credit' ? 'success' : 'primary' }}">
                                    {{ ucfirst($transaction->running_balance_type) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Summary -->
                @if($transactions->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3">Summary</th>
                            <th class="text-end">₹{{ number_format($totalDebit, 2) }}</th>
                            <th class="text-end">₹{{ number_format($totalCredit, 2) }}</th>
                            <th class="text-end">
                                @php
                                    $lastTransaction = $transactions->last();
                                    $closingBalance = $lastTransaction ? $lastTransaction->running_balance : $openingBalance['balance'];
                                    $closingType = $lastTransaction ? $lastTransaction->running_balance_type : $openingBalance['type'];
                                @endphp
                                ₹{{ number_format($closingBalance, 2) }}
                            </th>
                            <th>
                                <span class="badge bg-{{ $closingType == 'credit' ? 'success' : 'primary' }}">
                                    {{ ucfirst($closingType) }}
                                </span>
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Summary Cards -->
@if($transactions->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title">Opening Balance</h6>
                    <h4 class="text-{{ $openingBalance['type'] == 'credit' ? 'success' : 'primary' }}">
                        ₹{{ number_format($openingBalance['balance'], 2) }}
                    </h4>
                    <small class="text-muted">{{ ucfirst($openingBalance['type']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Debits</h6>
                    <h4 class="text-danger">₹{{ number_format($totalDebit, 2) }}</h4>
                    <small class="text-muted">{{ $transactions->where('debit', '>', 0)->count() }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Credits</h6>
                    <h4 class="text-success">₹{{ number_format($totalCredit, 2) }}</h4>
                    <small class="text-muted">{{ $transactions->where('credit', '>', 0)->count() }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="card-title">Closing Balance</h6>
                    <h4 class="text-{{ $closingType == 'credit' ? 'success' : 'primary' }}">
                        ₹{{ number_format($closingBalance, 2) }}
                    </h4>
                    <small class="text-muted">{{ ucfirst($closingType) }}</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
