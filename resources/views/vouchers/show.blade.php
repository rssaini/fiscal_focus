@extends('layouts.app_')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>
            {{ $voucher->voucher_type_display }}
            @if($voucher->customer)
                <span class="badge bg-info ms-2">{{ $voucher->customer->name }}</span>
            @endif
        </h1>
        <p class="text-muted mb-0">
            Voucher No: <strong>{{ $voucher->voucher_number }}</strong> |
            Date: {{ $voucher->voucher_date->format('d/m/Y') }} |
            {!! $voucher->status_badge !!}
            @if($voucher->customer)
                | Customer: <strong>{{ $voucher->customer->customer_code }}</strong>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @if($voucher->customer)
            <a href="{{ route('customers.show', $voucher->customer) }}" class="btn btn-outline-info">
                <i class="fas fa-user"></i> View Customer
            </a>
        @endif

        @if($voucher->canBeEdited())
            <a href="{{ route('vouchers.edit', $voucher) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        @endif

        @if($voucher->canBePosted())
            <form action="{{ route('vouchers.post', $voucher) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to post this voucher? This action cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Post Voucher
                </button>
            </form>
        @endif

        @if($voucher->canBeCancelled())
            <form action="{{ route('vouchers.cancel', $voucher) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to cancel this voucher?')">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </form>
        @endif

        <a href="{{ route('vouchers.duplicate', $voucher) }}" class="btn btn-info">
            <i class="fas fa-copy"></i> Duplicate
        </a>

        <a href="{{ route('vouchers.print', $voucher) }}" class="btn btn-outline-primary" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>

        <a href="{{ $voucher->customer ? route('customers.show', $voucher->customer) : route('vouchers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Customer Information Panel -->
