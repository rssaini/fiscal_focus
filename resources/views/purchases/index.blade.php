@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Purchase Records</h1>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Purchase
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('purchases.index') }}">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Rec No, Token No, Driver..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                       value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mines</label>
                                <select name="mines_id" class="form-control">
                                    <option value="">All Mines</option>
                                    @foreach($mines as $mine)
                                        <option value="{{ $mine->id }}" {{ request('mines_id') == $mine->id ? 'selected' : '' }}>
                                            {{ $mine->ml_number }} - {{ $mine->mines_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Vehicle</label>
                                <select name="vehicle_id" class="form-control">
                                    <option value="">All Vehicles</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->vehicle_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Use At</label>
                                <select name="use_at" class="form-control">
                                    <option value="">All Uses</option>
                                    <option value="stock" {{ request('use_at') == 'stock' ? 'selected' : '' }}>Stock</option>
                                    <option value="manufacturing" {{ request('use_at') == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
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

            <!-- Purchase Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Rec No</th>
                                    <th>Token No</th>
                                    <th>Mines</th>
                                    <th>Vehicle</th>
                                    <th>Driver</th>
                                    <th>Gross Wt</th>
                                    <th>Tare Wt</th>
                                    <th>Net Wt</th>
                                    <th>Wt (Tons)</th>
                                    <th>Commission</th>
                                    <th>Use At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->datetime->format('d/m/Y H:i') }}</td>
                                        <td><strong>{{ $purchase->rec_no }}</strong></td>
                                        <td>{{ $purchase->token_no }}</td>
                                        <td>
                                            <small>{{ $purchase->mines->ml_number }}</small>
                                            @if($purchase->mines->mines_name)
                                                <br><small class="text-muted">{{ $purchase->mines->mines_name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $purchase->vehicle->vehicle_number }}</td>
                                        <td>{{ $purchase->driver }}</td>
                                        <td>{{ $purchase->formatted_gross_wt }}</td>
                                        <td>{{ $purchase->formatted_tare_wt }}</td>
                                        <td><strong>{{ $purchase->formatted_net_wt }}</strong></td>
                                        <td><span class="badge bg-success">{{ $purchase->formatted_wt_ton }}</span></td>
                                        <td>{{ $purchase->formatted_commission }}</td>
                                        <td>{!! $purchase->use_at_badge !!}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('purchases.show', $purchase) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('purchases.edit', $purchase) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('purchases.destroy', $purchase) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this purchase record?')">
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
                                        <td colspan="13" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                                <h5>No purchase records found</h5>
                                                <p>No purchase records match your search criteria.</p>
                                                @if(request()->hasAny(['search', 'start_date', 'end_date', 'mines_id', 'vehicle_id', 'use_at']))
                                                    <a href="{{ route('purchases.index') }}" class="btn btn-primary">
                                                        <i class="fas fa-list"></i> View All Purchases
                                                    </a>
                                                @else
                                                    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Add First Purchase
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
            @if($purchases->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
