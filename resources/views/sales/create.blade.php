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
                                    <button type="submit" name="action" value="draft" class="btn btn-warning">Save as Draft</button>
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
    const productId = document.getElementById('product_id').value;
    if (!productId) return;

    fetch(`/api/sales/product/${productId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.error && data.default_rate) {
                document.getElementById('product_rate').value = data.default_rate;
            }
        })
        .catch(error => console.error('Error:', error));
}

// Add CSRF token to meta tag if not present
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.getElementsByTagName('head')[0].appendChild(meta);
}
</script>
@endsection