@if($voucher->customer)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Customer Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $voucher->customer->display_name }}</h6>
                            <small class="text-muted">{{ $voucher->customer->customer_code }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col-6">
                            @php
                                $balance = $voucher->customer->getCurrentBalance();
                            @endphp
                            <h6 class="text-{{ $balance['type'] == 'debit' ? 'danger' : 'success' }}">
                                ₹{{ number_format($balance['balance'], 2) }}
                            </h6>
                            <small class="text-muted">Current Balance ({{ ucfirst($balance['type']) }})</small>
                        </div>
                        <div class="col-6">
                            <h6 class="text-info">₹{{ number_format($voucher->customer->credit_limit, 2) }}</h6>
                            <small class="text-muted">Credit Limit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-end">
                        @if($voucher->customer->email)
                            <div><i class="fas fa-envelope text-muted"></i> {{ $voucher->customer->email }}</div>
                        @endif
                        @if($voucher->customer->mobile)
                            <div><i class="fas fa-mobile-alt text-muted"></i> {{ $voucher->customer->mobile }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Voucher Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Voucher Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Voucher Number:</td>
                                <td>{{ $voucher->voucher_number }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Voucher Type:</td>
                                <td>
                                    <span class="badge bg-{{ $voucher->voucher_type == 'journal' ? 'primary' :
                                                            ($voucher->voucher_type == 'payment' ? 'danger' :
                                                            ($voucher->voucher_type == 'receipt' ? 'success' : 'warning')) }}">
                                        {{ $voucher->voucher_type_display }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Voucher Date:</td>
                                <td>{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                            </tr>
                            @if($voucher->reference_number)
                                <tr>
                                    <td class="fw-bold">Reference:</td>
                                    <td>{{ $voucher->reference_number }}</td>
                                </tr>
                            @endif
                            @if($voucher->customer)
                                <tr>
                                    <td class="fw-bold">Customer:</td>
                                    <td>
                                        <a href="{{ route('customers.show', $voucher->customer) }}">
                                            {{ $voucher->customer->display_name }}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>{!! $voucher->status_badge !!}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Total Amount:</td>
                                <td><strong>₹{{ number_format($voucher->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created By:</td>
                                <td>{{ $voucher->creator ? $voucher->creator->name : 'System' }}</td>
                            </tr>
                            @if($voucher->posted_at)
                                <tr>
                                    <td class="fw-bold">Posted At:</td>
                                    <td>{{ $voucher->posted_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endif
                            @if($voucher->approver)
                                <tr>
                                    <td class="fw-bold">Approved By:</td>
                                    <td>{{ $voucher->approver->name }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <h6>Narration:</h6>
                    <p class="text-muted mb-0">{{ $voucher->narration }}</p>
                </div>

                @if($voucher->remarks)
                    <div class="mt-3">
                        <h6>Remarks:</h6>
                        <p class="text-muted mb-0">{{ $voucher->remarks }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Voucher Entries -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Voucher Entries</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ledger Account</th>
                                <th>Particular</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Narration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            @foreach($voucher->entries as $entry)
                                @php
                                    $totalDebit += $entry->debit;
                                    $totalCredit += $entry->credit;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($entry->ledger->id == $voucher->customer?->ledger_id)
                                                <i class="fas fa-user text-info me-2" title="Customer Ledger"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $entry->ledger->name }}</strong>
                                                @if($entry->ledger->chartOfAccount)
                                                    <br><small class="text-muted">
                                                        {{ $entry->ledger->chartOfAccount->account_code }} -
                                                        {{ $entry->ledger->chartOfAccount->account_name }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $entry->particular }}</td>
                                    <td class="text-end">
                                        @if($entry->debit > 0)
                                            <strong class="text-primary">₹{{ number_format($entry->debit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($entry->credit > 0)
                                            <strong class="text-success">₹{{ number_format($entry->credit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->narration ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-end">₹{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-end">₹{{ number_format($totalCredit, 2) }}</th>
                                <th>
                                    @if(abs($totalDebit - $totalCredit) < 0.01)
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="badge bg-danger">Unbalanced</span>
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">₹{{ number_format($voucher->total_debit, 2) }}</h4>
                            <small class="text-muted">Total Debit</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">₹{{ number_format($voucher->total_credit, 2) }}</h4>
                        <small class="text-muted">Total Credit</small>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <p><strong>Entries:</strong> {{ $voucher->entries->count() }}</p>
                    <p><strong>Created:</strong> {{ $voucher->created_at->format('d/m/Y H:i') }}</p>
                    @if($voucher->updated_at != $voucher->created_at)
                        <p><strong>Updated:</strong> {{ $voucher->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Quick Actions -->
        @if($voucher->customer)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Customer Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('customers.show', $voucher->customer) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> View Customer Profile
                        </a>

                        @if($voucher->customer->ledger)
                            <a href="{{ route('ledgers.show', $voucher->customer->ledger) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-book"></i> View Customer Ledger
                            </a>
                        @endif

                        <a href="{{ route('customers.statement', $voucher->customer) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-alt"></i> Customer Statement
                        </a>

                        @if($voucher->voucher_type === 'journal' && $voucher->status === 'posted')
                            <a href="{{ route('vouchers.create', ['type' => 'receipt', 'customer_id' => $voucher->customer->id]) }}"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-receipt"></i> Record Payment
                            </a>
                        @endif

                        <a href="{{ route('vouchers.create', ['type' => 'journal', 'customer_id' => $voucher->customer->id]) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Invoice
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Vouchers -->
        @if($voucher->customer && $voucher->status === 'posted')
            @php
                $relatedVouchers = \App\Models\Voucher::where('customer_id', $voucher->customer->id)
                    ->where('id', '!=', $voucher->id)
                    ->where('status', 'posted')
                    ->orderBy('voucher_date', 'desc')
                    ->limit(5)
                    ->get();
            @endphp

            @if($relatedVouchers->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Customer Vouchers</h6>
                    </div>
                    <div class="card-body">
                        @foreach($relatedVouchers as $relatedVoucher)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <a href="{{ route('vouchers.show', $relatedVoucher) }}" class="text-decoration-none">
                                        <small class="fw-bold">{{ $relatedVoucher->voucher_number }}</small>
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        {{ $relatedVoucher->voucher_date->format('d/m/Y') }} -
                                        {{ ucfirst($relatedVoucher->voucher_type) }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="fw-bold">₹{{ number_format($relatedVoucher->total_amount, 2) }}</small>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="my-2">
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <!-- Related Transactions -->
        @if($voucher->status === 'posted' && $voucher->transactions->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Related Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Transaction UUID:</strong></p>
                        <code class="small">{{ $voucher->voucher_number }}</code>
                        <p class="mt-2"><strong>Transactions Created:</strong> {{ $voucher->transactions->count() }}</p>
                        <p class="text-success mb-0">
                            <i class="fas fa-check-circle"></i> Posted to ledgers successfully
                        </p>

                        @if($voucher->customer && $voucher->customer->ledger)
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Customer Balance Impact:</span>
                                @php
                                    $customerTransaction = $voucher->transactions->where('ledger_id', $voucher->customer->ledger->id)->first();
                                @endphp
                                @if($customerTransaction)
                                    <span class="fw-bold text-{{ $customerTransaction->debit > 0 ? 'danger' : 'success' }}">
                                        {{ $customerTransaction->debit > 0 ? '+' : '-' }}₹{{ number_format($customerTransaction->debit > 0 ? $customerTransaction->debit : $customerTransaction->credit, 2) }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($voucher->customer)
    <!-- Customer Balance Alert -->
    @php
        $balance = $voucher->customer->getCurrentBalance();
        $isOverLimit = $voucher->customer->isOverCreditLimit();
        $outstanding = $voucher->customer->getOutstandingAmount();
    @endphp

    @if($isOverLimit || $outstanding > 0)
        <div class="alert alert-{{ $isOverLimit ? 'danger' : 'warning' }} mt-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-{{ $isOverLimit ? 'exclamation-triangle' : 'info-circle' }} me-2"></i>
                <div>
                    @if($isOverLimit)
                        <strong>Credit Limit Exceeded!</strong>
                        Customer has exceeded their credit limit of ₹{{ number_format($voucher->customer->credit_limit, 2) }}.
                    @endif
                    @if($outstanding > 0)
                        <div>Outstanding Amount: <strong>₹{{ number_format($outstanding, 2) }}</strong></div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
@endsection
