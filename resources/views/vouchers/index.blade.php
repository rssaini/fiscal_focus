@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Accounting Vouchers</h1>
        <p class="text-muted mb-0">Manage all voucher entries</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-plus"></i> Create Voucher
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'journal']) }}">
                <i class="fas fa-book text-primary"></i> Journal Voucher
            </a></li>
            <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'payment']) }}">
                <i class="fas fa-money-bill-wave text-danger"></i> Payment Voucher
            </a></li>
            <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'receipt']) }}">
                <i class="fas fa-receipt text-success"></i> Receipt Voucher
            </a></li>
            <li><a class="dropdown-item" href="{{ route('vouchers.create', ['type' => 'contra']) }}">
                <i class="fas fa-exchange-alt text-warning"></i> Contra Voucher
            </a></li>
        </ul>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('vouchers.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="voucher_type" class="form-label">Voucher Type</label>
                <select class="form-select" name="voucher_type" id="voucher_type">
                    <option value="">All Types</option>
                    <option value="journal" {{ request('voucher_type') == 'journal' ? 'selected' : '' }}>Journal</option>
                    <option value="payment" {{ request('voucher_type') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="receipt" {{ request('voucher_type') == 'receipt' ? 'selected' : '' }}>Receipt</option>
                    <option value="contra" {{ request('voucher_type') == 'contra' ? 'selected' : '' }}>Contra</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" name="from_date" id="from_date"
                       value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" name="to_date" id="to_date"
                       value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" name="search" id="search"
                       value="{{ request('search') }}" placeholder="Voucher number, narration...">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Vouchers Table -->
<div class="card">
    <div class="card-body">
        @if($vouchers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Voucher No.</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Narration</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vouchers as $voucher)
                            <tr>
                                <td>
                                    <strong>{{ $voucher->voucher_number }}</strong>
                                    @if($voucher->reference_number)
                                        <br><small class="text-muted">Ref: {{ $voucher->reference_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $voucher->voucher_type == 'journal' ? 'primary' :
                                                            ($voucher->voucher_type == 'payment' ? 'danger' :
                                                            ($voucher->voucher_type == 'receipt' ? 'success' : 'warning')) }}">
                                        {{ ucfirst($voucher->voucher_type) }}
                                    </span>
                                </td>
                                <td>{{ $voucher->voucher_date->format('d/m/Y') }}</td>
                                <td>
                                    {{ Str::limit($voucher->narration, 50) }}
                                    @if($voucher->entries_count > 2)
                                        <br><small class="text-muted">{{ $voucher->entries->count() }} entries</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($voucher->total_amount, 2) }}</strong>
                                </td>
                                <td>{!! $voucher->status_badge !!}</td>
                                <td>
                                    {{ $voucher->creator ? $voucher->creator->name : 'System' }}
                                    <br><small class="text-muted">{{ $voucher->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('vouchers.show', $voucher) }}"
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($voucher->canBeEdited())
                                            <a href="{{ route('vouchers.edit', $voucher) }}"
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('vouchers.duplicate', $voucher) }}"
                                           class="btn btn-outline-info" title="Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }}
                    of {{ $vouchers->total() }} vouchers
                </div>
                <div>
                    {{ $vouchers->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                <p class="text-muted">No vouchers found.</p>
                <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Voucher
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Summary Cards -->
@if($vouchers->count() > 0)
    @php
        $allVouchers = \App\Models\Voucher::selectRaw('
            voucher_type,
            status,
            COUNT(*) as count,
            SUM(total_amount) as total_amount
        ')->groupBy('voucher_type', 'status')->get();

        $summary = [];
        foreach($allVouchers as $item) {
            $summary[$item->voucher_type][$item->status] = [
                'count' => $item->count,
                'amount' => $item->total_amount
            ];
        }
    @endphp

    <div class="row mt-4">
        @foreach(['journal', 'payment', 'receipt', 'contra'] as $type)
            <div class="col-md-3">
                <div class="card border-{{ $type == 'journal' ? 'primary' :
                                        ($type == 'payment' ? 'danger' :
                                        ($type == 'receipt' ? 'success' : 'warning')) }}">
                    <div class="card-body text-center">
                        <h6 class="card-title">{{ ucfirst($type) }} Vouchers</h6>
                        @php
                            $typeData = $summary[$type] ?? [];
                            $totalCount = collect($typeData)->sum('count');
                            $totalAmount = collect($typeData)->sum('amount');
                        @endphp
                        <h4 class="text-{{ $type == 'journal' ? 'primary' :
                                          ($type == 'payment' ? 'danger' :
                                          ($type == 'receipt' ? 'success' : 'warning')) }}">
                            {{ $totalCount }}
                        </h4>
                        <small class="text-muted">₹{{ number_format($totalAmount, 2) }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
