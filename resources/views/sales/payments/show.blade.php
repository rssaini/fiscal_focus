@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Payment Details - {{ $payment->payment_reference }}</h4>
                    <div>
                        @if($payment->status !== 'cancelled')
                            <a href="{{ route('sales.payments.edit', [$sale, $payment]) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Payment
                            </a>
                        @endif
                        <a href="{{ route('sales.payments.index', $sale) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Payments
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Payment Status Banner -->
                    <div class="row mb-4">
                        <div class="col-12">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'cleared' => 'success',
                                    'bounced' => 'danger',
                                    'cancelled' => 'dark'
                                ];
                                $statusColor = $statusColors[$payment->status] ?? 'secondary';
                            @endphp
                            <div class="alert alert-{{ $statusColor }} d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-money-bill"></i>
                                        Payment Status: {{ ucfirst($payment->status) }}
                                    </h5>
                                </div>
                                <div class="text-end">
                                    <strong>Amount:</strong> ₹{{ number_format($payment->amount, 2) }}<br>
                                    <strong>Method:</strong> {{ ucfirst($payment->payment_method) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sale Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Related Sale Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Invoice Number:</strong></td>
                                            <td>
                                                <a href="{{ route('sales.show', $sale) }}" class="text-decoration-none">
                                                    {{ $sale->invoice_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Consignee:</strong></td>
                                            <td>{{ $sale->consignee?->consignee_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sale Date:</strong></td>
                                            <td>{{ $sale->date->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sale Amount:</strong></td>
                                            <td>₹{{ number_format($sale->total_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Paid:</strong></td>
                                            <td>₹{{ number_format($sale->getTotalPaidAmount(), 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Remaining:</strong></td>
                                            <td>₹{{ number_format($sale->getRemainingAmount(), 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Reference:</strong></td>
                                            <td>{{ $payment->payment_reference }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Method:</strong></td>
                                            <td>
                                                <span class="badge bg-secondary fs-6">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Amount:</strong></td>
                                            <td><h5 class="text-success mb-0">₹{{ number_format($payment->amount, 2) }}</h5></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $statusColor }} fs-6">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created:</strong></td>
                                            <td>{{ $payment->created_at->format('d-m-Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details (if applicable) -->
                    @if($payment->transaction_id || $payment->cheque_number || $payment->bank_name)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Transaction Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if($payment->transaction_id)
                                        <div class="col-md-6">
                                            <strong>Transaction ID:</strong><br>
                                            <span class="text-muted">{{ $payment->transaction_id }}</span>
                                        </div>
                                        @endif

                                        @if($payment->cheque_number)
                                        <div class="col-md-6">
                                            <strong>Cheque Number:</strong><br>
                                            <span class="text-muted">{{ $payment->cheque_number }}</span>
                                        </div>
                                        @endif

                                        @if($payment->cheque_date)
                                        <div class="col-md-6 mt-3">
                                            <strong>Cheque Date:</strong><br>
                                            <span class="text-muted">{{ $payment->cheque_date->format('d-m-Y') }}</span>
                                        </div>
                                        @endif

                                        @if($payment->bank_name)
                                        <div class="col-md-6 mt-3">
                                            <strong>Bank Name:</strong><br>
                                            <span class="text-muted">{{ $payment->bank_name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($payment->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $payment->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    @if($payment->status !== 'cancelled')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('sales.payments.edit', [$sale, $payment]) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Payment
                                        </a>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="fas fa-trash"></i> Delete Payment
                                        </button>
                                        <button type="button" class="btn btn-info" onclick="window.print()">
                                            <i class="fas fa-print"></i> Print Receipt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment?</p>
                <p><strong>Payment Reference:</strong> {{ $payment->payment_reference }}</p>
                <p><strong>Amount:</strong> ₹{{ number_format($payment->amount, 2) }}</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('sales.payments.destroy', [$sale, $payment]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .modal, .card-header {
        display: none !important;
    }

    .container-fluid {
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
