@extends('layouts.app')

@section('title', 'Edit Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Sale - {{ $sale->invoice_number }}</h4>
                    <div>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Sale
                        </a>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Sales
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('sales.update', $sale) }}" method="POST" id="saleForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" class="form-control"
                                           id="invoice_number" name="invoice_number"
                                           value="{{ $sale->invoice_number }}" readonly>
                                    <small class="form-text text-muted">Invoice number cannot be changed</small>
                                </div>

                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                           id="date" name="date" value="{{ old('date', $sale->date->format('Y-m-d\TH:i')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select class="form-select @error('customer_id') is-invalid @enderror"
                                            id="customer_id" name="customer_id" required onchange="loadCustomerInfo()">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="ref_party_id" class="form-label">Reference Party</label>
                                    <select class="form-select @error('ref_party_id') is-invalid @enderror"
                                            id="ref_party_id" name="ref_party_id">
                                        <option value="">Select Reference Party</option>
                                        @foreach($parties as $party)
                                            <option value="{{ $party->id }}"
                                                {{ old('ref_party_id', $sale->ref_party_id) == $party->id ? 'selected' : '' }}>
                                                {{ $party->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ref_party_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="vehicle_no" class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vehicle_no') is-invalid @enderror"
                                           id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no', $sale->vehicle_no) }}"
                                           placeholder="e.g., HR-26-AB-1234" required>
                                    @error('vehicle_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tare_wt" class="form-label">Tare Weight (KG) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control @error('tare_wt') is-invalid @enderror"
                                                   id="tare_wt" name="tare_wt" value="{{ old('tare_wt', $sale->tare_wt) }}"
                                                   onchange="calculateAmounts()" required>
                                            @error('tare_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gross_wt" class="form-label">Gross Weight (KG) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control @error('gross_wt') is-invalid @enderror"
                                                   id="gross_wt" name="gross_wt" value="{{ old('gross_wt', $sale->gross_wt) }}"
                                                   onchange="calculateAmounts()" required>
                                            @error('gross_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select @error('product_id') is-invalid @enderror"
                                            id="product_id" name="product_id" required onchange="loadProductInfo()">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                {{ old('product_id', $sale->product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="product_rate" class="form-label">Product Rate (₹)</label>
                                            <input type="number" step="0.01" class="form-control @error('product_rate') is-invalid @enderror"
                                                   id="product_rate" name="product_rate" value="{{ old('product_rate', $sale->product_rate) }}"
                                                   onchange="calculateAmounts()">
                                            @error('product_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="net_wt" class="form-label">Net Weight (KG)</label>
                                            <input type="number" step="0.01" class="form-control"
                                                   id="net_wt" name="net_wt" value="{{ $sale->net_wt }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="wt_ton" class="form-label">Weight (Ton)</label>
                                            <input type="number" step="0.001" class="form-control"
                                                   id="wt_ton" name="wt_ton" value="{{ $sale->wt_ton }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">Amount (₹)</label>
                                            <input type="number" step="0.01" class="form-control"
                                                   id="amount" name="amount" value="{{ $sale->amount }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TP & GST Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Transport & GST Details</h5>
                                <hr>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tp_no" class="form-label">TP Number</label>
                                    <input type="text" class="form-control @error('tp_no') is-invalid @enderror"
                                           id="tp_no" name="tp_no" value="{{ old('tp_no', $sale->tp_no) }}">
                                    @error('tp_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="invoice_rate" class="form-label">Invoice Rate (₹)</label>
                                    <input type="number" step="0.01" class="form-control @error('invoice_rate') is-invalid @enderror"
                                           id="invoice_rate" name="invoice_rate" value="{{ old('invoice_rate', $sale->invoice_rate) }}"
                                           onchange="calculateAmounts()">
                                    @error('invoice_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tp_wt" class="form-label">TP Weight (Ton)</label>
                                    <input type="number" step="0.001" class="form-control @error('tp_wt') is-invalid @enderror"
                                           id="tp_wt" name="tp_wt" value="{{ old('tp_wt', $sale->tp_wt) }}"
                                           onchange="calculateAmounts()">
                                    @error('tp_wt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="total_gst" class="form-label">Total GST (₹)</label>
                                    <input type="number" step="0.01" class="form-control"
                                           id="total_gst" name="total_gst" value="{{ $sale->total_gst }}" readonly>
                                    <small class="form-text text-muted">CGST 2.5% + SGST 2.5%</small>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt & Royalty Section -->
                        <div class="row">
                            <div class="col-12">
                                <h5>Receipt & Royalty Details</h5>
                                <hr>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rec_no" class="form-label">Receipt Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('rec_no') is-invalid @enderror"
                                           id="rec_no" name="rec_no" value="{{ old('rec_no', $sale->rec_no) }}" required>
                                    @error('rec_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="royalty_book_no" class="form-label">Royalty Book Number</label>
                                    <input type="text" class="form-control @error('royalty_book_no') is-invalid @enderror"
                                           id="royalty_book_no" name="royalty_book_no" value="{{ old('royalty_book_no', $sale->royalty_book_no) }}">
                                    @error('royalty_book_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="royalty_receipt_no" class="form-label">Royalty Receipt Number</label>
                                    <input type="text" class="form-control @error('royalty_receipt_no') is-invalid @enderror"
                                           id="royalty_receipt_no" name="royalty_receipt_no" value="{{ old('royalty_receipt_no', $sale->royalty_receipt_no) }}">
                                    @error('royalty_receipt_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Consignee Details -->
                        <div class="row">
                            <div class="col-12">
                                <h5>Consignee Details</h5>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="consignee_name" class="form-label">Consignee Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('consignee_name') is-invalid @enderror"
                                           id="consignee_name" name="consignee_name" value="{{ old('consignee_name', $sale->consignee_name) }}" required>
                                    @error('consignee_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="consignee_address" class="form-label">Consignee Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('consignee_address') is-invalid @enderror"
                                              id="consignee_address" name="consignee_address" rows="3" required>{{ old('consignee_address', $sale->consignee_address) }}</textarea>
                                    @error('consignee_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes & Total Amount Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="notes" class="form-label">Notes</label>
                                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                                              id="notes" name="notes" rows="3">{{ old('notes', $sale->notes) }}</textarea>
                                                    @error('notes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-end">
                                                    <h4>Total Amount: ₹<span id="total_amount_display">{{ number_format($sale->total_amount, 2) }}</span></h4>
                                                    <input type="hidden" id="total_amount" name="total_amount" value="{{ $sale->total_amount }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">Cancel</a>
                                    @if($sale->status === 'draft')
                                        <button type="submit" name="status" value="draft" class="btn btn-warning">
                                            <i class="fas fa-save"></i> Update as Draft
                                        </button>
                                        <button type="submit" name="status" value="confirmed" class="btn btn-success">
                                            <i class="fas fa-check"></i> Update & Confirm
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Sale
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize form with existing values
document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token if not present
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.getElementsByTagName('head')[0].appendChild(meta);
    }

    // Calculate amounts on load
    calculateAmounts();
});

function loadCustomerInfo() {
    const customerId = document.getElementById('customer_id').value;
    if (!customerId) return;

    fetch(`/api/sales/customer/${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                console.log('Customer Info:', data);
                // You can populate customer info here if needed
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadProductInfo() {
    const productId = document.getElementById('product_id').value;
    if (!productId) return;

    fetch(`/api/sales/product/${productId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error && data.default_rate) {
                // Only update if current rate is empty
                const currentRate = document.getElementById('product_rate').value;
                if (!currentRate || currentRate == 0) {
                    document.getElementById('product_rate').value = data.default_rate;
                    calculateAmounts();
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

function calculateAmounts() {
    const tare_wt = parseFloat(document.getElementById('tare_wt').value) || 0;
    const gross_wt = parseFloat(document.getElementById('gross_wt').value) || 0;
    const product_rate = parseFloat(document.getElementById('product_rate').value) || 0;
    const invoice_rate = parseFloat(document.getElementById('invoice_rate').value) || 0;
    const tp_wt = parseFloat(document.getElementById('tp_wt').value) || 0;

    const data = {
        tare_wt: tare_wt,
        gross_wt: gross_wt,
        product_rate: product_rate,
        invoice_rate: invoice_rate,
        tp_wt: tp_wt
    };

    fetch('/api/sales/calculate-amounts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('net_wt').value = data.net_wt;
        document.getElementById('wt_ton').value = data.wt_ton;
        document.getElementById('amount').value = data.amount;
        document.getElementById('total_gst').value = data.total_gst;
        document.getElementById('total_amount').value = data.total_amount;
        document.getElementById('total_amount_display').textContent = data.total_amount.toFixed(2);
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
