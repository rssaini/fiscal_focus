@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Entity Management Details</h4>
                    <div>
                        <a href="{{ route('entity-management.edit', $entityManagement) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('entity-management.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $entityManagement->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Head Name:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">{{ $entityManagement->head_name_display }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Voucher Type:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $entityManagement->voucher_type_display }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if ($entityManagement->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Chart of Account Details</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $entityManagement->chartOfAccount->account_name }}</h6>
                                    <p class="card-text">
                                        <strong>Account Code:</strong> {{ $entityManagement->chartOfAccount->account_code }}<br>
                                        <strong>Account Type:</strong>
                                        <span class="badge bg-secondary">{{ ucfirst($entityManagement->chartOfAccount->account_type) }}</span><br>
                                        <strong>Normal Balance:</strong>
                                        <span class="badge bg-{{ $entityManagement->chartOfAccount->normal_balance == 'debit' ? 'warning' : 'info' }}">
                                            {{ ucfirst($entityManagement->chartOfAccount->normal_balance) }}
                                        </span>
                                    </p>
                                    @if($entityManagement->chartOfAccount->description)
                                        <p class="card-text">
                                            <small class="text-muted">{{ $entityManagement->chartOfAccount->description }}</small>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5>Ledger Details</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $entityManagement->ledger->name }}</h6>
                                    <p class="card-text">
                                        @if($entityManagement->ledger->ledger_code)
                                            <strong>Ledger Code:</strong> {{ $entityManagement->ledger->ledger_code }}<br>
                                        @endif
                                        @if($entityManagement->ledger->chart_of_account_id)
                                            <strong>Under Chart of Account:</strong>
                                            {{ $entityManagement->ledger->chartOfAccount->account_name ?? 'N/A' }}<br>
                                        @endif
                                        @if($entityManagement->ledger->opening_balance)
                                            <strong>Opening Balance:</strong>
                                            ₹{{ number_format($entityManagement->ledger->opening_balance, 2) }}<br>
                                        @endif
                                    </p>
                                    @if($entityManagement->ledger->description)
                                        <p class="card-text">
                                            <small class="text-muted">{{ $entityManagement->ledger->description }}</small>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($entityManagement->description)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Description</h5>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $entityManagement->description }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h5>Timestamps</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $entityManagement->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $entityManagement->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Example Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-lightbulb"></i> How This Record Works</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Entity Creation</h6>
                            <p class="small">
                                When a <strong>{{ $entityManagement->head_name_display }}</strong> entity is created,
                                the system will automatically create a ledger under the
                                <strong>{{ $entityManagement->chartOfAccount->account_name }}</strong>
                                ({{ $entityManagement->chartOfAccount->account_code }}) chart of account.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Voucher Posting</h6>
                            <p class="small">
                                When a <strong>{{ $entityManagement->voucher_type_display }}</strong> voucher
                                is created for this entity type, the transaction will be posted to the
                                <strong>{{ $entityManagement->ledger->name }}</strong> ledger.
                            </p>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> Example Scenario</h6>
                        <ol class="small mb-0">
                            <li>Create a {{ $entityManagement->head_name_display }} entity → Ledger created under {{ $entityManagement->chartOfAccount->account_name }}</li>
                            <li>Generate {{ $entityManagement->voucher_type_display }} voucher → Transaction posted to {{ $entityManagement->ledger->name }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
