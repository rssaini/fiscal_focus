@extends('layouts.app_')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $ledger->name }}</h1>
        <p class="text-muted mb-0">Folio: {{ $ledger->folio }}</p>
    </div>
    <div>
        <a href="{{ route('ledgers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ledgers
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('ledgers.show', $ledger) }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date"
                       value="{{ $fromDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date"
                       value="{{ $toDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('ledgers.export', $ledger) }}?from_date={{ $fromDate->format('Y-m-d') }}&to_date={{ $toDate->format('Y-m-d') }}"
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <a href="{{ route('ledgers.export-pdf', $ledger) }}?from_date={{ $fromDate->format('Y-m-d') }}&to_date={{ $toDate->format('Y-m-d') }}"
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Ledger Statement -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            Ledger Statement
            <small class="text-muted">
                ({{ $fromDate->format('d/m/Y') }} to {{ $toDate->format('d/m/Y') }})
            </small>
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Opening Balance Row -->
                    <tr class="table-info">
                        <td>{{ $fromDate->format('d/m/Y') }}</td>
                        <td><strong>Opening Balance</strong></td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            <strong>
                                <span class="badge bg-{{ $openingBalance['type'] == 'credit' ? 'success' : 'primary' }}">
                                    {{ ucfirst($openingBalance['type'] == 'credit' ? 'CR' : 'DR') }} {{ number_format($openingBalance['balance'], 2) }}
                                </span>
                            </strong>
                        </td>
                    </tr>

                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                            <td>{{ $transaction->particular }}</td>
                            <td>{{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}</td>
                            <td>{{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->running_balance_type == 'credit' ? 'success' : 'primary' }}">
                                    {{ ucfirst($transaction->running_balance_type == 'credit' ? 'CR' : 'DR') }} {{ number_format($transaction->running_balance, 2) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No transactions found for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
