@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Add New Purchase Record</h1>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Purchases
                </a>
            </div>

            <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form">
                @csrf

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('datetime') is-invalid @enderror"
                                           name="datetime" value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('datetime')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mines <span class="text-danger">*</span></label>
                                    <select class="form-control @error('mines_id') is-invalid @enderror"
                                            name="mines_id" required>
                                        <option value="">Select Mines</option>
                                        @foreach($mines as $mine)
                                            <option value="{{ $mine->id }}" {{ old('mines_id') == $mine->id ? 'selected' : '' }}>
                                                {{ $mine->ml_number }}{{ $mine->mines_name ? ' - ' . $mine->mines_name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mines_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Rec No <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('rec_no') is-invalid @enderror"
                                                   name="rec_no" value="{{ old('rec_no') }}" required
                                                   placeholder="e.g., REC-001">
                                            @error('rec_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Token No <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('token_no') is-invalid @enderror"
                                                   name="token_no" value="{{ old('token_no') }}" required
                                                   placeholder="e.g., TKN-001">
                                            @error('token_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-control @error('vehicle_id') is-invalid @enderror"
                                            name="vehicle_id" id="vehicle_id" required>
                                        <option value="">Select Vehicle</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->vehicle_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Driver <span class="text-danger">*</span></label>
                                    <select class="form-control @error('driver') is-invalid @enderror"
                                            name="driver" id="driver" required>
                                        <option value="">Select Vehicle First</option>
                                    </select>
                                    @error('driver')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Weight & Commission Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Gross Weight (kg) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('gross_wt') is-invalid @enderror"
                                                   name="gross_wt" id="gross_wt" value="{{ old('gross_wt') }}" required
                                                   placeholder="e.g., 25000">
                                            @error('gross_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tare Weight (kg) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('tare_wt') is-invalid @enderror"
                                                   name="tare_wt" id="tare_wt" value="{{ old('tare_wt') }}" required
                                                   placeholder="e.g., 8500">
                                            @error('tare_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Net Weight (kg)</label>
                                            <input type="number" class="form-control" id="net_wt" readonly
                                                   placeholder="Auto calculated">
                                            <div class="form-text">Automatically calculated (Gross - Tare)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Weight (Tons)</label>
                                            <input type="text" class="form-control" id="wt_ton" readonly
                                                   placeholder="Auto calculated">
                                            <div class="form-text">Automatically calculated (Net ÷ 1000)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Commission (₹)</label>
                                    <input type="number" step="0.01" class="form-control @error('commission') is-invalid @enderror"
                                           name="commission" value="{{ old('commission') }}"
                                           placeholder="e.g., 5000.00">
                                    @error('commission')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Use At <span class="text-danger">*</span></label>
                                    <select class="form-control @error('use_at') is-invalid @enderror"
                                            name="use_at" required>
                                        <option value="manufacturing" {{ old('use_at', 'manufacturing') == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                        <option value="stock" {{ old('use_at') == 'stock' ? 'selected' : '' }}>Stock</option>
                                    </select>
                                    @error('use_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      name="notes" rows="3"
                                      placeholder="Additional notes about this purchase...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Purchase Record
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const driverSelect = document.getElementById('driver');
    const grossWtInput = document.getElementById('gross_wt');
    const tareWtInput = document.getElementById('tare_wt');
    const netWtInput = document.getElementById('net_wt');
    const wtTonInput = document.getElementById('wt_ton');

    // Load drivers when vehicle is selected
    vehicleSelect.addEventListener('change', function() {
        const vehicleId = this.value;

        if (vehicleId) {
            fetch(`/api/vehicles/${vehicleId}/drivers`)
                .then(response => response.json())
                .then(data => {
                    driverSelect.innerHTML = '<option value="">Select Driver</option>';

                    if (data.drivers && data.drivers.length > 0) {
                        data.drivers.forEach(driver => {
                            const option = document.createElement('option');
                            option.value = driver;
                            option.textContent = driver;
                            driverSelect.appendChild(option);
                        });
                    } else {
                        driverSelect.innerHTML = '<option value="">No drivers found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching drivers:', error);
                    driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                });
        } else {
            driverSelect.innerHTML = '<option value="">Select Vehicle First</option>';
        }
    });

    // Auto calculate weights
    function calculateWeights() {
        const grossWt = parseFloat(grossWtInput.value) || 0;
        const tareWt = parseFloat(tareWtInput.value) || 0;

        if (grossWt > 0 && tareWt > 0) {
            const netWt = grossWt - tareWt;
            const wtTon = (netWt / 1000).toFixed(2);

            netWtInput.value = netWt;
            wtTonInput.value = wtTon + ' Tons';
        } else {
            netWtInput.value = '';
            wtTonInput.value = '';
        }
    }

    grossWtInput.addEventListener('input', calculateWeights);
    tareWtInput.addEventListener('input', calculateWeights);

    // Initial calculation if values are already filled
    calculateWeights();
});
</script>
@endpush
@endsection
