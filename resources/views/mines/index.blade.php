@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Mines Master</h1>
                <a href="{{ route('mines.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Mines
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('mines.index') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by ML Number, Mines Name, Owner Name, Contact..."
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
                                <a href="{{ route('mines.index') }}" class="btn btn-secondary">
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

            <!-- Mines Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ML Number</th>
                                    <th>Mines Name</th>
                                    <th>Owner Name</th>
                                    <th>Contact Info</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mines as $mine)
                                    <tr>
                                        <td><strong>{{ $mine->ml_number }}</strong></td>
                                        <td>
                                            @if($mine->mines_name)
                                                {{ $mine->mines_name }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($mine->owner_name)
                                                {{ $mine->owner_name }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($mine->contact_info != 'No contact information')
                                                <small>{{ $mine->contact_info }}</small>
                                            @else
                                                <span class="text-muted">No contact</span>
                                            @endif
                                        </td>
                                        <td>{!! $mine->status_badge !!}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('mines.show', $mine) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('mines.edit', $mine) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('mines.destroy', $mine) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this mines?')">
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
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-mountain fa-3x mb-3"></i>
                                                <h5>No mines found</h5>
                                                <p>No mines match your search criteria.</p>
                                                @if(request()->hasAny(['search', 'status']))
                                                    <a href="{{ route('mines.index') }}" class="btn btn-primary">
                                                        <i class="fas fa-list"></i> View All Mines
                                                    </a>
                                                @else
                                                    <a href="{{ route('mines.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Add First Mines
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
            @if($mines->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $mines->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
