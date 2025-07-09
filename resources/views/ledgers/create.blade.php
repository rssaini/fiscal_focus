@extends('layouts.app_')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Create New Ledger</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('ledgers.store') }}" method="POST" id="ledgerForm">
                    @csrf

                    <div class="mb-3">
                        <label for="chart_of_account_id" class="form-label">Chart of Account <span class="text-danger">*</span></label>
                        <select class="form-select @error('chart_of_account_id') is-invalid @enderror"
                                id="chart_of_account_id" name="chart_of_account_id" required>
                            <option value="">Select Chart of Account</option>
                            @foreach($chartOfAccounts->groupBy('account_type') as $accountType => $accounts)
                                <optgroup label="{{ ucfirst(str_replace('_', ' ', $accountType)) }}">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}"
                                                data-normal-balance="{{ $account->normal_balance }}"
                                                data-account-type="{{ $account->account_type }}"
                                                data-account-subtype="{{ $account->account_subtype }}"
                                                {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_code }} - {{ $account->account_name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('chart_of_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <small id="accountInfo" class="text-muted"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Ledger Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required
                               placeholder="e.g., XYZ Bank Account, ABC Customer">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter a specific name for this ledger (e.g., specific bank, customer, or supplier name)</div>
                    </div>

                    <div class="mb-3">
                        <label for="folio" class="form-label">Folio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('folio') is-invalid @enderror"
                               id="folio" name="folio" value="{{ old('folio') }}" required
                               placeholder="e.g., BNK-001, CUST-001">
                        @error('folio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter a unique folio reference for this ledger</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('opening_date') is-invalid @enderror"
                                       id="opening_date" name="opening_date" value="{{ old('opening_date') }}" required>
                                @error('opening_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="opening_balance" class="form-label">Opening Balance <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror"
                                       id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}" required>
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="balance_type" class="form-label">Balance Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="balance_type"
                                           id="balance_type_debit" value="debit"
                                           {{ old('balance_type') == 'debit' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="balance_type_debit">
                                        <i class="fas fa-arrow-up text-primary"></i> Debit
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="balance_type"
                                           id="balance_type_credit" value="credit"
                                           {{ old('balance_type') == 'credit' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="balance_type_credit">
                                        <i class="fas fa-arrow-down text-success"></i> Credit
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('balance_type')
                            <div class="text-danger"><small>{{ $message }}</small></div>
                        @enderror
                        <div class="form-text">
                            <div class="alert alert-info mt-2" id="balanceTypeHelp" style="display: none;">
                                <small id="balanceTypeHelpText"></small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Ledger
                        </button>
                        <a href="{{ route('ledgers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Ledgers
                        </a>
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-sitemap"></i> Manage Chart of Accounts
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOfAccountSelect = document.getElementById('chart_of_account_id');
    const accountInfo = document.getElementById('accountInfo');
    const balanceTypeHelp = document.getElementById('balanceTypeHelp');
    const balanceTypeHelpText = document.getElementById('balanceTypeHelpText');
    const debitRadio = document.getElementById('balance_type_debit');
    const creditRadio = document.getElementById('balance_type_credit');

    chartOfAccountSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (selectedOption.value) {
            const normalBalance = selectedOption.dataset.normalBalance;
            const accountType = selectedOption.dataset.accountType;
            const accountSubtype = selectedOption.dataset.accountSubtype;

            // Show account information
            accountInfo.innerHTML = `
                <i class="fas fa-info-circle"></i>
                Type: <strong>${accountType.replace('_', ' ')}</strong> |
                Subtype: <strong>${accountSubtype.replace('_', ' ')}</strong> |
                Normal Balance: <strong class="text-${normalBalance === 'debit' ? 'primary' : 'success'}">${normalBalance}</strong>
            `;

            // Auto-select suggested balance type
            if (normalBalance === 'debit') {
                debitRadio.checked = true;
                balanceTypeHelpText.innerHTML = `
                    <i class="fas fa-lightbulb"></i> <strong>Suggested:</strong> This account type typically has a <strong>debit</strong> balance.
                    <br><small>Assets and Expenses increase with debits, decrease with credits.</small>
                `;
            } else {
                creditRadio.checked = true;
                balanceTypeHelpText.innerHTML = `
                    <i class="fas fa-lightbulb"></i> <strong>Suggested:</strong> This account type typically has a <strong>credit</strong> balance.
                    <br><small>Liabilities, Equity, and Revenue increase with credits, decrease with debits.</small>
                `;
            }

            balanceTypeHelp.style.display = 'block';
        } else {
            accountInfo.innerHTML = '';
            balanceTypeHelp.style.display = 'none';
        }
    });

    // Auto-generate folio based on account selection
    chartOfAccountSelect.addEventListener('change', function() {
        const folioInput = document.getElementById('folio');
        const selectedOption = this.options[this.selectedIndex];

        if (selectedOption.value && !folioInput.value) {
            const accountCode = selectedOption.text.split(' - ')[0];
            const accountType = selectedOption.dataset.accountType.toUpperCase();
            folioInput.value = `${accountCode}-001`;
        }
    });
});
</script>

@endsection
