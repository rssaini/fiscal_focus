@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Consignee Details Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $consignee->consignee_name }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('consignees.edit', $consignee) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('consignees.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Basic Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $consignee->consignee_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>GSTIN:</strong></td>
                                <td>{{ $consignee->formatted_gstin }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>{!! $consignee->status_badge !!}</td>
                            </tr>
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>#{{ $consignee->id }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Address Information</h6>
                        <address>
                            {{ $consignee->address }}<br>
                            @if($consignee->address2)
                                {{ $consignee->address2 }}<br>
                            @endif
                            {{ $consignee->city }}, {{ $consignee->state }} - {{ $consignee->zip }}
                        </address>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Timeline</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $consignee->created_at->format('M d, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td>{{ $consignee->updated_at->format('M d, Y g:i A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Days Since Created:</strong></td>
                                <td>{{ $consignee->created_at->diffInDays(now()) }} days</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Statistics</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Total Sales:</strong></td>
                                <td>{{ $consignee->sales_count }}</td>
                            </tr>
                            <tr>
                                <td><strong>Display Name:</strong></td>
                                <td>{{ $consignee->display_name }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        @if($recentSales->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Sales (Latest 10)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Vehicle No</th>
                                <th>Weight (Tons)</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                            <tr>
                                <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                                <td>{{ $sale->date->format('M d, Y') }}</td>
                                <td>{{ $sale->vehicle_no }}</td>
                                <td>{{ number_format($sale->wt_ton, 2) }}</td>
                                <td>₹{{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $sale->status == 'confirmed' ? 'success' : ($sale->status == 'draft' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <!-- Quick Stats Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary mb-1">{{ $consignee->sales_count }}</h4>
                            <small class="text-muted">Total Sales</small>
                        </div>
                    </div>
                </div>

                @if($consignee->sales_count > 0)
                    @php
                        $totalAmount = $consignee->sales->sum('total_amount');
                        $totalWeight = $consignee->sales->sum('wt_ton');
                        $avgAmount = $consignee->sales->avg('total_amount');
                    @endphp

                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h5 class="text-success mb-1">₹{{ number_format($totalAmount, 2) }}</h5>
                                <small class="text-muted">Total Sale Value</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h5 class="text-info mb-1">{{ number_format($totalWeight, 2) }}</h5>
                                <small class="text-muted">Total Weight (Tons)</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h5 class="text-warning mb-1">₹{{ number_format($avgAmount, 2) }}</h5>
                                <small class="text-muted">Average Sale Value</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('consignees.edit', $consignee) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Consignee
                    </a>
                    @if($consignee->sales_count == 0)
                        <form method="POST" action="{{ route('consignees.destroy', $consignee) }}"
                              onsubmit="return confirm('Are you sure you want to delete this consignee? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete Consignee
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger" disabled title="Cannot delete consignee with sales records">
                            <i class="fas fa-trash"></i> Cannot Delete (Has Sales)
                        </button>
                    @endif
                    <a href="{{ route('consignees.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> All Consignees
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Export & Print</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Details
                    </button>
                    <a href="{{ route('consignees.export', ['search' => $consignee->consignee_name]) }}" class="btn btn-outline-info">
                        <i class="fas fa-download"></i> Export as CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn, .card-header, .col-md-4 {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection
