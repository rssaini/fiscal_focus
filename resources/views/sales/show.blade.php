@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sale Details - {{ $sale->invoice_number }}</h4>
                    <div>
                        @if($sale->status === 'draft')
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @if(in_array($sale->status, ['confirmed', 'partially_paid']))
                            <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success">
                                <i class="fas fa-money-bill"></i> Add Payment
                            </a>
                        @endif
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                @if($sale->status === 'draft')
                                    <li>
                                        <form action="{{ route('sales.confirm', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item"
                                                    onclick="return confirm('Are you sure you want to confirm this sale?')">
                                                <i class="fas fa-check"></i> Confirm Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if(!in_array($sale->status, ['paid', 'cancelled']))
                                    <li>
                                        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Are you sure you want to cancel this sale?')">
                                                <i class="fas fa-times"></i> Cancel Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print Invoice
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Sale Status Banner -->
                    <div class="row mb-4">
                        <div class="col-12">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'confirmed' => 'warning',
                                    'paid' => 'success',
                                    'partially_paid' => 'info',
                                    'cancelled' => 'danger'
                                ];
                                $statusColor = $statusColors[$sale->status] ?? 'secondary';
                            @endphp
                            <div class="alert alert-{{ $statusColor }} d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Status: {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                                    </h5>
                                </div>
                                <div class="text-end">
                                    <strong>Created:</strong> {{ $sale->created_at->format('d-m-Y H:i') }}<br>
                                    <strong>Updated:</strong> {{ $sale->updated_at->format('d-m-Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Invoice Number:</strong></td>
                                            <td>{{ $sale->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>{{ $sale->date->format('d-m-Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td>
                                                <a href="{{ route('customers.show', $sale->customer) }}" class="text-decoration-none">
                                                    {{ $sale->customer->name }}
                                                </a>
                                                @if($sale->customer->phone)
                                                    <br><small class="text-muted">{{ $sale->customer->phone }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($sale->refParty)
                                        <tr>
                                            <td><strong>Reference Party:</strong></td>
                                            <td>{{ $sale->refParty->name }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Vehicle Number:</strong></td>
                                            <td><span class="badge bg-primary fs-6">{{ $sale->vehicle_no }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Product:</strong></td>
                                            <td>
                                                <span class="badge bg-info fs-6">{{ $sale->product->name }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Weight & Amount Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Tare Weight:</strong></td>
                                            <td>{{ number_format($sale->tare_wt, 2) }} KG</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Gross Weight:</strong></td>
                                            <td>{{ number_format($sale->gross_wt, 2) }} KG</td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>Net Weight:</strong></td>
                                            <td><strong>{{ number_format($sale->net_wt, 2) }} KG</strong></td>
                                        </tr>
                                        <tr class="table-active">
                                            <td><strong>Weight (Ton):</strong></td>
                                            <td><strong>{{ number_format($sale->wt_ton, 3) }} Ton</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Product Rate:</strong></td>
                                            <td>₹{{ number_format($sale->product_rate, 2) }} per ton</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong>Amount:</strong></td>
                                            <td><strong>₹{{ number_format($sale->amount, 2) }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transport & GST Details -->
                    @if($sale->tp_no || $sale->invoice_rate || $sale->tp_wt || $sale->total_gst)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Transport & GST Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>TP Number:</strong><br>
                                            <span class="text-muted">{{ $sale->tp_no ?: 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Invoice Rate:</strong><br>
                                            <span class="text-muted">₹{{ $sale->invoice_rate ? number_format($sale->invoice_rate, 2) : 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>TP Weight:</strong><br>
                                            <span class="text-muted">{{ $sale->tp_wt ? number_format($sale->tp_wt, 3) . ' Ton' : 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total GST:</strong><br>
                                            <span class="text-success">₹{{ $sale->total_gst ? number_format($sale->total_gst, 2) : '0.00' }}</span>
                                            @if($sale->cgst || $sale->sgst)
                                                <small class="d-block text-muted">
                                                    CGST: ₹{{ number_format($sale->cgst, 2) }} | SGST: ₹{{ number_format($sale->sgst, 2) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Receipt & Royalty Details -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Receipt & Royalty Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Receipt Number:</strong><br>
                                            <span class="text-muted">{{ $sale->rec_no }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Royalty Book No:</strong><br>
                                            <span class="text-muted">{{ $sale->royalty_book_no ?: 'N/A' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Royalty Receipt No:</strong><br>
                                            <span class="text-muted">{{ $sale->royalty_receipt_no ?: 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consignee Details -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Consignee Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Consignee Name:</strong><br>
                                            <span class="text-muted">{{ $sale->consignee_name }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Consignee Address:</strong><br>
                                            <span class="text-muted">{{ $sale->consignee_address }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Payment Summary</h6>
                                    @if(in_array($sale->status, ['confirmed', 'partially_paid']))
                                        <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus"></i> Add Payment
                                        </a>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h5 class="mb-1">₹{{ number_format($sale->total_amount, 2) }}</h5>
                                                <small class="text-muted">Total Amount</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-success text-white rounded">
                                                <h5 class="mb-1">₹{{ number_format($sale->getTotalPaidAmount(), 2) }}</h5>
                                                <small>Paid Amount</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-warning text-white rounded">
                                                <h5 class="mb-1">₹{{ number_format($sale->getRemainingAmount(), 2) }}</h5>
                                                <small>Remaining Amount</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 bg-info text-white rounded">
                                                <h5 class="mb-1">{{ $sale->payments->count() }}</h5>
                                                <small>Total Payments</small>
                                            </div>
                                        </div>
                                    </div>

                                    @if($sale->payments->isNotEmpty())
                                        <h6>Recent Payments</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Reference</th>
                                                        <th>Date</th>
                                                        <th>Method</th>
                                                        <th>Amount</th>
                                                        <th>Transaction ID</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sale->payments->take(5) as $payment)
                                                        <tr>
                                                            <td>{{ $payment->payment_reference }}</td>
                                                            <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    {{ ucfirst($payment->payment_method) }}
                                                                </span>
                                                            </td>
                                                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                                                            <td>{{ $payment->transaction_id ?: '-' }}</td>
                                                            <td>
                                                                @php
                                                                    $paymentStatusColors = [
                                                                        'pending' => 'warning',
                                                                        'cleared' => 'success',
                                                                        'bounced' => 'danger',
                                                                        'cancelled' => 'dark'
                                                                    ];
                                                                @endphp
                                                                <span class="badge bg-{{ $paymentStatusColors[$payment->status] ?? 'secondary' }}">
                                                                    {{ ucfirst($payment->status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{ route('sales.payments.show', [$sale, $payment]) }}"
                                                                       class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                    @if($payment->status !== 'cancelled')
                                                                        <a href="{{ route('sales.payments.edit', [$sale, $payment]) }}"
                                                                           class="btn btn-sm btn-warning">
                                                                            <i class="fas fa-edit"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('sales.payments.index', $sale) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-list"></i> View All Payments ({{ $sale->payments->count() }})
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-money-bill fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No payments recorded yet</p>
                                            @if(in_array($sale->status, ['confirmed', 'partially_paid']))
                                                <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success">
                                                    <i class="fas fa-plus"></i> Add First Payment
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($sale->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $sale->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Activity Timeline -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Activity Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Sale Created</h6>
                                                <p class="timeline-text">Sale was created with status: {{ $sale->status }}</p>
                                                <small class="text-muted">{{ $sale->created_at->format('d-m-Y H:i:s') }}</small>
                                            </div>
                                        </div>

                                        @if($sale->updated_at != $sale->created_at)
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Sale Updated</h6>
                                                <p class="timeline-text">Sale details were last updated</p>
                                                <small class="text-muted">{{ $sale->updated_at->format('d-m-Y H:i:s') }}</small>
                                            </div>
                                        </div>
                                        @endif

                                        @foreach($sale->payments->sortByDesc('created_at') as $payment)
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title">Payment Received</h6>
                                                <p class="timeline-text">
                                                    ₹{{ number_format($payment->amount, 2) }} received via {{ ucfirst($payment->payment_method) }}
                                                    @if($payment->transaction_id)
                                                        <br><small>Transaction ID: {{ $payment->transaction_id }}</small>
                                                    @endif
                                                </p>
                                                <small class="text-muted">{{ $payment->created_at->format('d-m-Y H:i:s') }}</small>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 1px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
}

.timeline-text {
    margin: 0 0 5px 0;
    color: #6c757d;
}

@media print {
    .btn, .dropdown, .card-header .btn-group, .timeline {
        display: none !important;
    }

    .container-fluid {
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .alert {
        border: 1px solid #ddd !important;
        background: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>
@endsection
