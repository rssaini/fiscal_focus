@extends('layouts.app_')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Customer: {{ $customer->display_name }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer) }}" method="POST" id="customerEditForm">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Basic Information</h5>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_type') is-invalid @enderror"
                                    id="customer_type" name="customer_type" required onchange="toggleCompanyFields()">
                                <option value="">Select Type</option>
                                <option value="individual" {{ old('customer_type', $customer->customer_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type', $customer->customer_type) == 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                            @error('customer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5" id="companyNameField">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Contact Information</h5>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                   id="mobile" name="mobile" value="{{ old('mobile', $customer->mobile) }}">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="row mb-4" id="businessFields">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Business Information</h5>
                        </div>
                        <div class="col-md-4">
                            <label for="gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror"
                                   id="gstin" name="gstin" value="{{ old('gstin', $customer->gstin) }}" maxlength="15">
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="pan" class="form-label">PAN</label>
                            <input type="text" class="form-control @error('pan') is-invalid @enderror"
                                   id="pan" name="pan" value="{{ old('pan', $customer->pan) }}" maxlength="10">
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   id="website" name="website" value="{{ old('website', $customer->website) }}">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Address Information</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="billing_address" class="form-label">Billing Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                      id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address', $customer->billing_address) }}</textarea>
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="shipping_address" class="form-label">Shipping Address</label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                      id="shipping_address" name="shipping_address" rows="3">{{ old('shipping_address', $customer->shipping_address) }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city', $customer->city) }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror"
                                   id="state" name="state" value="{{ old('state', $customer->state) }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country" value="{{ old('country', $customer->country) }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror"
                                   id="pincode" name="pincode" value="{{ old('pincode', $customer->pincode) }}" required>
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status & Credit Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Status & Credit Information</h5>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status', $customer->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="credit_limit" class="form-label">Credit Limit (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                   id="credit_limit" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}">
                            @error('credit_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="credit_days" class="form-label">Credit Days</label>
                            <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                   id="credit_days" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}">
                            @error('credit_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Warning for customers with transactions -->
                    @if($customer->ledger && $customer->ledger->transactions()->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This customer has {{ $customer->ledger->transactions()->count() }} existing transaction(s).
                            Changes to customer status may affect business operations.
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Customer
                        </button>
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customer
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCompanyFields() {
    const customerType = document.getElementById('customer_type').value;
    const companyNameField = document.getElementById('companyNameField');
    const businessFields = document.getElementById('businessFields');

    if (customerType === 'business') {
        companyNameField.style.display = 'block';
        businessFields.style.display = 'flex';
        document.getElementById('company_name').required = true;
    } else {
        companyNameField.style.display = 'none';
        businessFields.style.display = 'none';
        document.getElementById('company_name').required = false;
    }
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    toggleCompanyFields();

    // Auto-uppercase GSTIN and PAN
    document.getElementById('gstin').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    document.getElementById('pan').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endsection
