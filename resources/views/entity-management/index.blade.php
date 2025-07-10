@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Entity Management</h4>
                    <div>
                        <a href="{{ route('entity-management.create-defaults') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-magic"></i> Create Defaults
                        </a>
                        <a href="{{ route('entity-management.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Record
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('entity-management.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="head_name" class="form-label">Head Name</label>
                                    <select class="form-select" name="head_name" id="head_name">
                                        <option value="">All Head Names</option>
                                        @foreach($headNames as $key => $value)
                                            <option value="{{ $key }}" {{ request('head_name') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="voucher_type" class="form-label">Voucher Type</label>
                                    <select class="form-select" name="voucher_type" id="voucher_type">
                                        <option value="">All Voucher Types</option>
                                        @foreach($voucherTypes as $key => $value)
                                            <option value="{{ $key }}" {{ request('voucher_type') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                                    <a href="{{ route('entity-management.index') }}" class="btn btn-outline-secondary">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Head Name</th>
                                    <th>Chart of Account</th>
                                    <th>Voucher Type</th>
                                    <th>Ledger</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entityManagements as $entityManagement)
                                    <tr>
                                        <td>{{ $entityManagement->id }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $entityManagement->head_name_display }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $entityManagement->chartOfAccount->account_code }}</strong><br>
                                            <small class="text-muted">{{ $entityManagement->chartOfAccount->account_name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $entityManagement->voucher_type_display }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $entityManagement->ledger->name }}</strong><br>
                                            <small class="text-muted">{{ $entityManagement->ledger->ledger_code ?? 'No Code' }}</small>
                                        </td>
                                        <td>
                                            @if ($entityManagement->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('entity-management.show', $entityManagement) }}" class="btn btn-sm btn-info">View</a>
                                                <a href="{{ route('entity-management.edit', $entityManagement) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('entity-management.destroy', $entityManagement) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No entity management records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $entityManagements->withQueryString()->links() }}
                    </div>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> How Entity Management Works</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Entity Creation</h6>
                            <p class="small text-muted">
                                When an entity (customer, employee, vendor, etc.) is created, the system uses these records
                                to determine under which Chart of Account the entity's ledger should be created.
                            </p>
                            <ul class="small">
                                <li><strong>Customers</strong> → Accounts Receivable</li>
                                <li><strong>Employees</strong> → Salary Payable</li>
                                <li><strong>Vendors/Suppliers</strong> → Accounts Payable</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Voucher Posting</h6>
                            <p class="small text-muted">
                                When vouchers are created, the system uses these records to determine
                                which ledger should be used for posting the transaction.
                            </p>
                            <ul class="small">
                                <li><strong>Customer Sale</strong> → Sale Ledger</li>
                                <li><strong>Employee Expense</strong> → Salary Expense Ledger</li>
                                <li><strong>Vendor Purchase</strong> → Purchase Ledger</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
