@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Ledgers</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-sitemap"></i> Chart of Accounts
        </a>
        <a href="{{ route('ledgers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Ledger
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($ledgers->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Ledger Name</th>
                            <th>Account Type</th>
                            <th>Chart of Account</th>
                            <th>Folio</th>
                            <th>Opening Date</th>
                            <th>Opening Balance</th>
                            <th>Current Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ledgers as $ledger)
                            @php
                                $currentBalance = $ledger->getCurrentBalance();
                            @endphp
                            <tr>
                                <td>
                                    @if($ledger->chartOfAccount)
                                        <span class="badge bg-secondary">{{ $ledger->chartOfAccount->account_code }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $ledger->name }}</strong>
                                    @if($ledger->chartOfAccount)
                                        <br><small class="text-muted">{{ $ledger->chartOfAccount->account_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($ledger->chartOfAccount)
                                        <span class="badge bg-info">
                                            {{ ucfirst(str_replace('_', ' ', $ledger->chartOfAccount->account_type)) }}
                                        </span>
                                        <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $ledger->chartOfAccount->account_subtype)) }}</small>
                                    @else
                                        <span class="text-muted">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ledger->chartOfAccount)
                                        {{ $ledger->chartOfAccount->formatted_code }}
                                    @else
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Not Linked
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $ledger->folio }}</td>
                                <td>{{ $ledger->opening_date->format('d/m/Y') }}</td>
                                <td>
                                    {{ number_format($ledger->opening_balance, 2) }}
                                    <span class="badge bg-{{ $ledger->balance_type == 'credit' ? 'success' : 'primary' }}">
                                        {{ ucfirst($ledger->balance_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $currentBalance['type'] == 'credit' ? 'success' : 'primary' }}">
                                        {{ number_format($currentBalance['balance'], 2) }} {{ ucfirst($currentBalance['type']) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('ledgers.show', $ledger) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if(!$ledger->chartOfAccount)
                                            <a href="{{ route('ledgers.edit', $ledger) }}" class="btn btn-sm btn-outline-warning" title="Link to Chart of Account">
                                                <i class="fas fa-link"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Cards -->
            <div class="row mt-4">
                @php
                    $accountTypes = $ledgers->groupBy(function($ledger) {
                        return $ledger->chartOfAccount ? $ledger->chartOfAccount->account_type : 'unlinked';
                    });
                @endphp
                @foreach($accountTypes as $type => $ledgersInType)
                    <div class="col-md-2">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center p-2">
                                <h6 class="card-title mb-1">{{ ucfirst(str_replace('_', ' ', $type)) }}</h6>
                                <span class="badge bg-secondary">{{ $ledgersInType->count() }} ledgers</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted">No ledgers found. Create your first ledger!</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sitemap"></i> Setup Chart of Accounts
                    </a>
                    <a href="{{ route('ledgers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Ledger
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
