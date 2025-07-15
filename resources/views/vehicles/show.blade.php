@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Vehicle Details: {{ $vehicle->vehicle_number }}</h1>
                <div>
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Vehicle
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Vehicles
                    </a>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Vehicle Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 150px;">Vehicle Number:</td>
                                    <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tare Weight:</td>
                                    <td>{{ $vehicle->formatted_tare_weight }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Load Capacity:</td>
                                    <td>{{ $vehicle->formatted_load_capacity }}</td>
                                </tr>
                                @if($vehicle->gross_weight)
                                <tr>
                                    <td class="fw-bold">Gross Weight:</td>
                                    <td>
                                        <span class="badge bg-info">{{ $vehicle->formatted_gross_weight }}</span>
                                        <small class="text-muted d-block">Tare + Load Capacity</small>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>{!! $vehicle->status_badge !!}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 150px;">Created:</td>
                                    <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($vehicle->updated_at != $vehicle->created_at)
                                <tr>
                                    <td class="fw-bold">Last Updated:</td>
                                    <td>{{ $vehicle->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Total Contacts:</td>
                                    <td>{{ $vehicle->contacts->count() }}</td>
                                </tr>
                                @if($vehicle->primaryContact)
                                <tr>
                                    <td class="fw-bold">Primary Contact:</td>
                                    <td>{{ $vehicle->primaryContact->name }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($vehicle->notes)
                        <div class="mt-4">
                            <h6 class="fw-bold">Notes:</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $vehicle->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Persons -->
            @if($vehicle->contacts->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact Persons</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Contact Info</th>
                                        <th>Primary</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicle->contacts as $contact)
                                        <tr>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->designation ?: '-' }}</td>
                                            <td>{{ $contact->display_contact ?: '-' }}</td>
                                            <td>
                                                @if($contact->is_primary)
                                                    <span class="badge bg-primary">Primary</span>
                                                @else
                                                    <span class="badge bg-secondary">Secondary</span>
                                                @endif
                                            </td>
                                            <td>{{ $contact->notes ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact Persons</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-address-book fa-3x mb-3 opacity-50"></i>
                            <h6>No contact information available</h6>
                            <p class="mb-3">Contact information has not been added for this vehicle.</p>
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Add Contact Information
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Vehicle Information
                                </a>
                                @if($vehicle->primaryContact && $vehicle->primaryContact->email)
                                <a href="mailto:{{ $vehicle->primaryContact->email }}" class="btn btn-outline-info">
                                    <i class="fas fa-envelope me-2"></i>Send Email to {{ $vehicle->primaryContact->name }}
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                @if($vehicle->primaryContact && $vehicle->primaryContact->mobile)
                                <a href="tel:{{ $vehicle->primaryContact->mobile }}" class="btn btn-outline-success">
                                    <i class="fas fa-mobile-alt me-2"></i>Call Mobile: {{ $vehicle->primaryContact->mobile }}
                                </a>
                                @endif
                                @if($vehicle->primaryContact && $vehicle->primaryContact->phone)
                                <a href="tel:{{ $vehicle->primaryContact->phone }}" class="btn btn-outline-primary">
                                    <i class="fas fa-phone me-2"></i>Call Phone: {{ $vehicle->primaryContact->phone }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!$vehicle->primaryContact || (!$vehicle->primaryContact->email && !$vehicle->primaryContact->mobile && !$vehicle->primaryContact->phone))
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No contact methods available.</strong> Add contact information to enable quick communication.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
