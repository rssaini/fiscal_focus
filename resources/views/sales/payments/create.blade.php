@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add Payment - Invoice: {{ $sale->invoice_number }}</h4>
                    <a href="{{ route('sales.payments.index', $sale) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Payments
                    </a>
                </div>

                <div class="card-body">
                    <!-- Sale Summary -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Customer:</strong> {{ $sale->customer->name }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total Amount:</strong> ₹{{ number_format($sale->total_amount, 2) }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Paid Amount:</strong> ₹{{ number_format($sale->getTotalPaidAmount(), 2) }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Remaining:</strong>
                                            <span class="text-danger">₹{{ number_format($remainingAmount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Payment Form -->
                    <div class="row">
                        <div class="col-md-8">
                            <form action="{{ route('sales.payments.store', $sale) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                                   id="payment_date" name="payment_date"
                                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                            @error('payment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_method') is-invalid @enderror"
                                                    id="payment_method" name="payment_method" required onchange="togglePaymentFields()">
                                                <option value="">Select Payment Method</option>
                                                @foreach($paymentMethods as $value => $label)
                                                    <option value="{{ $value }}" {{ old('payment_method') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" max="{{ $remainingAmount }}"
                                                   class="form-control @error('amount') is-invalid @enderror"
                                                   id="amount" name="amount" value="{{ old('amount', $remainingAmount) }}" required>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3" id="transaction_id_field" style="display: none;">
                                            <label for="transaction_id" class="form-label">Transaction ID</label>
                                            <input type="text" class="form-control @error('transaction_id') is-invalid @enderror"
                                                   id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}">
                                            @error('transaction_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Cheque Fields -->
                                <div id="cheque_fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="cheque_number" class="form-label">Cheque Number</label>
                                                <input type="text" class="form-control @error('cheque_number') is-invalid @enderror"
                                                       id="cheque_number" name="cheque_number" value="{{ old('cheque_number') }}">
                                                @error('cheque_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="cheque_date" class="form-label">Cheque Date</label>
                                                <input type="date" class="form-control @error('cheque_date') is-invalid @enderror"
                                                       id="cheque_date" name="cheque_date" value="{{ old('cheque_date') }}">
                                                @error('cheque_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bank_name" class="form-label">Bank Name</label>
                                                <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                                                       id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                                                @error('bank_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('sales.payments.index', $sale) }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success">Record Payment</button>
                                </div>
                            </form>
                        </div>

                        <!-- Multiple Payments Section -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Multiple Payments</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted">
                                        Need to add multiple payments?
                                        <a href="#" onclick="showMultiplePaymentForm()">Click here</a>
                                    </p>

                                    <div id="multiplePaymentForm" style="display: none;">
                                        <form action="{{ route('sales.payments.store-multiple', $sale) }}" method="POST">
                                            @csrf
                                            <div id="multiplePayments">
                                                <!-- Payment entries will be added here -->
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="addPaymentRow()">
                                                Add Payment
                                            </button>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-success">Record All Payments</button>
                                            </div>
                                        </form>
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

function showMultiplePaymentForm() {
    document.getElementById('multiplePaymentForm').style.display = 'block';
    addPaymentRow(); // Add first row
}

let paymentRowIndex = 0;

function addPaymentRow() {
    const container = document.getElementById('multiplePayments');
    const row = document.createElement('div');
    row.className = 'payment-row border p-2 mb-2';
    row.innerHTML = `
        <div class="mb-2">
            <select name="payments[${paymentRowIndex}][payment_method]" class="form-select form-select-sm" required>
                <option value="">Method</option>
                @foreach($paymentMethods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2">
            <input type="number" step="0.01" name="payments[${paymentRowIndex}][amount]"
                   class="form-control form-control-sm" placeholder="Amount" required>
        </div>
        <div class="mb-2">
            <input type="date" name="payments[${paymentRowIndex}][payment_date]"
                   class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="mb-2">
            <input type="text" name="payments[${paymentRowIndex}][transaction_id]"
                   class="form-control form-control-sm" placeholder="Transaction ID (optional)">
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removePaymentRow(this)">Remove</button>
    `;
    container.appendChild(row);
    paymentRowIndex++;
}

function removePaymentRow(button) {
    button.closest('.payment-row').remove();
}
</script>
@endsection
