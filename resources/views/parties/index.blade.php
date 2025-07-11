@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Party Management</h4>
                    <a href="{{ route('parties.create') }}" class="btn btn-primary">Add New Party</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Search and Filter Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('parties.index') }}" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search by name, contact name, email, phone..."
                                           value="{{ request('search') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Entity Type</label>
                                    <select name="entity_type" class="form-select">
                                        <option value="">All Entity Types</option>
                                        @foreach($entityTypes as $class => $label)
                                            <option value="{{ $class }}" {{ request('entity_type') == $class ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Min Credit</label>
                                    <input type="number" name="credit_limit_min" class="form-control"
                                           placeholder="Min" step="0.01" value="{{ request('credit_limit_min') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Max Credit</label>
                                    <input type="number" name="credit_limit_max" class="form-control"
                                           placeholder="Max" step="0.01" value="{{ request('credit_limit_max') }}">
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-outline-secondary" type="submit" title="Search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="{{ route('parties.index') }}" class="btn btn-outline-danger" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    @if(request()->hasAny(['search', 'entity_type', 'credit_limit_min', 'credit_limit_max']))
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-filter me-2"></i>
                                    Showing filtered results: {{ $parties->total() }} parties found
                                    @if(request('search'))
                                        | Search: "{{ request('search') }}"
                                    @endif
                                    @if(request('entity_type'))
                                        | Entity Type: {{ $entityTypes[request('entity_type')] ?? request('entity_type') }}
                                    @endif
                                    @if(request('credit_limit_min') || request('credit_limit_max'))
                                        | Credit Range: ₹{{ number_format(request('credit_limit_min', 0), 2) }} - ₹{{ number_format(request('credit_limit_max', 999999), 2) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Primary Contact</th>
                                    <th>Credit Info</th>
                                    <th>Linked Entities</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($parties as $party)
                                    <tr>
                                        <td>{{ $party->id }}</td>
                                        <td>
                                            <strong>{{ $party->name }}</strong>
                                            <br><small class="text-muted">{{ $party->contacts->count() }} contact(s)</small>
                                        </td>
                                        <td>
                                            @if ($party->primaryContact)
                                                <strong>{{ $party->primaryContact->name }}</strong>
                                                @if ($party->primaryContact->designation)
                                                    <br><small class="text-muted">{{ $party->primaryContact->designation }}</small>
                                                @endif
                                                <br><small class="text-primary">{{ $party->primaryContact->display_contact }}</small>
                                            @else
                                                <span class="text-muted">No primary contact</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>₹{{ number_format($party->credit_limit, 2) }}</strong>
                                            <br><small class="text-muted">{{ $party->credit_days }} days</small>
                                        </td>
                                        <td>
                                            @if($party->entityRelationships->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @php
                                                        $entityCounts = $party->getEntityCountByType();
                                                    @endphp
                                                    @foreach($entityCounts as $modelType => $count)
                                                        @php
                                                            $displayType = str_replace('App\\Models\\', '', $modelType);
                                                            $badgeClass = match($displayType) {
                                                                'Customer' => 'bg-primary',
                                                                'Supplier' => 'bg-success',
                                                                'Employee' => 'bg-info',
                                                                default => 'bg-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}" title="{{ $displayType }}">
                                                            {{ $count }} {{ $displayType }}{{ $count > 1 ? 's' : '' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Total: {{ $party->total_entities_count }} entities
                                                </small>
                                            @else
                                                <span class="text-muted">No linked entities</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('parties.show', $party) }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('parties.edit', $party) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('parties.destroy', $party) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this party and all associated contacts and entity relationships?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            @if(request()->hasAny(['search', 'entity_type', 'credit_limit_min', 'credit_limit_max']))
                                                <div class="text-muted">
                                                    <i class="fas fa-search fa-2x mb-2"></i>
                                                    <p>No parties found matching your search criteria.</p>
                                                    <a href="{{ route('parties.index') }}" class="btn btn-outline-primary">
                                                        View All Parties
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-2x mb-2"></i>
                                                    <p>No parties found. Get started by creating your first party.</p>
                                                    <a href="{{ route('parties.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Add New Party
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($parties->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $parties->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
