@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Party Details</h4>
                    <div>
                        <a href="{{ route('parties.edit', $party) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('parties.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $party->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $party->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Limit:</strong></td>
                                    <td>₹{{ number_format($party->credit_limit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Days:</strong></td>
                                    <td>{{ $party->credit_days }} days</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $party->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $party->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            @if ($party->contacts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Value</th>
                                                <th>Designation</th>
                                                <th>Primary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($party->contacts as $contact)
                                                <tr class="{{ $contact->is_primary ? 'table-primary' : '' }}">
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($contact->contact_type) }}</span>
                                                    </td>
                                                    <td>{{ $contact->contact_value }}</td>
                                                    <td>{{ $contact->designation ?: '-' }}</td>
                                                    <td>
                                                        @if ($contact->is_primary)
                                                            <span class="badge bg-success">Primary</span>
                                                        @else
                                                            <span class="badge bg-light text-dark">Secondary</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No contact information available.</p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h5>Contact Summary by Type</h5>
                            <div class="row">
                                @foreach (['phone', 'email', 'fax', 'address'] as $type)
                                    @php
                                        $typeContacts = $party->contacts->where('contact_type', $type);
                                    @endphp
                                    @if ($typeContacts->count() > 0)
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="mb-0">{{ ucfirst($type) }} Contacts</h6>
                                                </div>
                                                <div class="card-body">
                                                    @foreach ($typeContacts as $contact)
                                                        <div class="mb-2">
                                                            <strong>{{ $contact->contact_value }}</strong>
                                                            @if ($contact->designation)
                                                                <br><small class="text-muted">{{ $contact->designation }}</small>
                                                            @endif
                                                            @if ($contact->is_primary)
                                                                <span class="badge bg-success ms-2">Primary</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
