@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Vehicle: {{ $vehicle->vehicle_number }}</h1>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                </a>
            </div>

            <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror"
                                           name="vehicle_number" value="{{ old('vehicle_number', $vehicle->vehicle_number) }}" required
                                           placeholder="e.g., HR-55-AB-1234">
                                    @error('vehicle_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status', $vehicle->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $vehicle->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tare Weight (kg)</label>
                                    <input type="number" step="0.01" class="form-control @error('tare_weight') is-invalid @enderror"
                                           name="tare_weight" value="{{ old('tare_weight', $vehicle->tare_weight) }}"
                                           placeholder="e.g., 8500.00">
                                    @error('tare_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Empty weight of the vehicle in kilograms</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Load Capacity (kg)</label>
                                    <input type="number" step="0.01" class="form-control @error('load_capacity') is-invalid @enderror"
                                           name="load_capacity" value="{{ old('load_capacity', $vehicle->load_capacity) }}"
                                           placeholder="e.g., 16000.00">
                                    @error('load_capacity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Maximum load the vehicle can carry in kilograms</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              name="notes" rows="3"
                                              placeholder="Additional notes about the vehicle...">{{ old('notes', $vehicle->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Contact Information <span class="text-danger">*</span></h5>
                        <button type="button" class="btn btn-sm btn-primary" id="add-contact-btn">
                            <i class="fas fa-plus"></i> Add Contact
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="contacts-container">
                            @foreach ($vehicle->contacts as $index => $contact)
                                <div class="contact-row border p-3 mb-3 rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('contacts.'.$index.'.name') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][name]" value="{{ old('contacts.'.$index.'.name', $contact->name) }}" required>
                                                @error('contacts.'.$index.'.name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Designation</label>
                                                <input type="text" class="form-control @error('contacts.'.$index.'.designation') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][designation]" value="{{ old('contacts.'.$index.'.designation', $contact->designation) }}"
                                                       placeholder="Driver, Owner, etc.">
                                                @error('contacts.'.$index.'.designation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control @error('contacts.'.$index.'.email') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][email]" value="{{ old('contacts.'.$index.'.email', $contact->email) }}">
                                                @error('contacts.'.$index.'.email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" class="form-control @error('contacts.'.$index.'.phone') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][phone]" value="{{ old('contacts.'.$index.'.phone', $contact->phone) }}">
                                                @error('contacts.'.$index.'.phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Mobile</label>
                                                <input type="text" class="form-control @error('contacts.'.$index.'.mobile') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][mobile]" value="{{ old('contacts.'.$index.'.mobile', $contact->mobile) }}">
                                                @error('contacts.'.$index.'.mobile')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="contacts[{{ $index }}][is_primary]"
                                                       id="is_primary_{{ $index }}" value="1"
                                                       {{ old('contacts.'.$index.'.is_primary', $contact->is_primary) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_primary_{{ $index }}">
                                                    Primary Contact
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-contact-btn">
                                                    Remove Contact
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea class="form-control @error('contacts.'.$index.'.notes') is-invalid @enderror"
                                                          name="contacts[{{ $index }}][notes]" rows="2">{{ old('contacts.'.$index.'.notes', $contact->notes) }}</textarea>
                                                @error('contacts.'.$index.'.notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Vehicle
                        </button>
                        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
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
    let contactIndex = {{ $vehicle->contacts->count() }};

    // Add contact functionality
    document.getElementById('add-contact-btn').addEventListener('click', function() {
        const container = document.getElementById('contacts-container');
        const newContactRow = createContactRow(contactIndex);
        container.appendChild(newContactRow);
        contactIndex++;
        updateRemoveButtons();
    });

    // Remove contact functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-contact-btn') || e.target.parentNode.classList.contains('remove-contact-btn')) {
            const contactRow = e.target.closest('.contact-row');
            contactRow.remove();
            updateRemoveButtons();
        }
    });

    function createContactRow(index) {
        const div = document.createElement('div');
        div.className = 'contact-row border p-3 mb-3 rounded';
        div.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="contacts[${index}][name]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" class="form-control" name="contacts[${index}][designation]" placeholder="Driver, Owner, etc.">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="contacts[${index}][email]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="contacts[${index}][phone]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" name="contacts[${index}][mobile]">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="contacts[${index}][is_primary]" id="is_primary_${index}" value="1">
                        <label class="form-check-label" for="is_primary_${index}">Primary Contact</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-contact-btn">Remove Contact</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="contacts[${index}][notes]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        `;
        return div;
    }

    function updateRemoveButtons() {
        const contactRows = document.querySelectorAll('.contact-row');
        contactRows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-contact-btn');
            if (contactRows.length > 1) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    // Initialize remove buttons
    updateRemoveButtons();
});
</script>
@endpush
@endsection
