@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Consignee Management</h1>
        <p class="text-muted mb-0">Manage consignee information and details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('consignees.export', request()->all()) }}" class="btn btn-outline-success">
            <i class="fas fa-download"></i> Export CSV
        </a>
        <a href="{{ route('consignees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Consignee
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('consignees.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="city" class="form-label">City</label>
                <select class="form-select" name="city" id="city">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="state" class="form-label">State</label>
                <select class="form-select" name="state" id="state">
                    <option value="">All States</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" name="search" id="search"
                       value="{{ request('search') }}" placeholder="Search by name, GSTIN, address...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('consignees.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Consignees Table -->
<div class="card">
    <div class="card-body">
        @if($consignees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Consignee Name</th>
                            <th>GSTIN</th>
                            <th>Address</th>
                            <th>City</th>
                            <th>State</th>
                            <th>ZIP</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consignees as $consignee)
                        <tr>
                            <td>{{ $consignee->id }}</td>
                            <td>
                                <strong>{{ $consignee->consignee_name }}</strong>
                            </td>
                            <td>
                                <span class="text-muted">{{ $consignee->formatted_gstin }}</span>
                            </td>
                            <td>
                                <small>{{ Str::limit($consignee->address, 40) }}</small>
                                @if($consignee->address2)
                                    <br><small class="text-muted">{{ Str::limit($consignee->address2, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $consignee->city }}</td>
                            <td>{{ $consignee->state }}</td>
                            <td>{{ $consignee->zip }}</td>
                            <td>{!! $consignee->status_badge !!}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('consignees.show', $consignee) }}"
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('consignees.edit', $consignee) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('consignees.destroy', $consignee) }}"
                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this consignee?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $consignees->firstItem() }} to {{ $consignees->lastItem() }} of {{ $consignees->total() }} results
                </div>
                <div>
                    {{ $consignees->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                <p class="text-muted">No consignees found.</p>
                <a href="{{ route('consignees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Consignee
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Summary Cards -->
@if($consignees->count() > 0)
    @php
        $allConsignees = \App\Models\Consignee::selectRaw('
            status,
            COUNT(*) as count
        ')->groupBy('status')->get();

        $summary = [];
        foreach($allConsignees as $item) {
            $summary[$item->status] = $item->count;
        }
    @endphp

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title">Total Consignees</h6>
                    <h4 class="text-primary">{{ $consignees->total() }}</h4>
                    <small class="text-muted">All registered consignees</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title">Active Consignees</h6>
                    <h4 class="text-success">{{ $summary['active'] ?? 0 }}</h4>
                    <small class="text-muted">Currently active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h6 class="card-title">Inactive Consignees</h6>
                    <h4 class="text-secondary">{{ $summary['inactive'] ?? 0 }}</h4>
                    <small class="text-muted">Currently inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="card-title">Unique Cities</h6>
                    <h4 class="text-info">{{ $cities->count() }}</h4>
                    <small class="text-muted">Cities covered</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
