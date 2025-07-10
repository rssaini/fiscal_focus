@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $chartOfAccount->account_name }}</h1>
        <p class="text-muted mb-0">
            Account Code: <span class="badge bg-secondary">{{ $chartOfAccount->account_code }}</span> |
            Type: <span class="badge bg-{{ $chartOfAccount->account_type == 'asset' ? 'primary' :
                                        ($chartOfAccount->account_type == 'liability' ? 'danger' :
                                        ($chartOfAccount->account_type == 'equity' ? 'warning' :
                                        ($chartOfAccount->account_type == 'revenue' ? 'success' : 'info'))) }}">
                {{ ucfirst($chartOfAccount->account_type) }}
            </span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit Account
        </a>
        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Chart
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Account Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Account Code:</td>
                                <td>{{ $chartOfAccount->account_code }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Account Name:</td>
                                <td>{{ $chartOfAccount->account_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Account Type:</td>
                                <td>
                                    <span class="badge bg-{{ $chartOfAccount->account_type == 'asset' ? 'primary' :
                                                            ($chartOfAccount->account_type == 'liability' ? 'danger' :
                                                            ($chartOfAccount->account_type == 'equity' ? 'warning' :
                                                            ($chartOfAccount->account_type == 'revenue' ? 'success' : 'info'))) }}">
                                        {{ ucfirst($chartOfAccount->account_type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Account Subtype:</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $chartOfAccount->account_subtype)) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Normal Balance:</td>
                                <td>
                                    <span class="badge bg-{{ $chartOfAccount->normal_balance == 'debit' ? 'primary' : 'success' }}">
                                        {{ ucfirst($chartOfAccount->normal_balance) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Allow Posting:</td>
                                <td>
                                    @if($chartOfAccount->allow_posting)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-warning">No (Header Account)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    @if($chartOfAccount->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Level:</td>
                                <td>{{ $chartOfAccount->level }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($chartOfAccount->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $chartOfAccount->description }}</p>
                    </div>
                @endif

                @if($chartOfAccount->parent)
                    <div class="mt-3">
                        <h6>Parent Account:</h6>
                        <a href="{{ route('chart-of-accounts.show', $chartOfAccount->parent) }}" class="btn btn-outline-secondary btn-sm">
                            {{ $chartOfAccount->parent->account_code }} - {{ $chartOfAccount->parent->account_name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sub Accounts -->
        @if($chartOfAccount->children->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sub Accounts ({{ $chartOfAccount->children->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Account Name</th>
                                    <th>Subtype</th>
                                    <th>Normal Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartOfAccount->children as $child)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $child->account_code }}</span></td>
                                        <td>
                                            {{ $child->account_name }}
                                            @if(!$child->allow_posting)
                                                <span class="badge bg-dark ms-1">Header</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $child->account_subtype)) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $child->normal_balance == 'debit' ? 'primary' : 'success' }}">
                                                {{ ucfirst($child->normal_balance) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($child->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('chart-of-accounts.show', $child) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Ledgers -->
        @if($chartOfAccount->ledgers && $chartOfAccount->ledgers->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Related Ledgers ({{ $chartOfAccount->ledgers->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ledger Name</th>
                                    <th>Folio</th>
                                    <th>Opening Balance</th>
                                    <th>Current Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartOfAccount->ledgers as $ledger)
                                    @php
                                        $currentBalance = $ledger->getCurrentBalance();
                                    @endphp
                                    <tr>
                                        <td>{{ $ledger->name }}</td>
                                        <td>{{ $ledger->folio }}</td>
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
                                            <a href="{{ route('ledgers.show', $ledger) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Account Hierarchy -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Account Hierarchy</h6>
            </div>
            <div class="card-body">
                @php
                    $hierarchy = [];
                    $current = $chartOfAccount;
                    while($current) {
                        $hierarchy[] = $current;
                        $current = $current->parent;
                    }
                    $hierarchy = array_reverse($hierarchy);
                @endphp

                @foreach($hierarchy as $index => $account)
                    <div class="d-flex align-items-center mb-2">
                        @if($index > 0)
                            <div class="me-2">
                                @for($i = 0; $i < $index; $i++)
                                    <span class="text-muted">—</span>
                                @endfor
                            </div>
                        @endif

                        @if($account->id == $chartOfAccount->id)
                            <strong class="text-primary">{{ $account->account_name }}</strong>
                        @else
                            <a href="{{ route('chart-of-accounts.show', $account) }}" class="text-decoration-none">
                                {{ $account->account_name }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($chartOfAccount->allow_posting)
                        <a href="{{ route('ledgers.create') }}?chart_of_account_id={{ $chartOfAccount->id }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Ledger
                        </a>
                    @endif

                    <a href="{{ route('chart-of-accounts.create') }}?parent_id={{ $chartOfAccount->id }}"
                       class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Add Sub Account
                    </a>

                    <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}"
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Account
                    </a>
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $chartOfAccount->children->count() }}</h4>
                        <small class="text-muted">Sub Accounts</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $chartOfAccount->ledgers ? $chartOfAccount->ledgers->count() : 0 }}</h4>
                        <small class="text-muted">Ledgers</small>
                    </div>
                </div>

                <hr>

                <div class="small text-muted">
                    <p><strong>Created:</strong> {{ $chartOfAccount->created_at->format('d/m/Y H:i') }}</p>
                    @if($chartOfAccount->updated_at != $chartOfAccount->created_at)
                        <p><strong>Updated:</strong> {{ $chartOfAccount->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
