@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Chart of Accounts</h1>
        <p class="text-muted mb-0">Manage your company's account structure</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ledgers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-book"></i> View Ledgers
        </a>
        <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Account
        </a>
    </div>
</div>

<!-- Account Type Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="btn-group" role="group" aria-label="Account type filter">
                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-all" value="all" checked>
                    <label class="btn btn-outline-secondary" for="filter-all">All Accounts</label>

                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-asset" value="asset">
                    <label class="btn btn-outline-primary" for="filter-asset">Assets</label>

                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-liability" value="liability">
                    <label class="btn btn-outline-danger" for="filter-liability">Liabilities</label>

                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-equity" value="equity">
                    <label class="btn btn-outline-warning" for="filter-equity">Equity</label>

                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-revenue" value="revenue">
                    <label class="btn btn-outline-success" for="filter-revenue">Revenue</label>

                    <input type="radio" class="btn-check" name="accountTypeFilter" id="filter-expense" value="expense">
                    <label class="btn btn-outline-info" for="filter-expense">Expenses</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="accountSearch" placeholder="Search accounts...">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accounts Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="accountsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%">Code</th>
                        <th style="width: 35%">Account Name</th>
                        <th style="width: 12%">Type</th>
                        <th style="width: 15%">Subtype</th>
                        <th style="width: 10%">Normal Balance</th>
                        <th style="width: 8%">Status</th>
                        <th style="width: 8%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                        @include('chart-of-accounts.partials.account-row', ['account' => $account, 'level' => 0])
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($accounts->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                <p class="text-muted">No accounts found. Set up your chart of accounts!</p>
                <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Account
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Account Summary Cards -->
@if(!$accounts->isEmpty())
<div class="row mt-4">
    @php
        $allAccounts = \App\Models\ChartOfAccount::all();
        $summary = [
            'asset' => ['count' => 0, 'color' => 'primary', 'icon' => 'building'],
            'liability' => ['count' => 0, 'color' => 'danger', 'icon' => 'credit-card'],
            'equity' => ['count' => 0, 'color' => 'warning', 'icon' => 'user-tie'],
            'revenue' => ['count' => 0, 'color' => 'success', 'icon' => 'arrow-up'],
            'expense' => ['count' => 0, 'color' => 'info', 'icon' => 'arrow-down']
        ];

        foreach($allAccounts as $account) {
            if(isset($summary[$account->account_type])) {
                $summary[$account->account_type]['count']++;
            }
        }
    @endphp

    @foreach($summary as $type => $data)
        <div class="col-md-2">
            <div class="card border-{{ $data['color'] }}">
                <div class="card-body text-center p-3">
                    <i class="fas fa-{{ $data['icon'] }} fa-2x text-{{ $data['color'] }} mb-2"></i>
                    <h4 class="mb-1">{{ $data['count'] }}</h4>
                    <small class="text-muted">{{ ucfirst($type) }} Accounts</small>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-md-2">
        <div class="card border-secondary">
            <div class="card-body text-center p-3">
                <i class="fas fa-calculator fa-2x text-secondary mb-2"></i>
                <h4 class="mb-1">{{ $allAccounts->count() }}</h4>
                <small class="text-muted">Total Accounts</small>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Account type filtering
    const filterButtons = document.querySelectorAll('input[name="accountTypeFilter"]');
    const searchInput = document.getElementById('accountSearch');
    const tableRows = document.querySelectorAll('#accountsTable tbody tr');

    function filterAccounts() {
        const selectedType = document.querySelector('input[name="accountTypeFilter"]:checked').value;
        const searchTerm = searchInput.value.toLowerCase();

        tableRows.forEach(row => {
            const accountType = row.dataset.accountType;
            const accountName = row.querySelector('.account-name').textContent.toLowerCase();
            const accountCode = row.querySelector('.account-code').textContent.toLowerCase();

            const typeMatch = selectedType === 'all' || accountType === selectedType;
            const searchMatch = accountName.includes(searchTerm) || accountCode.includes(searchTerm);

            if (typeMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterButtons.forEach(button => {
        button.addEventListener('change', filterAccounts);
    });

    searchInput.addEventListener('input', filterAccounts);

    // Toggle child accounts
    document.querySelectorAll('.toggle-children').forEach(button => {
        button.addEventListener('click', function() {
            const parentId = this.dataset.parentId;
            const childRows = document.querySelectorAll(`tr[data-parent-id="${parentId}"]`);
            const icon = this.querySelector('i');

            childRows.forEach(row => {
                if (row.style.display === 'none') {
                    row.style.display = '';
                    icon.className = 'fas fa-minus-circle text-primary';
                } else {
                    row.style.display = 'none';
                    icon.className = 'fas fa-plus-circle text-primary';
                }
            });
        });
    });
});
</script>
@endsection
