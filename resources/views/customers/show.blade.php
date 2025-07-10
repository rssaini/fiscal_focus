@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>{{ $customer->display_name }}</h1>
        <p class="text-muted mb-0">
            Customer Code: <strong>{{ $customer->customer_code }}</strong> |
            {!! $customer->status_badge !!}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        @if($customer->ledger)
            <a href="{{ route('ledgers.show', $customer->ledger) }}" class="btn btn-info">
                <i class="fas fa-book"></i> View Ledger
            </a>
            <a href="{{ route('customers.statement', $customer) }}" class="btn btn-success">
                <i class="fas fa-file-alt"></i> Statement
            </a>
        @endif
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i> More
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                    <i class="fas fa-upload"></i> Upload Document
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCustomer()">
                    <i class="fas fa-trash"></i> Delete Customer
                </a></li>
            </ul>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Customer Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-{{ $summary['current_balance']['type'] == 'debit' ? 'danger' : 'success' }}">
            <div class="card-body text-center">
                <h6 class="card-title">Current Balance</h6>
                <h4 class="text-{{ $summary['current_balance']['type'] == 'debit' ? 'danger' : 'success' }}">
                    ₹{{ number_format($summary['current_balance']['balance'], 2) }}
                </h4>
                <small class="text-muted">{{ ucfirst($summary['current_balance']['type']) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h6 class="card-title">Outstanding Amount</h6>
                <h4 class="text-warning">₹{{ number_format($summary['outstanding_amount'], 2) }}</h4>
                <small class="text-muted">Amount due</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h6 class="card-title">Credit Limit</h6>
                <h4 class="text-info">₹{{ number_format($customer->credit_limit, 2) }}</h4>
                <small class="text-muted">
                    {{ $customer->credit_limit > 0 ? number_format($summary['credit_utilization'], 1) . '% used' : 'No limit set' }}
                </small>
                @if($summary['is_over_limit'])
                    <div><span class="badge bg-danger">Over Limit</span></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h6 class="card-title">Advance Amount</h6>
                <h4 class="text-primary">₹{{ number_format($summary['advance_amount'], 2) }}</h4>
                <small class="text-muted">Prepaid balance</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Customer Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Customer Type:</td>
                                <td>
                                    <span class="badge bg-{{ $customer->customer_type == 'business' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($customer->customer_type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Name:</td>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            @if($customer->company_name)
                                <tr>
                                    <td class="fw-bold">Company:</td>
                                    <td>{{ $customer->company_name }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td>
                                    @if($customer->email)
                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Mobile:</td>
                                <td>
                                    @if($customer->mobile)
                                        <a href="tel:{{ $customer->mobile }}">{{ $customer->mobile }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </td>
                            </tr>
                            @if($customer->phone)
                                <tr>
                                    <td class="fw-bold">Phone:</td>
                                    <td><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            @if($customer->customer_type == 'business')
                                @if($customer->gstin)
                                    <tr>
                                        <td class="fw-bold">GSTIN:</td>
                                        <td>{{ $customer->gstin }}</td>
                                    </tr>
                                @endif
                                @if($customer->pan)
                                    <tr>
                                        <td class="fw-bold">PAN:</td>
                                        <td>{{ $customer->pan }}</td>
                                    </tr>
                                @endif
                                @if($customer->website)
                                    <tr>
                                        <td class="fw-bold">Website:</td>
                                        <td><a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a></td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td class="fw-bold">Credit Limit:</td>
                                <td>₹{{ number_format($customer->credit_limit, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Credit Days:</td>
                                <td>{{ $customer->credit_days }} days</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>{!! $customer->status_badge !!}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td>{{ $customer->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mt-3">
                    <h6>Billing Address:</h6>
                    <p class="text-muted">{{ $customer->full_address }}</p>

                    @if($customer->shipping_address)
                        <h6>Shipping Address:</h6>
                        <p class="text-muted">{{ $customer->shipping_address }}, {{ $customer->city }}, {{ $customer->state }} - {{ $customer->pincode }}</p>
                    @endif
                </div>

                @if($customer->notes)
                    <div class="mt-3">
                        <h6>Notes:</h6>
                        <p class="text-muted">{{ $customer->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Contact Persons -->
        @if($customer->contacts->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Contact Persons</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Contact Info</th>
                                    <th>Primary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->contacts as $contact)
                                    <tr>
                                        <td>{{ $contact->name }}</td>
                                        <td>{{ $contact->designation ?: '-' }}</td>
                                        <td>{{ $contact->contact_info ?: '-' }}</td>
                                        <td>
                                            @if($contact->is_primary)
                                                <span class="badge bg-success">Primary</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Documents -->
        @if($customer->documents->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Documents</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Expiry</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->documents as $document)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($document->document_type) }}</span>
                                        </td>
                                        <td>{{ $document->document_name }}</td>
                                        <td>{{ $document->file_size_human }}</td>
                                        <td>
                                            @if($document->expiry_date)
                                                <span class="text-{{ $document->isExpired() ? 'danger' : ($document->isExpiringSoon() ? 'warning' : 'success') }}">
                                                    {{ $document->expiry_date->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ $document->file_url }}" target="_blank" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form action="{{ route('customers.delete-document', [$customer, $document]) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this document?')">
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
                </div>
            </div>
        @endif

        <!-- Recent Transactions -->
        @if($customer->ledger && $customer->ledger->transactions->count() > 0)
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Transactions</h5>
                        <a href="{{ route('ledgers.show', $customer->ledger) }}" class="btn btn-sm btn-outline-primary">
                            View All Transactions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Particular</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->ledger->transactions->take(10) as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                        <td>{{ $transaction->particular }}</td>
                                        <td class="text-end">
                                            @if($transaction->debit > 0)
                                                <span class="text-danger">{{ number_format($transaction->debit, 2) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($transaction->credit > 0)
                                                <span class="text-success">{{ number_format($transaction->credit, 2) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-{{ $transaction->running_balance_type == 'debit' ? 'danger' : 'success' }}">
                                                {{ number_format($transaction->running_balance, 2) }}
                                                {{ ucfirst($transaction->running_balance_type) }}
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
    </div>

    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}?customer_id={{ $customer->id }}"
                       class="btn btn-success btn-sm">
                        <i class="fas fa-receipt"></i> Record Payment
                    </a>
                    <a href="{{ route('vouchers.create', ['type' => 'journal']) }}?customer_id={{ $customer->id }}"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-book"></i> Create Invoice
                    </a>
                    @if($customer->ledger)
                        <a href="{{ route('customers.statement', $customer) }}"
                           class="btn btn-info btn-sm">
                            <i class="fas fa-file-alt"></i> Generate Statement
                        </a>
                    @endif
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="fas fa-upload"></i> Upload Document
                    </button>
                </div>
            </div>
        </div>

        <!-- Credit Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Credit Information</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Credit Limit</h6>
                        <h5 class="text-info">₹{{ number_format($customer->credit_limit, 0) }}</h5>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Credit Days</h6>
                        <h5 class="text-info">{{ $customer->credit_days }}</h5>
                    </div>
                </div>

                @if($customer->credit_limit > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <small>Credit Utilization</small>
                            <small>{{ number_format($summary['credit_utilization'], 1) }}%</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $summary['is_over_limit'] ? 'bg-danger' : ($summary['credit_utilization'] > 80 ? 'bg-warning' : 'bg-success') }}"
                                 style="width: {{ min($summary['credit_utilization'], 100) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Customer Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted">Total Transactions</h6>
                        <h5 class="text-primary">{{ $customer->ledger ? $customer->ledger->transactions->count() : 0 }}</h5>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Documents</h6>
                        <h5 class="text-primary">{{ $customer->documents->count() }}</h5>
                    </div>
                </div>

                <hr>

                <div class="small text-muted">
                    <p><strong>Customer since:</strong> {{ $customer->created_at->format('d/m/Y') }}</p>
                    <p><strong>Last updated:</strong> {{ $customer->updated_at->format('d/m/Y H:i') }}</p>
                    @if($customer->ledger)
                        <p><strong>Ledger:</strong> {{ $customer->ledger->name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('customers.upload-document', $customer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="document_type" class="form-label">Document Type</label>
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="">Select Document Type</option>
                            <option value="pan">PAN Card</option>
                            <option value="gstin">GSTIN Certificate</option>
                            <option value="agreement">Agreement</option>
                            <option value="invoice">Invoice</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="document_name" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="document_name" name="document_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">Choose File</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">Maximum file size: 10MB</div>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date (if applicable)</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCustomer() {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('customers.destroy', $customer) }}';
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
