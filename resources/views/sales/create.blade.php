@extends('layouts.app')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Create New Sale</h4>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Sales
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                        @csrf

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input readonly type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                           id="date" name="date" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_no" class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vehicle_no') is-invalid @enderror"
                                        id="vehicle_no" name="vehicle_no" value="{{ old('vehicle_no') }}"
                                        placeholder="e.g., HR-26-AB-1234" required>
                                    @error('vehicle_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tare_wt" class="form-label">Tare Weight (KG) <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" class="form-control @error('tare_wt') is-invalid @enderror"
                                            id="tare_wt" name="tare_wt" value="{{ old('tare_wt') }}" required>
                                    @error('tare_wt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                                    <select class="form-select @error('product_id') is-invalid @enderror"
                                            id="product_id" name="product_id" required onchange="loadProductInfo()">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_rate" class="form-label">Product Rate (₹)</label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('product_rate') is-invalid @enderror"
                                            id="product_rate" name="product_rate" value="{{ old('product_rate') }}" required>
                                    @error('product_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" name="action" value="pending" class="btn btn-warning">Create Sale</button>
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
function loadProductInfo() {
    const productSelect = document.getElementById('product_id');
    const rateInput = document.getElementById('product_rate');

    if (!productSelect.value) {
        rateInput.value = '';
        return;
    }

    // Show loading state
    rateInput.value = 'Loading...';
    rateInput.disabled = true;

    // Fetch product details
    fetch(`/api/products/${productSelect.value}`)
        .then(response => response.json())
        .then(data => {
            rateInput.value = data.default_rate || '';
            rateInput.disabled = false;
            rateInput.focus();
        })
        .catch(error => {
            console.error('Error loading product info:', error);
            rateInput.value = '';
            rateInput.disabled = false;
            alert('Error loading product information. Please enter rate manually.');
        });
}

// Function to validate form before submission
function validateSaleForm() {
    const vehicleNo = document.getElementById('vehicle_no').value.trim();
    const tareWt = parseInt(document.getElementById('tare_wt').value);
    const productId = document.getElementById('product_id').value;
    const productRate = parseFloat(document.getElementById('product_rate').value);

    // Basic validations
    if (!vehicleNo) {
        alert('Please enter vehicle number');
        document.getElementById('vehicle_no').focus();
        return false;
    }

    if (!tareWt || tareWt <= 0) {
        alert('Please enter valid tare weight');
        document.getElementById('tare_wt').focus();
        return false;
    }

    if (!productId) {
        alert('Please select a product');
        document.getElementById('product_id').focus();
        return false;
    }

    if (!productRate || productRate <= 0) {
        alert('Please enter valid product rate');
        document.getElementById('product_rate').focus();
        return false;
    }

    return true;
}

// Add event listeners when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation on submit
    const saleForm = document.getElementById('saleForm');
    if (saleForm) {
        saleForm.addEventListener('submit', function(e) {
            if (!validateSaleForm()) {
                e.preventDefault();
            }
        });
    }

    // Format vehicle number input (uppercase)
    const vehicleInput = document.getElementById('vehicle_no');
    if (vehicleInput) {
        vehicleInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Auto-focus first empty field
    const requiredFields = ['vehicle_no', 'tare_wt', 'product_id', 'product_rate'];
    for (const fieldId of requiredFields) {
        const field = document.getElementById(fieldId);
        if (field && !field.value) {
            field.focus();
            break;
        }
    }
});

</script>
@endsection
