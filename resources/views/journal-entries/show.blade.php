@extends('layouts.app_')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Journal Entry Details</h1>
        <p class="text-muted mb-0">
            Date: {{ $journalEntry['transaction_date']->format('d/m/Y') }} |
            UUID: <code>{{ $journalEntry['uuid'] }}</code>
        </p>
    </div>
    <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Entries
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $journalEntry['particular'] }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ledger</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Running Balance</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            @foreach($journalEntry['transactions'] as $transaction)
                                @php
                                    $totalDebit += $transaction->debit;
                                    $totalCredit += $transaction->credit;
                                @endphp
                                <tr>
                                    <td>{{ $transaction->ledger->name }}</td>
                                    <td>{{ $transaction->debit > 0 ? number_format($transaction->debit, 2) : '-' }}</td>
                                    <td>{{ $transaction->credit > 0 ? number_format($transaction->credit, 2) : '-' }}</td>
                                    <td>

                                        <span class="badge bg-{{ $transaction->running_balance_type == 'credit' ? 'success' : 'primary' }}">
                                            {{ ucfirst($transaction->running_balance_type == 'credit' ? 'CR' : 'DR') }}
                                            {{ number_format($transaction->running_balance, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th>{{ number_format($totalDebit, 2) }}</th>
                                <th>{{ number_format($totalCredit, 2) }}</th>
                                <th colspan="2">
                                    @if(abs($totalDebit - $totalCredit) < 0.01)
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="badge bg-danger">Unbalanced</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ number_format($totalDebit, 2) }}</h4>
                            <small class="text-muted">Total Debit</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ number_format($totalCredit, 2) }}</h4>
                        <small class="text-muted">Total Credit</small>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <p><strong>Ledgers Affected:</strong> {{ count($journalEntry['transactions']) }}</p>
                    <p><strong>Created:</strong> {{ $journalEntry['transactions']->first()->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
