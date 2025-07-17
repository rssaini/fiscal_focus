@extends('layouts.app')

@section('title', 'Sale Payments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Payments - Invoice: {{ $sale->invoice_number }}</h4>
                    <div>
                        @if($remainingAmount > 0)
                            <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Payment
                            </a>
                        @endif
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sale
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Payment Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>₹{{ number_format($sale->total_amount, 2) }}</h4>
                                    <p class="mb-0">Total Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>₹{{ number_format($sale->getTotalPaidAmount(), 2) }}</h4>
                                    <p class="mb-0">Paid Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>₹{{ number_format($remainingAmount, 2) }}</h4>
                                    <p class="mb-0">Remaining</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $payments->count() }}</h4>
                                    <p class="mb-0">Total Payments</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    @if($payments->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Transaction ID</th>
                                        <th>Cheque Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_reference }}</td>
                                            <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                                            </td>
                                            <td>{{ $payment->transaction_id ?: '-' }}</td>
                                            <td>
                                                @if($payment->cheque_number)
                                                    <small>
                                                        <strong>No:</strong> {{ $payment->cheque_number }}<br>
                                                        <strong>Date:</strong> {{ $payment->cheque_date?->format('d-m-Y') }}<br>
                                                        <strong>Bank:</strong> {{ $payment->bank_name }}
                                                    </small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'cleared' => 'success',
                                                        'bounced' => 'danger',
                                                        'cancelled' => 'dark'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
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
                                                        <form action="{{ route('sales.payments.destroy', [$sale, $payment]) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this payment?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-money-bill fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No payments recorded yet</h5>
                            @if($remainingAmount > 0)
                                <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success mt-3">
                                    <i class="fas fa-plus"></i> Add First Payment
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
