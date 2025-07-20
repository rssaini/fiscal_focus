@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sale Details - {{ $sale->invoice_number ?? $sale->vehicle_no }}</h4>
                    <div>
                        @if($sale->status === 'draft')
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @if(in_array($sale->status, ['confirmed', 'partially_paid']))
                            <a href="{{ route('sales.payments.create', $sale) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-money-bill"></i> Add Payment
                            </a>
                        @endif
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <ul class="dropdown-menu">
                                <li><button type="button" class="dropdown-item" onclick="print_docs('{{ route('sales.weighment.slip', $sale->id) }}')">Weightment Slip</button></li>
                                @if($sale->tp_no)
                                <li><button type="button" class="dropdown-item" onclick="print_docs('{{ route('sales.invoice.print', $sale) }}')">Invoice</button></li>
                                <li><button type="button" class="dropdown-item" onclick="print_docs('https://mines.rajasthan.gov.in/DMG2/Public/eRawannaStatus/{{ $sale->tp_no }}')">Transit Pass</button></li>
                                @endif
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                                @if($sale->status === 'draft')
                                    <li>
                                        <form action="{{ route('sales.confirm', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item"
                                                    onclick="return confirm('Are you sure you want to confirm this sale?')">
                                                <i class="fas fa-check"></i> Confirm Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if(!in_array($sale->status, ['paid', 'cancelled']))
                                    <li>
                                        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Are you sure you want to cancel this sale?')">
                                                <i class="fas fa-times"></i> Cancel Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if($sale->status === 'draft')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                    onclick="return confirm('Are you sure you want to delete this sale?')">
                                                <i class="fas fa-trash"></i> Delete Sale
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($sale->status != 'pending')
                    <!-- Sale Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td>Date:</th>
                                    <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($sale->tp_no)
                                <tr>
                                    <td>Invoice Number:</th>
                                    <td>{{ $sale->invoice_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Consignee:</th>
                                    <td>{{ $sale->consignee->consignee_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>GSTIN:</th>
                                    <td>{{ $sale->consignee->gstin ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>Invoice Rate:</th>
                                    <td>₹ {{ $sale->invoice_rate }}</td>
                                </tr>
                                <tr>
                                    <td>TP Details:</th>
                                    <td>{{ $sale->tp_wt }} MT ( {{ $sale->tp_no}} )</td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Status:</th>
                                    <td>{!! $sale->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <td>Reference Party:</td>
                                    <td>{{ $sale->refParty->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td>Vehicle Number:</th>
                                    <td><strong>{{ $sale->vehicle_no }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Gross Weight:</th>
                                    <td>{{ $sale->gross_wt }}</td>
                                </tr>
                                <tr>
                                    <td>Tare Weight:</th>
                                    <td>{{ $sale->tare_wt }}</td>
                                </tr>
                                <tr>
                                    <td>Net Weight:</th>
                                    <td>{{ $sale->net_wt }} <strong>({{ $sale->wt_ton }} MT)</strong></td>
                                </tr>
                                <tr>
                                    <td>Amount:</th>
                                    <td>₹ {{ $sale->subtotal }}</td>
                                </tr>
                                <tr>
                                    <td>GST(5%):</th>
                                    <td>₹ {{ $sale->tax_amount }}</td>
                                </tr>
                                <tr>
                                    <td>Total Amount:</th>
                                    <td><strong>{{ $sale->formatted_total_amount }}</strong></td>
                                </tr>
                                @if($sale->payments->count() > 0)
                                <tr>
                                    <td>Paid Amount:</th>
                                    <td>₹ {{ number_format($sale->getTotalPaidAmount(), 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Product Loading Sequence -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                Product Loading Sequence
                                @if($sale->canAddProducts())
                                <button type="button" onclick="changeStatus()" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Complete Adding Products
                                </button>
                                @endif
                            </h5>
                            @if($sale->canAddProducts())
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fas fa-plus"></i> Add Product
                                </button>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($sale->items->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Tare Weight</th>
                                                <th>Gross Weight</th>
                                                <th>Net Weight</th>
                                                <th>Rate</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sale->items as $item)
                                                <tr>
                                                    <td>{{ $item->sort_order }}</td>
                                                    <td>
                                                        <strong>{{ $item->product->name }}</strong>
                                                    </td>
                                                    <td>{{ $item->tare_wt }} kg</td>
                                                    <td>
                                                        @if($item->gross_wt)
                                                            {{ $item->gross_wt }} kg
                                                        @else
                                                            <span class="text-muted">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->formatted_weight }}</td>
                                                    <td>₹{{ number_format($item->rate, 2) }}</td>
                                                    <td>{{ $item->formatted_amount }}</td>
                                                    <td>{!! $item->status_badge !!}</td>
                                                    <td>
                                                        @if(!$item->gross_wt)
                                                            <button type="button"
                                                                    class="btn btn-success btn-sm weigh-btn"
                                                                    onclick="openWeighModal({{ $sale->id }}, {{ $item->id }}, '{{ addslashes($item->product->name) }}', {{ $item->tare_wt }}, {{ $item->rate }})">
                                                                <i class="fas fa-weight"></i> Weigh
                                                            </button>
                                                        @endif
                                                        @if($sale->status == 'pending' && $sale->canAddProducts() && $item->sort_order == $sale->items->max('sort_order'))
                                                            <form action="{{ route('sales.items.remove', [$sale, $item]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                        onclick="return confirm('Remove this product?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>


                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-info">
                                                <th colspan="4"></th>
                                                <th>{{ $sale->formatted_weight }}</th>
                                                <th>SubTotal</th>
                                                <th>₹{{ $sale->subtotal }}</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            @if($sale->status != 'pending')
                                            <tr class="table-info">
                                                <th colspan="4"></th>
                                                <th></th>
                                                <th>GST</th>
                                                <th>₹<span id="gst">{{ $sale->tax_amount }}</span></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-info">
                                                <th colspan="4"></th>
                                                <th></th>
                                                <th>Total</th>
                                                <th>₹<span id="total_amount">{{ $sale->total_amount }}</span></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            @endif
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-box fa-3x mb-3"></i>
                                    <p>No products added yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($sale->payments->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                <td>{{ $payment->payment_reference }}</td>
                                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $payment->status === 'cleared' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($sale->getRemainingAmount() > 0)
                                <div class="alert alert-warning mt-3">
                                    <strong>Remaining Amount:</strong> ₹{{ number_format($sale->getRemainingAmount(), 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($sale->notes)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Notes</h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $sale->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
@if($sale->canAddProducts())
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('sales.items.add', $sale) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Next tare weight:</strong> {{ number_format($sale->getNextTareWeight()) }} kg<br>
                        <small class="text-muted">This will be the starting weight for the next product</small>
                    </div>
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="product_id" name="product_id" required onchange="loadProductRate()">
                            <option value="">Select Product</option>
                            @foreach(\App\Models\Product::active()->get() as $product)
                                <option value="{{ $product->id }}" data-rate="{{ $product->default_rate }}">
                                    {{ $product->name }} ({{ $product->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rate" class="form-label">Rate (₹ per ton) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="rate" name="rate" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Single Reusable Weigh Modal -->
<div class="modal fade" id="weighModal" tabindex="-1" role="dialog" aria-labelledby="weighModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="weighModalLabel">Weigh Product</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="weighForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-info" id="weighInfo">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                    <div class="mb-3">
                        <label for="gross_wt" class="form-label">Gross Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number"
                               step="1"
                               class="form-control"
                               id="gross_wt"
                               name="gross_wt"
                               placeholder="Enter loaded vehicle weight"
                               autocomplete="off"
                               required>
                        <small class="form-text text-muted" id="weighHint">
                            <!-- Dynamic hint will be loaded here -->
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Weigh Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openWeighModal(saleId, itemId, productName, tareWt, rate) {
    // Set form action
    const form = document.getElementById('weighForm');
    form.action = `/sales/${saleId}/items/${itemId}/weigh`;

    // Update modal title
    document.getElementById('weighModalLabel').textContent = `Weigh ${productName}`;

    // Update info content
    document.getElementById('weighInfo').innerHTML = `
        <strong>Current tare weight:</strong> ${new Intl.NumberFormat('en-IN').format(tareWt)} kg<br>
        <strong>Product:</strong> ${productName}<br>
        <strong>Rate:</strong> ₹${new Intl.NumberFormat('en-IN', {minimumFractionDigits: 2}).format(rate)} per ton
    `;

    // Update hint
    document.getElementById('weighHint').textContent = `Must be greater than ${new Intl.NumberFormat('en-IN').format(tareWt)} kg`;

    // Set minimum value
    document.getElementById('gross_wt').min = tareWt + 1;

    // Clear previous value
    document.getElementById('gross_wt').value = '';

    // Show modal
    $('#weighModal').modal('show');

    // Focus on input after modal is shown
    $('#weighModal').on('shown.bs.modal', function() {
        document.getElementById('gross_wt').focus();
        // Remove this event listener to prevent multiple bindings
        $(this).off('shown.bs.modal');
    });
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const weighForm = document.getElementById('weighForm');
    if (weighForm) {
        weighForm.addEventListener('submit', function(e) {
            const grossWt = parseInt(document.getElementById('gross_wt').value);
            const minWt = parseInt(document.getElementById('gross_wt').min);

            if (!grossWt || grossWt < minWt) {
                e.preventDefault();
                alert('Please enter a valid gross weight greater than ' + (minWt - 1) + ' kg');
                return false;
            }
        });
    }
});
</script>

<script>
    function changeStatus() {
        if (confirm('Are you sure you want to complete adding products?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('sales.draft', $sale) }}";
            form.innerHTML = '@csrf @method("PATCH") <input type="hidden" name="status" value="draft">';
            document.body.appendChild(form);
            form.submit();
        }
    }
function loadProductRate() {
    const select = document.getElementById('product_id');
    const rateInput = document.getElementById('rate');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.dataset.rate) {
        rateInput.value = selectedOption.dataset.rate;
    }
}
    function print_docs(url) {
        const win = window.open(url, "_blank", 'width=800,height=600,resizable=yes,scrollbars=yes');
        win.onload = function() { // Attach the printing after window content completely loaded.
            win.focus(); // ensure the new window receives focus
            win.print(); // Initiate printing
            win.onafterprint = function() {
            win.close(); // Close the window after printing
            };
        }
    }
</script>
@endsection
