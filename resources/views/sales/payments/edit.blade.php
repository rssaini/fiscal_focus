@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Payment - {{ $payment->payment_reference }}</h4>
                    <div>
                        <a href="{{ route('sales.payments.show', [$sale, $payment]) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Payment
                        </a>
                        <a href="{{ route('sales.payments.index', $sale) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Payments
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Sale Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Invoice:</strong> {{ $sale->invoice_number }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Consignee:</strong> {{ $sale->consignee?->consignee_name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total Amount:</strong> ₹{{ number_format($sale->total_amount, 2) }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Remaining:</strong>
                                            <span class="text-danger">₹{{ number_format($sale->getRemainingAmount() + $payment->amount, 2) }}</span>
                                            <small class="text-muted">(excluding this payment)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('sales.payments.update', [$sale, $payment]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_reference" class="form-label">Payment Reference</label>
                                    <input type="text" class="form-control"
                                           id="payment_reference" value="{{ $payment->payment_reference }}" readonly>
                                    <small class="form-text text-muted">Payment reference cannot be changed</small>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                           id="payment_date" name="payment_date"
                                           value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method" required onchange="togglePaymentFields()">
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentMethods as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('payment_method', $payment->payment_method) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01"
                                           max="{{ $sale->getRemainingAmount() + $payment->amount }}"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount"
                                           value="{{ old('amount', $payment->amount) }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Maximum: ₹{{ number_format($sale->getRemainingAmount() + $payment->amount, 2) }}
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3" id="transaction_id_field"
                                     style="display: {{ in_array($payment->payment_method, ['upi', 'rtgs', 'neft']) ? 'block' : 'none' }};">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control @error('transaction_id') is-invalid @enderror"
                                           id="transaction_id" name="transaction_id"
                                           value="{{ old('transaction_id', $payment->transaction_id) }}">
                                    @error('transaction_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cheque Fields -->
                                <div id="cheque_fields" style="display: {{ $payment->payment_method === 'cheque' ? 'block' : 'none' }};">
                                    <div class="mb-3">
                                        <label for="cheque_number" class="form-label">Cheque Number</label>
                                        <input type="text" class="form-control @error('cheque_number') is-invalid @enderror"
                                               id="cheque_number" name="cheque_number"
                                               value="{{ old('cheque_number', $payment->cheque_number) }}">
                                        @error('cheque_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="cheque_date" class="form-label">Cheque Date</label>
                                        <input type="date" class="form-control @error('cheque_date') is-invalid @enderror"
                                               id="cheque_date" name="cheque_date"
                                               value="{{ old('cheque_date', $payment->cheque_date?->format('Y-m-d')) }}">
                                        @error('cheque_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                                               id="bank_name" name="bank_name"
                                               value="{{ old('bank_name', $payment->bank_name) }}">
                                        @error('bank_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status" name="status">
                                        <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="cleared" {{ old('status', $payment->status) == 'cleared' ? 'selected' : '' }}>Cleared</option>
                                        <option value="bounced" {{ old('status', $payment->status) == 'bounced' ? 'selected' : '' }}>Bounced</option>
                                        <option value="cancelled" {{ old('status', $payment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="3">{{ old('notes', $payment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sales.payments.show', [$sale, $payment]) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const transactionField = document.getElementById('transaction_id_field');
    const chequeFields = document.getElementById('cheque_fields');

    // Hide all fields first
    transactionField.style.display = 'none';
    chequeFields.style.display = 'none';

    // Show relevant fields based on payment method
    if (['upi', 'rtgs', 'neft'].includes(paymentMethod)) {
        transactionField.style.display = 'block';
    } else if (paymentMethod === 'cheque') {
        chequeFields.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentFields();
});
</script>
@endsection
