@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Add New Customer</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.store') }}" method="POST" id="customerForm">
                    @csrf

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
                                <option value="individual" {{ old('customer_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type') == 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                            @error('customer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="Contact person name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-5" id="companyNameField" style="display: none;">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name" value="{{ old('company_name') }}"
                                   placeholder="Business/Company name">
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
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="customer@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                   id="mobile" name="mobile" value="{{ old('mobile') }}"
                                   placeholder="+91 98765 43210">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="011-12345678">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="row mb-4" id="businessFields" style="display: none;">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Business Information</h5>
                        </div>
                        <div class="col-md-4">
                            <label for="gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror"
                                   id="gstin" name="gstin" value="{{ old('gstin') }}"
                                   placeholder="22AAAAA0000A1Z5" maxlength="15">
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="pan" class="form-label">PAN</label>
                            <input type="text" class="form-control @error('pan') is-invalid @enderror"
                                   id="pan" name="pan" value="{{ old('pan') }}"
                                   placeholder="ABCDE1234F" maxlength="10">
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   id="website" name="website" value="{{ old('website') }}"
                                   placeholder="https://example.com">
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
                                      id="billing_address" name="billing_address" rows="3" required
                                      placeholder="Street address, building, etc.">{{ old('billing_address') }}</textarea>
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="shipping_address" class="form-label">
                                Shipping Address
                                <small class="text-muted">(leave blank if same as billing)</small>
                            </label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                      id="shipping_address" name="shipping_address" rows="3"
                                      placeholder="Delivery address if different">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city') }}" required
                                   placeholder="City name">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror"
                                   id="state" name="state" value="{{ old('state') }}" required
                                   placeholder="State name">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country" value="{{ old('country', 'India') }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror"
                                   id="pincode" name="pincode" value="{{ old('pincode') }}" required
                                   placeholder="123456">
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Credit Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Credit & Financial Information</h5>
                        </div>
                        <div class="col-md-3">
                            <label for="credit_limit" class="form-label">Credit Limit (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                   id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}"
                                   placeholder="0.00">
                            @error('credit_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="credit_days" class="form-label">Credit Days</label>
                            <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                   id="credit_days" name="credit_days" value="{{ old('credit_days', 30) }}"
                                   placeholder="30">
                            @error('credit_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="opening_balance" class="form-label">Opening Balance (₹)</label>
                            <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror"
                                   id="opening_balance" name="opening_balance" value="{{ old('opening_balance', 0) }}">
                            @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="opening_balance_type" class="form-label">Balance Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('opening_balance_type') is-invalid @enderror"
                                    id="opening_balance_type" name="opening_balance_type" required>
                                <option value="debit" {{ old('opening_balance_type', 'debit') == 'debit' ? 'selected' : '' }}>Debit (Customer owes us)</option>
                                <option value="credit" {{ old('opening_balance_type') == 'credit' ? 'selected' : '' }}>Credit (Advance from customer)</option>
                            </select>
                            @error('opening_balance_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('opening_date') is-invalid @enderror"
                                   id="opening_date" name="opening_date"
                                   value="{{ old('opening_date', now()->format('Y-m-d')) }}" required>
                            @error('opening_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Additional notes about the customer">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Persons -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="border-bottom pb-2 mb-0">Contact Persons</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addContact()">
                                <i class="fas fa-plus"></i> Add Contact
                            </button>
                        </div>

                        <div id="contactsContainer">
                            <!-- Contacts will be added here dynamically -->
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Customer
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let contactCount = 0;

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

function addContact() {
    const container = document.getElementById('contactsContainer');
    const contactDiv = document.createElement('div');
    contactDiv.className = 'row mb-3 contact-row';
    contactDiv.innerHTML = `
        <div class="col-md-3">
            <input type="text" class="form-control" name="contacts[${contactCount}][name]"
                   placeholder="Contact Name" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="contacts[${contactCount}][designation]"
                   placeholder="Designation">
        </div>
        <div class="col-md-3">
            <input type="email" class="form-control" name="contacts[${contactCount}][email]"
                   placeholder="Email">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="contacts[${contactCount}][mobile]"
                   placeholder="Mobile">
        </div>
        <div class="col-md-1">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="contacts[${contactCount}][is_primary]"
                       value="1" onchange="makePrimary(this)">
                <label class="form-check-label">Primary</label>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeContact(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(contactDiv);
    contactCount++;
}

function removeContact(button) {
    button.closest('.contact-row').remove();
}

function makePrimary(checkbox) {
    if (checkbox.checked) {
        // Uncheck all other primary checkboxes
        document.querySelectorAll('input[name*="[is_primary]"]').forEach(cb => {
            if (cb !== checkbox) {
                cb.checked = false;
            }
        });
    }
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Set initial state based on selected customer type
    toggleCompanyFields();

    // Add one contact by default
    addContact();

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
