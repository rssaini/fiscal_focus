@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Party Information Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ $party->name }}</h3>
                    <div>
                        <a href="{{ route('parties.edit', $party) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Party
                        </a>
                        <a href="{{ route('parties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $party->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Limit:</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            ₹{{ number_format($party->credit_limit, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Days:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $party->credit_days }} days
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $party->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Financial Summary</h5>
                            @if($ledgersSummary['ledger_count'] > 0)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="text-success">
                                                    <i class="fas fa-arrow-down fa-2x"></i>
                                                    <h6 class="mt-2">Receivable</h6>
                                                    <h5>₹{{ number_format($ledgersSummary['total_receivable'], 2) }}</h5>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-danger">
                                                    <i class="fas fa-arrow-up fa-2x"></i>
                                                    <h6 class="mt-2">Payable</h6>
                                                    <h5>₹{{ number_format($ledgersSummary['total_payable'], 2) }}</h5>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="{{ $ledgersSummary['net_payable'] > 0 ? 'text-danger' : 'text-success' }}">
                                                    <i class="fas fa-balance-scale fa-2x"></i>
                                                    <h6 class="mt-2">Net Balance</h6>
                                                    <h5>₹{{ number_format(abs($ledgersSummary['net_payable']), 2) }}</h5>
                                                    <small>{{ $ledgersSummary['net_payable'] > 0 ? '(We owe them)' : '(They owe us)' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No ledgers are linked to this party yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Linked Ledgers Card -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book"></i> Linked Ledgers
                        <span class="badge bg-primary ms-2">{{ $party->ledgers->count() }}</span>
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#linkLedgerModal">
                        <i class="fas fa-link"></i> Link Ledger
                    </button>
                </div>
                <div class="card-body">
                    @if($party->ledgers->count() > 0)
                        <div class="row">
                            @foreach($party->ledgers as $ledger)
                                @php $balance = $ledger->getCurrentBalance(); @endphp
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title">{{ $ledger->name }}</h6>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            Folio: {{ $ledger->folio }}<br>
                                                            Account: {{ $ledger->chartOfAccount->account_name ?? 'N/A' }}
                                                        </small>
                                                    </p>
                                                    <div class="mt-2">
                                                        <span class="badge bg-{{ $balance['type'] == 'credit' ? 'warning' : 'primary' }} fs-6">
                                                            {{ ucfirst($balance['type']) }} ₹{{ number_format($balance['balance'], 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger unlink-ledger-btn"
                                                        data-ledger-id="{{ $ledger->id }}"
                                                        data-ledger-name="{{ $ledger->name }}">
                                                    <i class="fas fa-unlink"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <a href="{{ route('ledgers.show', $ledger) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View Ledger
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No ledgers are currently linked to this party.
                            <button type="button" class="btn btn-outline-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#linkLedgerModal">
                                Link a Ledger
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions Card -->
            @if($recentTransactions->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Recent Transactions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Ledger</th>
                                    <th>Particular</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                        <td>
                                            <small class="badge bg-secondary">{{ $transaction->ledger_name }}</small>
                                        </td>
                                        <td>{{ $transaction->particular }}</td>
                                        <td class="text-end">
                                            {{ $transaction->debit > 0 ? '₹' . number_format($transaction->debit, 2) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ $transaction->credit > 0 ? '₹' . number_format($transaction->credit, 2) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-{{ $transaction->running_balance_type == 'credit' ? 'success' : 'primary' }}">
                                                {{ ucfirst($transaction->running_balance_type) }} ₹{{ number_format($transaction->running_balance, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Contacts Card -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-address-book"></i> Contacts
                        <span class="badge bg-primary ms-2">{{ $party->contacts->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($party->contacts->count() > 0)
                        <div class="row">
                            @foreach($party->contacts as $contact)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $contact->name }}</h6>
                                            <p class="card-text">
                                                @if($contact->email)
                                                    <i class="fas fa-envelope me-1"></i> {{ $contact->email }}<br>
                                                @endif
                                                @if($contact->phone)
                                                    <i class="fas fa-phone me-1"></i> {{ $contact->phone }}<br>
                                                @endif
                                                @if($contact->designation)
                                                    <i class="fas fa-briefcase me-1"></i> {{ $contact->designation }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No contacts are currently associated with this party.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('parties.edit', $party) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Party
                            </a>
                        </div>
                        <div>
                            <form action="{{ route('parties.destroy', $party) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this party and all associated contacts and ledger relationships? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete Party
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link Ledger Modal -->
<div class="modal fade" id="linkLedgerModal" tabindex="-1" aria-labelledby="linkLedgerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkLedgerModalLabel">Link Ledger to Party</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="linkLedgerForm">
                    @csrf
                    <div class="mb-3">
                        <label for="ledgerId" class="form-label">Select Ledger</label>
                        <select class="form-select" id="ledgerId" name="ledger_id" required>
                            <option value="">Loading available ledgers...</option>
                        </select>
                        <div class="form-text">Only active ledgers not already linked to this party are shown.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="linkLedgerBtn">Link Ledger</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const linkLedgerModal = document.getElementById('linkLedgerModal');
    const linkLedgerForm = document.getElementById('linkLedgerForm');
    const ledgerSelect = document.getElementById('ledgerId');
    const linkLedgerBtn = document.getElementById('linkLedgerBtn');
    const partyId = {{ $party->id }};

    // Load available ledgers when modal is shown
    linkLedgerModal.addEventListener('show.bs.modal', function() {
        fetch(`/api/parties/${partyId}/available-ledgers`)
            .then(response => response.json())
            .then(data => {
                ledgerSelect.innerHTML = '<option value="">Select a ledger</option>';
                data.forEach(ledger => {
                    const option = document.createElement('option');
                    option.value = ledger.id;
                    option.textContent = `${ledger.name} (${ledger.folio}) - ${ledger.chart_of_account}`;
                    ledgerSelect.appendChild(option);
                });

                if (data.length === 0) {
                    ledgerSelect.innerHTML = '<option value="">No available ledgers to link</option>';
                    linkLedgerBtn.disabled = true;
                } else {
                    linkLedgerBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading ledgers:', error);
                ledgerSelect.innerHTML = '<option value="">Error loading ledgers</option>';
                linkLedgerBtn.disabled = true;
            });
    });

    // Handle link ledger
    linkLedgerBtn.addEventListener('click', function() {
        const formData = new FormData(linkLedgerForm);

        fetch(`/api/parties/${partyId}/link-ledger`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated data
            } else {
                alert(data.message || 'Error linking ledger');
            }
        })
        .catch(error => {
            console.error('Error linking ledger:', error);
            alert('Error linking ledger');
        });
    });

    // Handle unlink ledger
    document.querySelectorAll('.unlink-ledger-btn').forEach(button => {
        button.addEventListener('click', function() {
            const ledgerId = this.getAttribute('data-ledger-id');
            const ledgerName = this.getAttribute('data-ledger-name');

            if (confirm(`Are you sure you want to unlink "${ledgerName}" from this party?`)) {
                const formData = new FormData();
                formData.append('ledger_id', ledgerId);

                fetch(`/api/parties/${partyId}/unlink-ledger`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload to show updated data
                    } else {
                        alert(data.message || 'Error unlinking ledger');
                    }
                })
                .catch(error => {
                    console.error('Error unlinking ledger:', error);
                    alert('Error unlinking ledger');
                });
            }
        });
    });
});
</script>
@endsection
