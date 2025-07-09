@extends('layouts.app_')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Customer Management</h1>
        <p class="text-muted mb-0">Manage customer accounts and relationships</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.export', request()->all()) }}" class="btn btn-outline-success">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="customer_type" class="form-label">Customer Type</label>
                <select class="form-select" name="customer_type" id="customer_type">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('customer_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="business" {{ request('customer_type') == 'business' ? 'selected' : '' }}>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="city" class="form-label">City</label>
                <select class="form-select" name="city" id="city">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="state" class="form-label">State</label>
                <select class="form-select" name="state" id="state">
                    <option value="">All States</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" name="search" id="search"
                       value="{{ request('search') }}" placeholder="Name, code, email, phone...">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Details</th>
                            <th>Contact Info</th>
                            <th>Location</th>
                            <th>Credit Info</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            @php
                                $balance = $customer->getCurrentBalance();
                                $outstanding = $customer->getOutstandingAmount();
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $customer->display_name }}</strong>
                                        <span class="badge bg-{{ $customer->customer_type == 'business' ? 'info' : 'secondary' }} ms-1">
                                            {{ ucfirst($customer->customer_type) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $customer->customer_code }}</small>
                                    @if($customer->company_name && $customer->customer_type == 'business')
                                        <br><small class="text-primary">{{ $customer->company_name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->email)
                                        <div><i class="fas fa-envelope text-muted"></i> {{ $customer->email }}</div>
                                    @endif
                                    @if($customer->mobile)
                                        <div><i class="fas fa-mobile-alt text-muted"></i> {{ $customer->mobile }}</div>
                                    @endif
                                    @if($customer->phone)
                                        <div><i class="fas fa-phone text-muted"></i> {{ $customer->phone }}</div>
                                    @endif
                                    @if($customer->primaryContact)
                                        <small class="text-info">Contact: {{ $customer->primaryContact->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $customer->city }}, {{ $customer->state }}</div>
                                    <small class="text-muted">{{ $customer->pincode }}</small>
                                </td>
                                <td>
                                    <div>
                                        <small>Limit: <strong>₹{{ number_format($customer->credit_limit, 0) }}</strong></small>
                                    </div>
                                    <div>
                                        <small>Days: {{ $customer->credit_days }}</small>
                                    </div>
                                    @if($customer->isOverCreditLimit())
                                        <span class="badge bg-danger">Over Limit</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-{{ $balance['type'] == 'debit' ? 'danger' : 'success' }}">
                                            ₹{{ number_format($balance['balance'], 2) }}
                                        </strong>
                                        <span class="badge bg-{{ $balance['type'] == 'debit' ? 'danger' : 'success' }}">
                                            {{ ucfirst($balance['type']) }}
                                        </span>
                                    </div>
                                    @if($outstanding > 0)
                                        <small class="text-danger">Outstanding: ₹{{ number_format($outstanding, 2) }}</small>
                                    @endif
                                </td>
                                <td>{!! $customer->status_badge !!}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('customers.show', $customer) }}"
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}"
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($customer->ledger)
                                            <a href="{{ route('ledgers.show', $customer->ledger) }}"
                                               class="btn btn-outline-info" title="Ledger">
                                                <i class="fas fa-book"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }}
                    of {{ $customers->total() }} customers
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No customers found.</p>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Customer
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Summary Cards -->
@if($customers->count() > 0)
    @php
        $allCustomers = \App\Models\Customer::selectRaw('
            customer_type,
            status,
            COUNT(*) as count,
            SUM(credit_limit) as total_credit_limit
        ')->groupBy('customer_type', 'status')->get();

        $summary = [];
        foreach($allCustomers as $item) {
            $summary[$item->customer_type][$item->status] = [
                'count' => $item->count,
                'credit_limit' => $item->total_credit_limit
            ];
        }
    @endphp

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Customers</h6>
                    <h4 class="text-primary">{{ $customers->total() }}</h4>
                    <small class="text-muted">All registered customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title">Active Customers</h6>
                    @php $activeCount = \App\Models\Customer::where('status', 'active')->count(); @endphp
                    <h4 class="text-success">{{ $activeCount }}</h4>
                    <small class="text-muted">Currently active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title">Business Customers</h6>
                    @php $businessCount = \App\Models\Customer::where('customer_type', 'business')->count(); @endphp
                    <h4 class="text-warning">{{ $businessCount }}</h4>
                    <small class="text-muted">Corporate clients</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title">Over Credit Limit</h6>
                    @php
                        $overLimitCount = \App\Models\Customer::whereRaw('
                            credit_limit > 0 AND
                            (SELECT COALESCE(SUM(debit) - SUM(credit), 0) FROM transactions
                             JOIN ledgers ON transactions.ledger_id = ledgers.id
                             WHERE ledgers.id = customers.ledger_id) > credit_limit
                        ')->count();
                    @endphp
                    <h4 class="text-danger">{{ $overLimitCount }}</h4>
                    <small class="text-muted">Needs attention</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
