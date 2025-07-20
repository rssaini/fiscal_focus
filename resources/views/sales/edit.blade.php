@extends('layouts.app')

@section('title', 'Edit Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sale Details - {{ $sale->invoice_number ?? $sale->vehicle_no }}</h4>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Sale
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('sales.update', $sale) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                            <input readonly type="datetime-local" class="form-control @error('date') is-invalid @enderror"
                                                   id="date" name="date"
                                                   value="{{ old('date', $sale->date->format('Y-m-d\TH:i')) }}" required>
                                            @error('date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="vehicle_no" class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                                            <input readonly type="text" class="form-control @error('vehicle_no') is-invalid @enderror"
                                                   id="vehicle_no" name="vehicle_no"
                                                   value="{{ old('vehicle_no', $sale->vehicle_no) }}" required>
                                            @error('vehicle_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ref_party_id" class="form-label">Reference Party</label>
                                            <select class="form-select @error('ref_party_id') is-invalid @enderror"
                                                    id="ref_party_id" name="ref_party_id">
                                                <option value="">Select Reference Party</option>
                                                @foreach($parties as $party)
                                                    <option value="{{ $party->id }}" {{ old('ref_party_id', $sale->ref_party_id) == $party->id ? 'selected' : '' }}>
                                                        {{ $party->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('ref_party_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="rec_no" class="form-label">Receipt No.</label>
                                            <input type="text" class="form-control @error('rec_no') is-invalid @enderror"
                                                   id="rec_no" name="rec_no"
                                                   value="{{ old('rec_no', $sale->rec_no) }}" required>
                                            @error('rec_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Product Summary (Read-only) -->
                        @if($sale->items->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Current Products <small class="text-muted">(Use product actions in sale view to modify)</small></h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm">
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sale->items as $item)
                                                <tr>
                                                    <td>{{ $item->sort_order }}</td>
                                                    <td>{{ $item->product->name }}</td>
                                                    <td>{{ number_format($item->tare_wt) }} kg</td>
                                                    <td>
                                                        @if($item->gross_wt)
                                                            {{ number_format($item->gross_wt) }} kg
                                                        @else
                                                            <span class="text-muted">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->formatted_weight }}</td>
                                                    <td>₹{{ number_format($item->rate, 2) }}</td>
                                                    <td>{{ $item->formatted_amount }}</td>
                                                    <td>{!! $item->status_badge !!}</td>
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
                                            </tr>
                                            <tr class="table-info">
                                                <th colspan="4"></th>
                                                <th></th>
                                                <th>GST</th>
                                                <th>₹<span id="gst">{{ $sale->tax_amount }}</span></th>
                                                <th></th>
                                            </tr>
                                            <tr class="table-info">
                                                <th colspan="4"></th>
                                                <th></th>
                                                <th>Total</th>
                                                <th>₹<span id="total_amount">{{ $sale->total_amount }}</span></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Transport & Regulatory -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">TP & Invoice Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tp_no" class="form-label">Transit Pass No.</label>
                                            <input type="text" class="form-control @error('tp_no') is-invalid @enderror"
                                                   id="tp_no" name="tp_no"
                                                   value="{{ old('tp_no', $sale->tp_no) }}">
                                            @error('tp_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tp_wt" class="form-label">TP Weight (tons)</label>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control @error('tp_wt') is-invalid @enderror"
                                                   id="tp_wt" name="tp_wt"
                                                   value="{{ old('tp_wt', $sale->tp_wt) }}">
                                            @error('tp_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="invoice_rate" class="form-label">Invoice Rate (₹)</label>
                                            <div class="input-group">
                                               <input onchange="updateGstTotal()" type="number" step="0.01" min="0"
                                                   class="form-control @error('invoice_rate') is-invalid @enderror"
                                                   id="invoice_rate" name="invoice_rate"
                                                   value="{{ old('invoice_rate', $sale->invoice_rate) }}">
                                                <button type="button" class="btn btn-sm btn-warning" onclick="calculate_invoice_amount()">Full GST</button>
                                            </div>

                                            @error('invoice_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="consignee_id" class="form-label">Consignee</label>
                                            <select class="form-select @error('consignee_id') is-invalid @enderror"
                                                    id="consignee_id" name="consignee_id">
                                                <option value="">Select Consignee</option>
                                                @foreach($consignees as $consignee)
                                                    <option value="{{ $consignee->id }}" {{ old('consignee_id', $sale->consignee_id) == $consignee->id ? 'selected' : '' }}>
                                                        {{ $consignee->consignee_name }} ({{ $consignee->gstin }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('consignee_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="consignee_name" class="form-label">Consignee Name</label>
                                            <input type="text" class="form-control @error('consignee_name') is-invalid @enderror"
                                                   id="consignee_name" name="consignee_name"
                                                   value="{{ old('consignee_name', $sale->consignee_name) }}">
                                            @error('consignee_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="consignee_address" class="form-label">Consignee Address</label>
                                        <textarea class="form-control @error('consignee_address') is-invalid @enderror"
                                                id="consignee_address" name="consignee_address" rows="3">{{ old('consignee_address', $sale->consignee_address) }}</textarea>
                                        @error('consignee_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Consignee Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Royalty Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="royalty_book_no" class="form-label">Royalty Book No.</label>
                                            <input type="text" class="form-control @error('royalty_book_no') is-invalid @enderror"
                                                   id="royalty_book_no" name="royalty_book_no"
                                                   value="{{ old('royalty_book_no', $sale->royalty_book_no) }}">
                                            @error('royalty_book_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="royalty_receipt_no" class="form-label">Royalty Receipt No.</label>
                                            <input type="text" class="form-control @error('royalty_receipt_no') is-invalid @enderror"
                                                   id="royalty_receipt_no" name="royalty_receipt_no"
                                                   value="{{ old('royalty_receipt_no', $sale->royalty_receipt_no) }}">
                                            @error('royalty_receipt_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="royalty_wt" class="form-label">Royalty Wt.</label>
                                            <div class="input-group">
                                               <input type="text" class="form-control @error('royalty_wt') is-invalid @enderror"
                                                   id="royalty_wt" name="royalty_wt"
                                                   value="{{ old('royalty_wt', $sale->royalty_wt) }}">
                                                <button type="button" class="btn btn-sm btn-warning" onclick="royalty_actual_weight()">Actual Wt</button>
                                            </div>

                                            @error('royalty_wt')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Additional Notes</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="4">{{ old('notes', $sale->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>



                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Confirm Sale
                                    </button>
                                    <input type="hidden" name="status" value="confirmed">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function royalty_actual_weight() {
        document.getElementById('royalty_wt').value = "{{ $sale->wt_ton }}";
    }
    function calculate_invoice_amount() {
        const total_rate = parseFloat({{ $sale->subtotal }}) || 0;
        const tp_wt = parseFloat(document.getElementById('tp_wt').value);
        if(tp_wt > 0) {
            document.getElementById('invoice_rate').value = (total_rate / tp_wt).toFixed(2);
        } else {
            document.getElementById('invoice_rate').value = '';
        }
        updateGstTotal();
    }
    function updateGstTotal(){
        const tp_wt = parseFloat(document.getElementById('tp_wt').value) || 0;
        const invoice_rate = parseFloat(document.getElementById('invoice_rate').value) || 0;
        const gst = (tp_wt * invoice_rate * 5 / 100).toFixed(2); // Assuming 5% GST
        document.getElementById('gst').innerText = gst;
        const total_amount = (parseFloat({{ $sale->subtotal }}) + parseFloat(gst) - parseFloat({{ $sale->discount_amount }})).toFixed(2);
        document.getElementById('total_amount').innerText = total_amount;
    }
</script>
@endsection
