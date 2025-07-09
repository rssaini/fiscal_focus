@extends('layouts.app_')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Journal Entries</h1>
    <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Entry
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($journalEntries->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Particular</th>
                            <th>UUID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journalEntries as $entry)
                            <tr>
                                <td>{{ $entry->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ $entry->particular }}</td>
                                <td>
                                    <code class="small">{{ substr($entry->uuid, 0, 8) }}...</code>
                                </td>
                                <td>
                                    <a href="{{ route('journal-entries.show', $entry->uuid) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $journalEntries->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-journal-whills fa-3x text-muted mb-3"></i>
                <p class="text-muted">No journal entries found. Create your first entry!</p>
                <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Entry
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
