@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Account: {{ $chartOfAccount->account_name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('chart-of-accounts.update', $chartOfAccount) }}" method="POST" id="accountEditForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_code') is-invalid @enderror"
                                       id="account_code" name="account_code"
                                       value="{{ old('account_code', $chartOfAccount->account_code) }}" required>
                                @error('account_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_name') is-invalid @enderror"
                                       id="account_name" name="account_name"
                                       value="{{ old('account_name', $chartOfAccount->account_name) }}" required>
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('account_type') is-invalid @enderror"
                                        id="account_type" name="account_type" required>
                                    <option value="">Select Account Type</option>
                                    <option value="asset" {{ old('account_type', $chartOfAccount->account_type) == 'asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="liability" {{ old('account_type', $chartOfAccount->account_type) == 'liability' ? 'selected' : '' }}>Liability</option>
                                    <option value="equity" {{ old('account_type', $chartOfAccount->account_type) == 'equity' ? 'selected' : '' }}>Equity</option>
                                    <option value="revenue" {{ old('account_type', $chartOfAccount->account_type) == 'revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="expense" {{ old('account_type', $chartOfAccount->account_type) == 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_subtype" class="form-label">Account Subtype <span class="text-danger">*</span></label>
                                <select class="form-select @error('account_subtype') is-invalid @enderror"
                                        id="account_subtype" name="account_subtype" required>
                                    <option value="">Select Account Subtype</option>
                                </select>
                                @error('account_subtype')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="normal_balance" class="form-label">Normal Balance <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="normal_balance"
                                                   id="normal_balance_debit" value="debit"
                                                   {{ old('normal_balance', $chartOfAccount->normal_balance) == 'debit' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="normal_balance_debit">
                                                <i class="fas fa-arrow-up text-primary"></i> Debit
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="normal_balance"
                                                   id="normal_balance_credit" value="credit"
                                                   {{ old('normal_balance', $chartOfAccount->normal_balance) == 'credit' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="normal_balance_credit">
                                                <i class="fas fa-arrow-down text-success"></i> Credit
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('normal_balance')
                                    <div class="text-danger"><small>{{ $message }}</small></div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Parent Account</label>
                                <select class="form-select @error('parent_id') is-invalid @enderror"
                                        id="parent_id" name="parent_id">
                                    <option value="">No Parent (Main Category)</option>
                                    @foreach($parentAccounts as $parent)
                                        @if($parent->id != $chartOfAccount->id)
                                            <option value="{{ $parent->id }}"
                                                    {{ old('parent_id', $chartOfAccount->parent_id) == $parent->id ? 'selected' : '' }}>
                                                {{ $parent->indented_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allow_posting"
                                           name="allow_posting" value="1"
                                           {{ old('allow_posting', $chartOfAccount->allow_posting) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_posting">
                                        Allow Direct Posting
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                           name="is_active" value="1"
                                           {{ old('is_active', $chartOfAccount->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order"
                                       value="{{ old('sort_order', $chartOfAccount->sort_order) }}">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $chartOfAccount->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Warnings for changes -->
                    @if($chartOfAccount->ledgers && $chartOfAccount->ledgers->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This account has {{ $chartOfAccount->ledgers->count() }} associated ledger(s).
                            Changes to account type or normal balance may affect existing transactions.
                        </div>
                    @endif

                    @if($chartOfAccount->children->count() > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> This account has {{ $chartOfAccount->children->count() }} sub-account(s).
                            Consider the impact on the account hierarchy when making changes.
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Account
                        </button>
                        <a href="{{ route('chart-of-accounts.show', $chartOfAccount) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Account
                        </a>
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Chart of Accounts
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountTypeSelect = document.getElementById('account_type');
    const accountSubtypeSelect = document.getElementById('account_subtype');
    const currentSubtype = '{{ old('account_subtype', $chartOfAccount->account_subtype) }}';

    const subtypes = {
        asset: [
            { value: 'current_asset', text: 'Current Asset' },
            { value: 'fixed_asset', text: 'Fixed Asset' },
            { value: 'intangible_asset', text: 'Intangible Asset' },
            { value: 'other_asset', text: 'Other Asset' }
        ],
        liability: [
            { value: 'current_liability', text: 'Current Liability' },
            { value: 'long_term_liability', text: 'Long-term Liability' }
        ],
        equity: [
            { value: 'owner_equity', text: 'Owner Equity' },
            { value: 'retained_earnings', text: 'Retained Earnings' }
        ],
        revenue: [
            { value: 'operating_revenue', text: 'Operating Revenue' },
            { value: 'other_revenue', text: 'Other Revenue' }
        ],
        expense: [
            { value: 'cost_of_goods_sold', text: 'Cost of Goods Sold' },
            { value: 'operating_expense', text: 'Operating Expense' },
            { value: 'other_expense', text: 'Other Expense' }
        ]
    };

    function populateSubtypes(selectedType) {
        accountSubtypeSelect.innerHTML = '<option value="">Select Account Subtype</option>';

        if (selectedType && subtypes[selectedType]) {
            subtypes[selectedType].forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype.value;
                option.textContent = subtype.text;
                option.selected = subtype.value === currentSubtype;
                accountSubtypeSelect.appendChild(option);
            });
        }
    }

    // Populate subtypes on page load
    populateSubtypes(accountTypeSelect.value);

    accountTypeSelect.addEventListener('change', function() {
        populateSubtypes(this.value);
    });
});
</script>
@endsection
