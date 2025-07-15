@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Mines Details: {{ $mine->ml_number }}</h1>
                <div>
                    <a href="{{ route('mines.edit', $mine) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Mines
                    </a>
                    <a href="{{ route('mines.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Mines
                    </a>
                </div>
            </div>

            <!-- Mines Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Mines Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 150px;">ML Number:</td>
                                    <td><strong>{{ $mine->ml_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Mines Name:</td>
                                    <td>
                                        @if($mine->mines_name)
                                            {{ $mine->mines_name }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Owner Name:</td>
                                    <td>
                                        @if($mine->owner_name)
                                            {{ $mine->owner_name }}
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>{!! $mine->status_badge !!}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 150px;">Created:</td>
                                    <td>{{ $mine->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($mine->updated_at != $mine->created_at)
                                <tr>
                                    <td class="fw-bold">Last Updated:</td>
                                    <td>{{ $mine->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold">Display Name:</td>
                                    <td>{{ $mine->display_name }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($mine->notes)
                        <div class="mt-4">
                            <h6 class="fw-bold">Notes:</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ $mine->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    @if($mine->email || $mine->phone || $mine->mobile)
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    @if($mine->email)
                                    <tr>
                                        <td class="fw-bold" style="width: 150px;">Email:</td>
                                        <td>
                                            <a href="mailto:{{ $mine->email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope me-1"></i>{{ $mine->email }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($mine->phone)
                                    <tr>
                                        <td class="fw-bold">Phone:</td>
                                        <td>
                                            <a href="tel:{{ $mine->phone }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-1"></i>{{ $mine->phone }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                    @if($mine->mobile)
                                    <tr>
                                        <td class="fw-bold">Mobile:</td>
                                        <td>
                                            <a href="tel:{{ $mine->mobile }}" class="text-decoration-none">
                                                <i class="fas fa-mobile-alt me-1"></i>{{ $mine->mobile }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if($mine->primary_contact_method != 'No contact info')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Primary Contact:</strong> {{ $mine->primary_contact_method }}
                                </div>
                                @endif

                                @if($mine->contact_info != 'No contact information')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Quick Contact:</strong> {{ $mine->contact_info }}
                                </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-address-book fa-3x mb-3 opacity-50"></i>
                            <h6>No contact information available</h6>
                            <p class="mb-3">Contact information has not been provided for this mines.</p>
                            <a href="{{ route('mines.edit', $mine) }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Add Contact Information
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('mines.edit', $mine) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Mines Information
                                </a>
                                @if($mine->email)
                                <a href="mailto:{{ $mine->email }}" class="btn btn-outline-info">
                                    <i class="fas fa-envelope me-2"></i>Send Email
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                @if($mine->mobile)
                                <a href="tel:{{ $mine->mobile }}" class="btn btn-outline-success">
                                    <i class="fas fa-mobile-alt me-2"></i>Call Mobile: {{ $mine->mobile }}
                                </a>
                                @endif
                                @if($mine->phone)
                                <a href="tel:{{ $mine->phone }}" class="btn btn-outline-primary">
                                    <i class="fas fa-phone me-2"></i>Call Phone: {{ $mine->phone }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!$mine->email && !$mine->phone && !$mine->mobile)
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
