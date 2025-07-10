@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Entity Management Record</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('entity-management.update', $entityManagement) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="head_name" class="form-label">Head Name <span class="text-danger">*</span></label>
                            <select class="form-select @error('head_name') is-invalid @enderror"
                                    id="head_name" name="head_name" required>
                                <option value="">Select Head Name</option>
                                @foreach($headNames as $key => $value)
                                    <option value="{{ $key }}" {{ old('head_name', $entityManagement->head_name) == $key ? 'selected' : '' }}>
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
                                    <option value="{{ $account->id }}" {{ old('chart_of_account_id', $entityManagement->chart_of_account_id) == $account->id ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('voucher_type', $entityManagement->voucher_type) == $key ? 'selected' : '' }}>
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
                                    <option value="{{ $ledger->id }}" {{ old('ledger_id', $entityManagement->ledger_id) == $ledger->id ? 'selected' : '' }}>
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
                                      id="description" name="description" rows="3">{{ old('description', $entityManagement->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="is_active" value="1" {{ old('is_active', $entityManagement->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('entity-management.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Record</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
