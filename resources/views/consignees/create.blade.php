@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add New Consignee</h4>
                    <a href="{{ route('consignees.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('consignees.store') }}" method="POST" id="consigneeForm">
                    @csrf

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Basic Information</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="consignee_name" class="form-label">Consignee Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('consignee_name') is-invalid @enderror"
                                   id="consignee_name" name="consignee_name" value="{{ old('consignee_name') }}" required
                                   placeholder="Enter consignee name">
                            @error('consignee_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror"
                                   id="gstin" name="gstin" value="{{ old('gstin') }}"
                                   placeholder="22AAAAA0000A1Z5" maxlength="15">
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format: 22AAAAA0000A1Z5 (Optional)</div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Address Information</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3" required
                                      placeholder="Enter street address">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="address2" class="form-label">Address 2</label>
                            <textarea class="form-control @error('address2') is-invalid @enderror"
                                      id="address2" name="address2" rows="3"
                                      placeholder="Additional address information (optional)">{{ old('address2') }}</textarea>
                            @error('address2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city') }}" required
                                   placeholder="Enter city name">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror"
                                   id="state" name="state" value="{{ old('state') }}" required
                                   placeholder="Enter state name">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="zip" class="form-label">ZIP Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('zip') is-invalid @enderror"
                                   id="zip" name="zip" value="{{ old('zip') }}" required
                                   placeholder="Enter ZIP code" maxlength="10">
                            @error('zip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('consignees.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Consignee
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // GSTIN validation
        const gstinInput = document.getElementById('gstin');
        if (gstinInput) {
            gstinInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // Form validation
        const form = document.getElementById('consigneeForm');
        form.addEventListener('submit', function(e) {
            const gstin = gstinInput.value.trim();
            if (gstin && gstin.length !== 15) {
                e.preventDefault();
                alert('GSTIN must be exactly 15 characters long');
                gstinInput.focus();
                return false;
            }
        });

        // Auto-capitalize city and state
        const cityInput = document.getElementById('city');
        const stateInput = document.getElementById('state');

        [cityInput, stateInput].forEach(input => {
            input.addEventListener('blur', function() {
                this.value = this.value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            });
        });
    });
</script>
@endpush
@endsection
