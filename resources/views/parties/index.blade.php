@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Parties Management</h3>
                    <a href="{{ route('parties.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Party
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Credit Limit</th>
                                    <th>Credit Days</th>
                                    <th>Contacts</th>
                                    <th>Linked Ledgers</th>
                                    <th>Financial Summary</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parties as $party)
                                    <tr>
                                        <td>
                                            <strong>{{ $party->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                ₹{{ number_format($party->credit_limit, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $party->credit_days }} days
                                            </span>
                                        </td>
                                        <td>
                                            @if($party->contacts->count() > 0)
                                                <span class="badge bg-success">
                                                    {{ $party->contacts->count() }} contact{{ $party->contacts->count() > 1 ? 's' : '' }}
                                                </span>
                                            @else
                                                <span class="text-muted">No contacts</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($party->ledgers->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($party->ledgers->take(3) as $ledger)
                                                        @php $balance = $ledger->getCurrentBalance(); @endphp
                                                        <span class="badge bg-{{ $balance['type'] == 'credit' ? 'warning' : 'primary' }}"
                                                              title="{{ $ledger->name }}: {{ ucfirst($balance['type']) }} {{ number_format($balance['balance'], 2) }}">
                                                            {{ $ledger->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($party->ledgers->count() > 3)
                                                        <span class="badge bg-secondary">
                                                            +{{ $party->ledgers->count() - 3 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Total: {{ $party->ledgers->count() }} ledger{{ $party->ledgers->count() > 1 ? 's' : '' }}
                                                </small>
                                            @else
                                                <span class="text-muted">No linked ledgers</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $summary = $party->ledgers_summary; @endphp
                                            @if($summary['ledger_count'] > 0)
                                                <div class="small">
                                                    <div class="text-success">
                                                        <i class="fas fa-arrow-down"></i> Receivable: ₹{{ number_format($summary['total_receivable'], 2) }}
                                                    </div>
                                                    <div class="text-danger">
                                                        <i class="fas fa-arrow-up"></i> Payable: ₹{{ number_format($summary['total_payable'], 2) }}
                                                    </div>
                                                    <div class="fw-bold {{ $summary['net_payable'] > 0 ? 'text-danger' : 'text-success' }}">
                                                        Net: ₹{{ number_format(abs($summary['net_payable']), 2) }}
                                                        {{ $summary['net_payable'] > 0 ? '(Payable)' : '(Receivable)' }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No financial data</span>
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
                                                            onclick="return confirm('Are you sure you want to delete this party and all associated contacts and ledger relationships? This action cannot be undone.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                            <h5>No parties found</h5>
                                            <p>Create your first party to get started.</p>
                                            <a href="{{ route('parties.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add New Party
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
