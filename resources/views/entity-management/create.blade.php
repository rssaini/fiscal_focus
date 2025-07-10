@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Add Entity Management Record</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('entity-management.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="head_name" class="form-label">Head Name <span class="text-danger">*</span></label>
                            <select class="form-select @error('head_name') is-invalid @enderror"
                                    id="head_name" name="head_name" required>
                                <option value="">Select Head Name</option>
                                @foreach($headNames as $key => $value)
                                    <option value="{{ $key }}" {{ old('head_name') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('head_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="chart_of_account_id" class="form-label">Chart of Account <span class="text-danger">*</span></label>
                            <select class="form-select @error('chart_of_account_id') is-invalid @enderror"
                                    id="chart_of_account_id" name="chart_of_account_id" required>
                                <option value="">Select Chart of Account</option>
                                @foreach($chartOfAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_code }} - {{ $account->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('chart_of_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the chart of account under which entity ledgers will be created.</div>
                        </div>

                        <div class="mb-3">
                            <label for="voucher_type" class="form-label">Voucher Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('voucher_type') is-invalid @enderror"
                                    id="voucher_type" name="voucher_type" required>
                                <option value="">Select Voucher Type</option>
                                @foreach($voucherTypes as $key => $value)
                                    <option value="{{ $key }}" {{ old('voucher_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('voucher_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Type of voucher that will be posted for this entity.</div>
                        </div>

                        <div class="mb-3">
                            <label for="ledger_id" class="form-label">Ledger <span class="text-danger">*</span></label>
                            <select class="form-select @error('ledger_id') is-invalid @enderror"
                                    id="ledger_id" name="ledger_id" required>
                                <option value="">Select Ledger</option>
                                @foreach($ledgers as $ledger)
                                    <option value="{{ $ledger->id }}" {{ old('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                        {{ $ledger->name }}
                                        @if($ledger->ledger_code)
                                            ({{ $ledger->ledger_code }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('ledger_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the ledger where vouchers will be posted.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('entity-management.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Record</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Example Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-lightbulb"></i> Examples</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Example</h6>
                            <ul class="small">
                                <li><strong>Head Name:</strong> customers</li>
                                <li><strong>Chart of Account:</strong> Accounts Receivable</li>
                                <li><strong>Voucher Type:</strong> sale</li>
                                <li><strong>Ledger:</strong> Sale Ledger</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Employee Example</h6>
                            <ul class="small">
                                <li><strong>Head Name:</strong> employees</li>
                                <li><strong>Chart of Account:</strong> Salary Payable</li>
                                <li><strong>Voucher Type:</strong> expense</li>
                                <li><strong>Ledger:</strong> Salary Expense Ledger</li>
                            </ul>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Vendor Example</h6>
                            <ul class="small">
                                <li><strong>Head Name:</strong> vendors</li>
                                <li><strong>Chart of Account:</strong> Accounts Payable</li>
                                <li><strong>Voucher Type:</strong> purchase</li>
                                <li><strong>Ledger:</strong> Purchase Ledger</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Supplier Example</h6>
                            <ul class="small">
                                <li><strong>Head Name:</strong> suppliers</li>
                                <li><strong>Chart of Account:</strong> Accounts Payable</li>
                                <li><strong>Voucher Type:</strong> purchase</li>
                                <li><strong>Ledger:</strong> Purchase Ledger</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
