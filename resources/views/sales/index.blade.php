@extends('layouts.app')

@section('title', 'Sales Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sales Management</h4>
                    <div>
                        <a href="{{ route('sales.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Sale
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select">
                                <option value="">All Customers</option>
                                @foreach($customers as $id => $name)
                                    <option value="{{ $id }}" {{ request('customer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control"
                                   placeholder="Invoice, Vehicle, Rec No..." value="{{ request('search') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>

                    <!-- Sales Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Vehicle No</th>
                                    <th>Product</th>
                                    <th>Net Wt (KG)</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->date->format('d-m-Y') }}</td>
                                        <td>{{ $sale->customer?->name }}</td>
                                        <td>{{ $sale->vehicle_no }}</td>
                                        <td>{{ $sale->product->name }}</td>
                                        <td>{{ number_format($sale->net_wt, 2) }}</td>
                                        <td>₹{{ number_format($sale->total_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'confirmed' => 'warning',
                                                    'paid' => 'success',
                                                    'partially_paid' => 'info',
                                                    'cancelled' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$sale->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($sale->status === 'draft')
                                                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if(in_array($sale->status, ['confirmed', 'partially_paid']))
                                                    <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-money-bill"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No sales found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $sales->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
