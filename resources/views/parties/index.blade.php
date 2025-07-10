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

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Primary Contact</th>
                                    <th>Credit Limit</th>
                                    <th>Credit Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($parties as $party)
                                    <tr>
                                        <td>{{ $party->id }}</td>
                                        <td>{{ $party->name }}</td>
                                        <td>
                                            @if ($party->primaryContact)
                                                <strong>{{ ucfirst($party->primaryContact->contact_type) }}:</strong>
                                                {{ $party->primaryContact->contact_value }}
                                                @if ($party->primaryContact->designation)
                                                    <br><small class="text-muted">{{ $party->primaryContact->designation }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">No primary contact</span>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format($party->credit_limit, 2) }}</td>
                                        <td>{{ $party->credit_days }} days</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('parties.show', $party) }}" class="btn btn-sm btn-info">View</a>
                                                <a href="{{ route('parties.edit', $party) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('parties.destroy', $party) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this party?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No parties found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $parties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
