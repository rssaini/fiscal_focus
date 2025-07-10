@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Party</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('parties.update', $party) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Party Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $party->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="credit_limit" class="form-label">Credit Limit <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                           id="credit_limit" name="credit_limit" value="{{ old('credit_limit', $party->credit_limit) }}" required>
                                    @error('credit_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="credit_days" class="form-label">Credit Days <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('credit_days') is-invalid @enderror"
                                           id="credit_days" name="credit_days" value="{{ old('credit_days', $party->credit_days) }}" required>
                                    @error('credit_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Information <span class="text-danger">*</span></label>
                            <div id="contacts-container">
                                @foreach ($party->contacts as $index => $contact)
                                    <div class="contact-row border p-3 mb-2 rounded">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <select class="form-select @error('contacts.'.$index.'.contact_type') is-invalid @enderror"
                                                        name="contacts[{{ $index }}][contact_type]" required>
                                                    <option value="">Select Type</option>
                                                    <option value="phone" {{ old('contacts.'.$index.'.contact_type', $contact->contact_type) == 'phone' ? 'selected' : '' }}>Phone</option>
                                                    <option value="email" {{ old('contacts.'.$index.'.contact_type', $contact->contact_type) == 'email' ? 'selected' : '' }}>Email</option>
                                                    <option value="fax" {{ old('contacts.'.$index.'.contact_type', $contact->contact_type) == 'fax' ? 'selected' : '' }}>Fax</option>
                                                    <option value="address" {{ old('contacts.'.$index.'.contact_type', $contact->contact_type) == 'address' ? 'selected' : '' }}>Address</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control @error('contacts.'.$index.'.contact_value') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][contact_value]" placeholder="Contact Value"
                                                       value="{{ old('contacts.'.$index.'.contact_value', $contact->contact_value) }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control @error('contacts.'.$index.'.designation') is-invalid @enderror"
                                                       name="contacts[{{ $index }}][designation]" placeholder="Designation"
                                                       value="{{ old('contacts.'.$index.'.designation', $contact->designation) }}">
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="contacts[{{ $index }}][is_primary]"
                                                           value="1" {{ old('contacts.'.$index.'.is_primary', $contact->is_primary) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Primary</label>
                                                </div>
                                            </div>
                                            @if ($index > 0)
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeContact(this)">Remove</button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addContact()">Add More Contact</button>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('parties.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Party</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let contactIndex = {{ $party->contacts->count() }};

function addContact() {
    const container = document.getElementById('contacts-container');
    const newContact = document.createElement('div');
    newContact.className = 'contact-row border p-3 mb-2 rounded';
    newContact.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <select class="form-select" name="contacts[${contactIndex}][contact_type]" required>
                    <option value="">Select Type</option>
                    <option value="phone">Phone</option>
                    <option value="email">Email</option>
                    <option value="fax">Fax</option>
                    <option value="address">Address</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="contacts[${contactIndex}][contact_value]" placeholder="Contact Value" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="contacts[${contactIndex}][designation]" placeholder="Designation">
            </div>
            <div class="col-md-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="contacts[${contactIndex}][is_primary]" value="1">
                    <label class="form-check-label">Primary</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeContact(this)">Remove</button>
            </div>
        </div>
    `;
    container.appendChild(newContact);
    contactIndex++;
}

function removeContact(button) {
    button.closest('.contact-row').remove();
}
</script>
@endsection
