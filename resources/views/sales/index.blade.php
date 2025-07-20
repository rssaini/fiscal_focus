@extends('layouts.app')

@section('title', 'Sales Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-truck"></i> Sales Management
                        <small class="text-muted">({{ $sales->total() }} total)</small>
                    </h4>
                    <div>
                        <a href="{{ route('sales.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Sale
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="row g-3 mb-4" id="filterForm">
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="consignee_id" class="form-label">Consignee</label>
                            <select name="consignee_id" id="consignee_id" class="form-select form-select-sm">
                                <option value="">All Consignee</option>
                                @foreach($consignees as $id => $consignee_name)
                                    <option value="{{ $id }}" {{ request('consignee_id') == $id ? 'selected' : '' }}>
                                        {{ $consignee_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" name="date_from" id="date_from"
                                   class="form-control form-control-sm"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" name="date_to" id="date_to"
                                   class="form-control form-control-sm"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search"
                                   class="form-control form-control-sm"
                                   value="{{ request('search') }}"
                                   placeholder="Invoice, Vehicle, Receipt no...">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Clear Filters -->
                    @if(request()->hasAny(['status', 'customer_id', 'date_from', 'date_to', 'search']))
                    <div class="mb-3">
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                        <small class="text-muted ms-2">
                            Showing filtered results
                        </small>
                    </div>
                    @endif

                    <!-- Sales Table -->
                    @if($sales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>DateTime</th>
                                        <th>Reference</th>
                                        <th>Vehicle</th>
                                        <th>Weight</th>
                                        <th>Products</th>
                                        <th>Amount</th>
                                        <th>T.Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales as $sale)
                                        <tr>
                                            <td>
                                                {{ $sale->date->format('d/m/Y') }}<br>
                                                <small class="text-muted">{{ $sale->date->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($sale->refParty)
                                                    {{ $sale->refParty->name }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('sales.show', $sale->id) }}"><strong class="text-primary">{{ $sale->vehicle_no }}</strong></a><br>
                                                @if($sale->items->count() > 0)
                                                    <div class="small">
                                                        @foreach($sale->items as $item)
                                                            <div class="text-muted">
                                                                Tare {{ $item->sort_order }}: {{ number_format($item->tare_wt) }} Kg
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <small class="text-muted">Tare: {{ number_format($sale->tare_wt) }} kg</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sale->gross_wt)
                                                    <strong>{{ $sale->wt_ton }} MT</strong><br>
                                                    @if($sale->items->count() > 0)
                                                        <div class="small">
                                                            @foreach($sale->items as $item)
                                                                <div class="text-muted">
                                                                    Gross {{ $item->sort_order }}: {{ number_format($item->gross_wt) }} Kg
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <small class="text-muted">Gross: {{ number_format($sale->gross_wt) }} kg</small>
                                                    @endif

                                                @else
                                                    <span class="text-muted">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sale->items->count() > 0)
                                                    <strong>{{ $sale->items->count() }} items</strong>
                                                    <div class="small">
                                                        @foreach($sale->items as $item)
                                                            <div class="text-muted">
                                                                {{ $item->product->name }} (₹{{ $item->rate}} x {{ $item->net_wt }})
                                                                @if($item->gross_wt)
                                                                    <span class="text-success">✓</span>
                                                                @else
                                                                    <span class="text-warning">⏳</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No items</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>₹{{ number_format($sale->subtotal,2) }}</strong>
                                                @if($sale->items->count() > 0)
                                                    <div class="small">
                                                        @foreach($sale->items as $item)
                                                            <div class="text-muted">
                                                                {{ $item->product->name }}: ₹{{ number_format($item->amount) }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <small class="text-muted">Gross: {{ number_format($sale->gross_wt) }} kg</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $sale->formatted_total_amount }}</strong><br>
                                                <small>GST: ₹{{ $sale->tax_amount }}</small>
                                            </td>
                                            <td>
                                                {!! $sale->status_badge !!}
                                                @if($sale->payments->count() > 0)
                                                    <br>
                                                    <div class="small">
                                                        <span class="text-success">
                                                            Paid: ₹{{ number_format($sale->getTotalPaidAmount(), 2) }}
                                                        </span>
                                                        @if($sale->getRemainingAmount() > 0)
                                                        <span class="text-danger">
                                                            Bal: ₹{{ number_format($sale->getRemainingAmount(), 2) }}
                                                        </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $sales->firstItem() }} to {{ $sales->lastItem() }} of {{ $sales->total() }} results
                            </div>
                            <div>
                                {{ $sales->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="fas fa-truck fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Sales Found</h5>
                            @if(request()->hasAny(['status', 'customer_id', 'date_from', 'date_to', 'search']))
                                <p class="text-muted">Try adjusting your filters or
                                    <a href="{{ route('sales.index') }}">clear all filters</a>
                                </p>
                            @else
                                <p class="text-muted mb-3">Start by creating your first sale</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create First Sale
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit filter form on change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const autoSubmitFields = ['status'];

    autoSubmitFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    });

    // Search on Enter key
    const searchField = document.getElementById('search');
    if (searchField) {
        searchField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterForm.submit();
            }
        });
    }
});
</script>

<style>
/* Custom styles for better UX */
.table tbody tr:hover {
    background-color: #f8f9fa;
}
.table tbody tr td a{
    text-decoration: none;
}


.badge {
    font-size: 0.75em;
}
small,.small{
    white-space: nowrap;
}


</style>
@endsection
