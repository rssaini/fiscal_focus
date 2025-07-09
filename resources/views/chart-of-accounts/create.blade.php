@extends('layouts.app_')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Add New Account</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('chart-of-accounts.store') }}" method="POST" id="accountForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_code" class="form-label">Account Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_code') is-invalid @enderror"
                                       id="account_code" name="account_code" value="{{ old('account_code') }}"
                                       required placeholder="e.g., 1110">
                                @error('account_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Enter a unique numeric code for this account</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('account_name') is-invalid @enderror"
                                       id="account_name" name="account_name" value="{{ old('account_name') }}"
                                       required placeholder="e.g., Cash in Hand">
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
                                    <option value="asset" {{ old('account_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                                    <option value="liability" {{ old('account_type') == 'liability' ? 'selected' : '' }}>Liability</option>
                                    <option value="equity" {{ old('account_type') == 'equity' ? 'selected' : '' }}>Equity</option>
                                    <option value="revenue" {{ old('account_type') == 'revenue' ? 'selected' : '' }}>Revenue</option>
                                    <option value="expense" {{ old('account_type') == 'expense' ? 'selected' : '' }}>Expense</option>
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
                                                   {{ old('normal_balance') == 'debit' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="normal_balance_debit">
                                                <i class="fas fa-arrow-up text-primary"></i> Debit
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="normal_balance"
                                                   id="normal_balance_credit" value="credit"
                                                   {{ old('normal_balance') == 'credit' ? 'checked' : '' }}>
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
                                        <option value="{{ $parent->id }}"
                                                data-account-type="{{ $parent->account_type }}"
                                                {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->indented_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Select a parent account to create a sub-account</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allow_posting"
                                           name="allow_posting" value="1"
                                           {{ old('allow_posting', '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_posting">
                                        Allow Direct Posting
                                    </label>
                                </div>
                                <div class="form-text">Uncheck if this is a header account (no direct transactions)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Enter a description for this account">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Account Guidelines -->
                    <div class="alert alert-info" id="accountGuidelines" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> Account Guidelines</h6>
                        <div id="guidelinesContent"></div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Account
                        </button>
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Chart of Accounts
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
    const normalBalanceDebit = document.getElementById('normal_balance_debit');
    const normalBalanceCredit = document.getElementById('normal_balance_credit');
    const accountGuidelines = document.getElementById('accountGuidelines');
    const guidelinesContent = document.getElementById('guidelinesContent');

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

    const guidelines = {
        asset: {
            normal: 'debit',
            text: 'Assets represent resources owned by the business. They have normal debit balances and increase with debits.',
            examples: 'Cash, Accounts Receivable, Inventory, Equipment, Buildings'
        },
        liability: {
            normal: 'credit',
            text: 'Liabilities represent debts owed by the business. They have normal credit balances and increase with credits.',
            examples: 'Accounts Payable, Notes Payable, Accrued Expenses, Loans'
        },
        equity: {
            normal: 'credit',
            text: 'Equity represents owner\'s interest in the business. It has normal credit balances and increases with credits.',
            examples: 'Owner\'s Capital, Retained Earnings, Common Stock'
        },
        revenue: {
            normal: 'credit',
            text: 'Revenue represents income earned by the business. It has normal credit balances and increases with credits.',
            examples: 'Sales Revenue, Service Revenue, Interest Income, Rent Income'
        },
        expense: {
            normal: 'debit',
            text: 'Expenses represent costs incurred to generate revenue. They have normal debit balances and increase with debits.',
            examples: 'Salaries Expense, Rent Expense, Utilities Expense, Advertising Expense'
        }
    };

    accountTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;

        // Clear and populate subtypes
        accountSubtypeSelect.innerHTML = '<option value="">Select Account Subtype</option>';

        if (selectedType && subtypes[selectedType]) {
            subtypes[selectedType].forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype.value;
                option.textContent = subtype.text;
                accountSubtypeSelect.appendChild(option);
            });
        }

        // Set normal balance and show guidelines
        if (selectedType && guidelines[selectedType]) {
            const guideline = guidelines[selectedType];

            // Set normal balance
            if (guideline.normal === 'debit') {
                normalBalanceDebit.checked = true;
            } else {
                normalBalanceCredit.checked = true;
            }

            // Show guidelines
            guidelinesContent.innerHTML = `
                <p><strong>Normal Balance:</strong> ${guideline.normal.toUpperCase()}</p>
                <p>${guideline.text}</p>
                <p><strong>Examples:</strong> ${guideline.examples}</p>
            `;
            accountGuidelines.style.display = 'block';
        } else {
            accountGuidelines.style.display = 'none';
        }
    });

    // Auto-suggest account code based on type
    accountTypeSelect.addEventListener('change', function() {
        const accountCodeInput = document.getElementById('account_code');
        const selectedType = this.value;

        if (!accountCodeInput.value && selectedType) {
            const codeRanges = {
                asset: '1',
                liability: '2',
                equity: '3',
                revenue: '4',
                expense: '5'
            };

            if (codeRanges[selectedType]) {
                accountCodeInput.value = codeRanges[selectedType] + '000';
                accountCodeInput.focus();
                accountCodeInput.select();
            }
        }
    });
});
</script>
@endsection
