@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Vehicle Master</h1>
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Vehicle
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('vehicles.index') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by Vehicle Number..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Vehicles Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Vehicle Number</th>
                                    <th>Tare Weight</th>
                                    <th>Load Capacity</th>
                                    <th>Gross Weight</th>
                                    <th>Primary Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicles as $vehicle)
                                    <tr>
                                        <td><strong>{{ $vehicle->vehicle_number }}</strong></td>
                                        <td>{{ $vehicle->formatted_tare_weight }}</td>
                                        <td>{{ $vehicle->formatted_load_capacity }}</td>
                                        <td>
                                            @if($vehicle->gross_weight)
                                                <span class="badge bg-info">{{ $vehicle->formatted_gross_weight }}</span>
                                            @else
                                                <span class="text-muted">Not calculated</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vehicle->primaryContact)
                                                <strong>{{ $vehicle->primaryContact->name }}</strong>
                                                @if($vehicle->primaryContact->designation)
                                                    <br><small class="text-muted">{{ $vehicle->primaryContact->designation }}</small>
                                                @endif
                                                @if($vehicle->primaryContact->primary_contact_method)
                                                    <br><small>{{ $vehicle->primaryContact->primary_contact_method }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No contact</span>
                                            @endif
                                        </td>
                                        <td>{!! $vehicle->status_badge !!}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('vehicles.show', $vehicle) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('vehicles.edit', $vehicle) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('vehicles.destroy', $vehicle) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-truck fa-3x mb-3"></i>
                                                <h5>No vehicles found</h5>
                                                <p>No vehicles match your search criteria.</p>
                                                @if(request()->hasAny(['search', 'status']))
                                                    <a href="{{ route('vehicles.index') }}" class="btn btn-primary">
                                                        <i class="fas fa-list"></i> View All Vehicles
                                                    </a>
                                                @else
                                                    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Add First Vehicle
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($vehicles->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $vehicles->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
