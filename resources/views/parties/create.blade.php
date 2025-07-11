@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Party</h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('parties.store') }}" method="POST">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Basic Information</h5>
                                <hr>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Party Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="credit_limit" class="form-label">Credit Limit <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                           id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}" required>
                                    @error('credit_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="credit_days" class="form-label">Credit Days <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                           id="credit_days" name="credit_days" value="{{ old('credit_days', 0) }}" required>
                                    @error('credit_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Contact Information <span class="text-danger">*</span></h5>
                                <hr>
                            </div>
                        </div>

                        <div id="contacts-container">
                            <div class="contact-row border p-3 mb-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('contacts.0.name') is-invalid @enderror"
                                                   name="contacts[0][name]" value="{{ old('contacts.0.name') }}" required>
                                            @error('contacts.0.name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Designation</label>
                                            <input type="text" class="form-control @error('contacts.0.designation') is-invalid @enderror"
                                                   name="contacts[0][designation]" value="{{ old('contacts.0.designation') }}" placeholder="Manager, Director, etc.">
                                            @error('contacts.0.designation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control @error('contacts.0.email') is-invalid @enderror"
                                                   name="contacts[0][email]" value="{{ old('contacts.0.email') }}">
                                            @error('contacts.0.email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control @error('contacts.0.phone') is-invalid @enderror"
                                                   name="contacts[0][phone]" value="{{ old('contacts.0.phone') }}">
                                            @error('contacts.0.phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Mobile</label>
                                            <input type="text" class="form-control @error('contacts.0.mobile') is-invalid @enderror"
                                                   name="contacts[0][mobile]" value="{{ old('contacts.0.mobile') }}">
                                            @error('contacts.0.mobile')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="contacts[0][is_primary]"
                                                   id="is_primary_0" value="1" {{ old('contacts.0.is_primary') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_primary_0">
                                                Primary Contact
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-contact-btn" disabled>
                                                Remove Contact
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control @error('contacts.0.notes') is-invalid @enderror"
                                                      name="contacts[0][notes]" rows="2">{{ old('contacts.0.notes') }}</textarea>
                                            @error('contacts.0.notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-primary" id="add-contact-btn">
                                <i class="fas fa-plus"></i> Add Another Contact
                            </button>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('parties.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Party</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let contactIndex = 1;

    // Add contact functionality
    document.getElementById('add-contact-btn').addEventListener('click', function() {
        const container = document.getElementById('contacts-container');
        const newContact = createContactRow(contactIndex);
        container.appendChild(newContact);
        contactIndex++;
        updateRemoveButtons();
    });

    // Remove contact functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-contact-btn')) {
            e.target.closest('.contact-row').remove();
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
                        <input type="text" class="form-control" name="contacts[${index}][designation]" placeholder="Manager, Director, etc.">
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
                        <input class="form-check-input" type="checkbox" name="contacts[${index}][is_primary]"
                               id="is_primary_${index}" value="1">
                        <label class="form-check-label" for="is_primary_${index}">
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
                        <textarea class="form-control" name="contacts[${index}][notes]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        `;
        return div;
    }

    function updateRemoveButtons() {
        const contactRows = document.querySelectorAll('.contact-row');
        const removeButtons = document.querySelectorAll('.remove-contact-btn');

        removeButtons.forEach((btn, index) => {
            btn.disabled = contactRows.length === 1;
        });
    }

    // Initialize remove buttons state
    updateRemoveButtons();
});
</script>
@endsection
