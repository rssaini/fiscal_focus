@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        Create {{ ucfirst($voucherType) }} Voucher
                        <span class="badge bg-{{ $voucherType == 'journal' ? 'primary' :
                                              ($voucherType == 'payment' ? 'danger' :
                                              ($voucherType == 'receipt' ? 'success' : 'warning')) }}">
                            {{ strtoupper($voucherType) }}
                        </span>
                        @if(request('customer_id'))
                            @php $customer = \App\Models\Customer::find(request('customer_id')); @endphp
                            @if($customer)
                                <span class="badge bg-info ms-2">Customer: {{ $customer->name }}</span>
                            @endif
                        @endif
                    </h4>
                    @if(request('customer_id'))
                        <a href="{{ route('customers.show', request('customer_id')) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-user"></i> Back to Customer
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('vouchers.store') }}" method="POST" id="voucherForm">
                    @csrf
                    <input type="hidden" name="voucher_type" value="{{ $voucherType }}">

                    <!-- Voucher Header -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="voucher_date" class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('voucher_date') is-invalid @enderror"
                                   id="voucher_date" name="voucher_date"
                                   value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required>
                            @error('voucher_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">
                                Customer
                                @if(in_array($voucherType, ['receipt']) || request('customer_id'))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <select class="form-select @error('customer_id') is-invalid @enderror"
                                    id="customer_id" name="customer_id"
                                    {{ request('customer_id') ? '' : '' }}
                                    onchange="handleCustomerChange()">
                                <option value="">Select Customer (Optional)</option>
                                @foreach(\App\Models\Customer::active()->orderBy('name')->get() as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-credit-limit="{{ $customer->credit_limit }}"
                                            data-credit-days="{{ $customer->credit_days }}"
                                            {{ (old('customer_id') ?: request('customer_id')) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->display_name }} ({{ $customer->customer_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                   id="reference_number" name="reference_number"
                                   value="{{ old('reference_number') }}" placeholder="e.g., INV-001, Receipt-001">
                            @error('reference_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="narration" class="form-label">Narration <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('narration') is-invalid @enderror"
                                   id="narration" name="narration" value="{{ old('narration') }}"
                                   placeholder="Brief description of the transaction" required>
                            @error('narration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Customer Information Panel -->
                    <div id="customerInfoPanel" class="card bg-light mb-4" style="display: none;">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted">Customer Balance:</small>
                                    <div id="customerBalance" class="fw-bold"></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Credit Limit:</small>
                                    <div id="customerCreditLimit" class="fw-bold"></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Credit Days:</small>
                                    <div id="customerCreditDays" class="fw-bold"></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Outstanding:</small>
                                    <div id="customerOutstanding" class="fw-bold"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method (for receipt/payment vouchers) -->
                    <div id="paymentMethodSection" class="row mb-4" style="display: none;">
                        <div class="col-md-4">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="payment_reference" class="form-label">Payment Reference</label>
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference"
                                   placeholder="Cheque no., Transaction ID, etc.">
                        </div>
                        <div class="col-md-4">
                            <label for="payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="payment_amount"
                                   placeholder="0.00" onchange="updatePaymentEntries()">
                        </div>
                    </div>

                    <!-- Quick Invoice Mode (for journal vouchers with customer) -->
                    <div id="quickInvoiceSection" class="mb-4" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Invoice Details</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableQuickInvoice" onchange="toggleInvoiceMode()">
                                <label class="form-check-label" for="enableQuickInvoice">
                                    Quick Invoice Mode
                                </label>
                            </div>
                        </div>

                        <div id="invoiceItemsContainer" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%">Description</th>
                                            <th style="width: 10%">Qty</th>
                                            <th style="width: 10%">Unit</th>
                                            <th style="width: 15%">Rate</th>
                                            <th style="width: 15%">Amount</th>
                                            <th style="width: 10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsTableBody">
                                        <!-- Invoice items will be added here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-success btn-sm" onclick="addInvoiceItem()">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                                <div class="text-end">
                                    <div><strong>Total Amount: ₹<span id="invoiceTotalAmount">0.00</span></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Entries -->
                    <div class="mb-4" id="voucherEntriesSection">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Voucher Entries</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addEntry()">
                                <i class="fas fa-plus"></i> Add Entry
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="entriesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%">Ledger Account</th>
                                        <th style="width: 25%">Particular</th>
                                        <th style="width: 15%">Debit</th>
                                        <th style="width: 15%">Credit</th>
                                        <th style="width: 10%">Narration</th>
                                        <th style="width: 5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="entriesTableBody">
                                    <!-- Dynamic entries will be added here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="alert alert-primary">
                                    <strong>Total Debit:</strong>
                                    <span id="totalDebit" class="fw-bold">0.00</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <strong>Total Credit:</strong>
                                    <span id="totalCredit" class="fw-bold">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror"
                                  id="remarks" name="remarks" rows="3"
                                  placeholder="Additional notes or comments">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('entries')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save"></i> Save Voucher
                        </button>
                        <a href="{{ request('customer_id') ? route('customers.show', request('customer_id')) : route('vouchers.index') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let entryCount = 0;
let invoiceItemCount = 0;
const ledgers = @json($ledgers);
const voucherType = '{{ $voucherType }}';
const selectedCustomerId = {{ request('customer_id') ?: 'null' }};

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    handleVoucherTypeSetup();

    if (selectedCustomerId) {
        handleCustomerChange();
    }

    // Add initial entries
    if (voucherType === 'payment' || voucherType === 'receipt') {
        addEntry(); // Cash/Bank entry
        addEntry(); // Customer/Expense entry
    } else {
        addEntry();
        addEntry();
    }
});

function handleVoucherTypeSetup() {
    const paymentMethodSection = document.getElementById('paymentMethodSection');
    const quickInvoiceSection = document.getElementById('quickInvoiceSection');

    // Show payment method for receipt/payment vouchers
    if (voucherType === 'receipt' || voucherType === 'payment') {
        paymentMethodSection.style.display = 'flex';
    }

    // Show quick invoice for journal vouchers with customer
    if (voucherType === 'journal' && selectedCustomerId) {
        quickInvoiceSection.style.display = 'block';
    }
}

function handleCustomerChange() {
    const customerSelect = document.getElementById('customer_id');
    const customerId = customerSelect.value;
    const customerInfoPanel = document.getElementById('customerInfoPanel');
    const quickInvoiceSection = document.getElementById('quickInvoiceSection');

    if (customerId) {
        // Show customer info panel
        customerInfoPanel.style.display = 'block';

        // Show quick invoice section for journal vouchers
        if (voucherType === 'journal') {
            quickInvoiceSection.style.display = 'block';
        }

        // Fetch customer details via AJAX
        fetchCustomerDetails(customerId);

        // Auto-fill narration based on voucher type
        autoFillNarration(customerSelect.options[customerSelect.selectedIndex].text);

    } else {
        customerInfoPanel.style.display = 'none';
        quickInvoiceSection.style.display = 'none';
    }
}

function fetchCustomerDetails(customerId) {
    // This would typically be an AJAX call
    // For now, we'll use the data attributes
    const customerSelect = document.getElementById('customer_id');
    const selectedOption = customerSelect.options[customerSelect.selectedIndex];

    if (selectedOption) {
        const creditLimit = selectedOption.dataset.creditLimit || '0';
        const creditDays = selectedOption.dataset.creditDays || '0';

        document.getElementById('customerCreditLimit').textContent = '₹' + parseFloat(creditLimit).toLocaleString();
        document.getElementById('customerCreditDays').textContent = creditDays + ' days';

        // You would fetch actual balance and outstanding via AJAX
        document.getElementById('customerBalance').innerHTML = '<span class="text-muted">Loading...</span>';
        document.getElementById('customerOutstanding').innerHTML = '<span class="text-muted">Loading...</span>';
    }
}

function autoFillNarration(customerName) {
    const narrationInput = document.getElementById('narration');
    if (!narrationInput.value) {
        switch(voucherType) {
            case 'receipt':
                narrationInput.value = `Payment received from ${customerName.split(' (')[0]}`;
                break;
            case 'payment':
                narrationInput.value = `Payment made to ${customerName.split(' (')[0]}`;
                break;
            case 'journal':
                narrationInput.value = `Sale to ${customerName.split(' (')[0]}`;
                break;
        }
    }
}

function toggleInvoiceMode() {
    const enableQuickInvoice = document.getElementById('enableQuickInvoice');
    const invoiceItemsContainer = document.getElementById('invoiceItemsContainer');
    const voucherEntriesSection = document.getElementById('voucherEntriesSection');

    if (enableQuickInvoice.checked) {
        invoiceItemsContainer.style.display = 'block';
        voucherEntriesSection.style.display = 'none';
        addInvoiceItem();
    } else {
        invoiceItemsContainer.style.display = 'none';
        voucherEntriesSection.style.display = 'block';
        document.getElementById('invoiceItemsTableBody').innerHTML = '';
        invoiceItemCount = 0;
    }
}

function addInvoiceItem() {
    const tbody = document.getElementById('invoiceItemsTableBody');
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <input type="text" class="form-control" name="invoice_items[${invoiceItemCount}][description]"
                   placeholder="Item description" required>
        </td>
        <td>
            <input type="number" step="0.001" class="form-control" name="invoice_items[${invoiceItemCount}][quantity]"
                   value="1" min="0.001" onchange="calculateItemAmount(this)" required>
        </td>
        <td>
            <input type="text" class="form-control" name="invoice_items[${invoiceItemCount}][unit]"
                   value="Nos" placeholder="Unit">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="invoice_items[${invoiceItemCount}][rate]"
                   placeholder="0.00" onchange="calculateItemAmount(this)" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control item-amount" readonly
                   name="invoice_items[${invoiceItemCount}][amount]" value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeInvoiceItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    invoiceItemCount++;
}

function removeInvoiceItem(button) {
    button.closest('tr').remove();
    calculateInvoiceTotal();
}

function calculateItemAmount(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const rate = parseFloat(row.querySelector('input[name*="[rate]"]').value) || 0;
    const amount = quantity * rate;

    row.querySelector('.item-amount').value = amount.toFixed(2);
    calculateInvoiceTotal();
}

function calculateInvoiceTotal() {
    const amounts = document.querySelectorAll('.item-amount');
    let total = 0;

    amounts.forEach(amount => {
        total += parseFloat(amount.value) || 0;
    });

    document.getElementById('invoiceTotalAmount').textContent = total.toFixed(2);

    // Auto-create voucher entries for invoice
    if (total > 0) {
        createInvoiceEntries(total);
    }
}

function createInvoiceEntries(totalAmount) {
    const customerId = document.getElementById('customer_id').value;
    if (!customerId) return;

    // Clear existing entries
    document.getElementById('entriesTableBody').innerHTML = '';
    entryCount = 0;

    // Add customer debit entry
    addEntry();
    const customerRow = document.querySelector('#entriesTableBody tr:last-child');
    customerRow.querySelector('select[name*="[ledger_id]"]').value = getCustomerLedgerId(customerId);
    customerRow.querySelector('input[name*="[particular]"]').value = 'Sale Invoice';
    customerRow.querySelector('input[name*="[debit]"]').value = totalAmount.toFixed(2);
    customerRow.querySelector('input[name*="[debit]"]').dispatchEvent(new Event('change'));

    // Add sales credit entry
    addEntry();
    const salesRow = document.querySelector('#entriesTableBody tr:last-child');
    salesRow.querySelector('input[name*="[particular]"]').value = 'Sales Revenue';
    salesRow.querySelector('input[name*="[credit]"]').value = totalAmount.toFixed(2);
    salesRow.querySelector('input[name*="[credit]"]').dispatchEvent(new Event('change'));
}

function updatePaymentEntries() {
    const customerId = document.getElementById('customer_id').value;
    const amount = parseFloat(document.getElementById('payment_amount').value) || 0;
    const paymentMethod = document.getElementById('payment_method').value;

    if (!customerId || amount <= 0) return;

    // Clear existing entries
    document.getElementById('entriesTableBody').innerHTML = '';
    entryCount = 0;

    if (voucherType === 'receipt') {
        // Dr. Cash/Bank, Cr. Customer
        addEntry();
        const cashRow = document.querySelector('#entriesTableBody tr:last-child');
        cashRow.querySelector('input[name*="[particular]"]').value = 'Payment received';
        cashRow.querySelector('input[name*="[debit]"]').value = amount.toFixed(2);
        cashRow.querySelector('input[name*="[debit]"]').dispatchEvent(new Event('change'));

        addEntry();
        const customerRow = document.querySelector('#entriesTableBody tr:last-child');
        customerRow.querySelector('select[name*="[ledger_id]"]').value = getCustomerLedgerId(customerId);
        customerRow.querySelector('input[name*="[particular]"]').value = 'Payment received';
        customerRow.querySelector('input[name*="[credit]"]').value = amount.toFixed(2);
        customerRow.querySelector('input[name*="[credit]"]').dispatchEvent(new Event('change'));
    }
}

function getCustomerLedgerId(customerId) {
    // This would typically be fetched via AJAX or passed from backend
    // For now, return empty - user will need to select manually
    return '';
}

// Rest of the existing functions (addEntry, removeEntry, etc.) remain the same
function addEntry() {
    const tbody = document.getElementById('entriesTableBody');
    const row = document.createElement('tr');

    let ledgerOptions = '<option value="">Select Ledger</option>';

    const groupedLedgers = @json($groupedLedgers);
    Object.keys(groupedLedgers).forEach(accountType => {
        if (groupedLedgers[accountType].length > 0) {
            ledgerOptions += `<optgroup label="${accountType.replace('_', ' ').toUpperCase()}">`;
            groupedLedgers[accountType].forEach(ledger => {
                const accountInfo = ledger.chart_of_account ?
                    ` (${ledger.chart_of_account.account_code})` : '';
                ledgerOptions += `<option value="${ledger.id}" data-account-type="${accountType}">${ledger.name}${accountInfo}</option>`;
            });
            ledgerOptions += '</optgroup>';
        }
    });

    row.innerHTML = `
        <td>
            <select class="form-select ledger-select" name="entries[${entryCount}][ledger_id]" required onchange="updateTotals()">
                ${ledgerOptions}
            </select>
        </td>
        <td>
            <input type="text" class="form-control" name="entries[${entryCount}][particular]"
                   placeholder="Transaction details" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control debit-input"
                   name="entries[${entryCount}][debit]" placeholder="0.00"
                   oninput="handleDebitInput(this)" onchange="updateTotals()">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control credit-input"
                   name="entries[${entryCount}][credit]" placeholder="0.00"
                   oninput="handleCreditInput(this)" onchange="updateTotals()">
        </td>
        <td>
            <input type="text" class="form-control" name="entries[${entryCount}][narration]"
                   placeholder="Notes">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    entryCount++;

    const ledgerSelect = row.querySelector('.ledger-select');
    ledgerSelect.addEventListener('change', function() {
        suggestParticular(this, row);
    });
}

function removeEntry(button) {
    const tbody = document.getElementById('entriesTableBody');
    if (tbody.children.length > 2) {
        button.closest('tr').remove();
        updateTotals();
    } else {
        alert('At least 2 entries are required for a voucher.');
    }
}

function handleDebitInput(input) {
    const row = input.closest('tr');
    const creditInput = row.querySelector('.credit-input');

    if (input.value && parseFloat(input.value) > 0) {
        creditInput.value = '';
        creditInput.disabled = true;
    } else {
        creditInput.disabled = false;
    }
}

function handleCreditInput(input) {
    const row = input.closest('tr');
    const debitInput = row.querySelector('.debit-input');

    if (input.value && parseFloat(input.value) > 0) {
        debitInput.value = '';
        debitInput.disabled = true;
    } else {
        debitInput.disabled = false;
    }
}

function suggestParticular(selectElement, row) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const ledgerName = selectedOption.text.split(' (')[0];
    const particularInput = row.querySelector('input[name*="[particular]"]');
    const customerId = document.getElementById('customer_id').value;

    if (ledgerName && !particularInput.value) {
        let suggestion = '';

        if (customerId) {
            const customerName = document.getElementById('customer_id').options[document.getElementById('customer_id').selectedIndex].text.split(' (')[0];
            switch(voucherType) {
                case 'receipt':
                    suggestion = `Payment from ${customerName}`;
                    break;
                case 'payment':
                    suggestion = `Payment to ${customerName}`;
                    break;
                case 'journal':
                    suggestion = `Transaction with ${customerName}`;
                    break;
                default:
                    suggestion = `Transaction with ${ledgerName}`;
            }
        } else {
            suggestion = `Transaction with ${ledgerName}`;
        }

        particularInput.value = suggestion;
    }
}

function updateTotals() {
    const debitInputs = document.querySelectorAll('.debit-input');
    const creditInputs = document.querySelectorAll('.credit-input');

    let totalDebit = 0;
    let totalCredit = 0;

    debitInputs.forEach(input => {
        if (input.value) {
            totalDebit += parseFloat(input.value);
        }
    });

    creditInputs.forEach(input => {
        if (input.value) {
            totalCredit += parseFloat(input.value);
        }
    });

    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    const enableQuickInvoice = document.getElementById('enableQuickInvoice');

    let isValid = false;

    if (enableQuickInvoice && enableQuickInvoice.checked) {
        // For invoice mode, check if we have items
        const itemAmounts = document.querySelectorAll('.item-amount');
        let hasItems = Array.from(itemAmounts).some(input => parseFloat(input.value) > 0);
        isValid = hasItems;
    } else {
        // For regular vouchers, check balance
        isValid = Math.abs(totalDebit - totalCredit) < 0.01 && totalDebit > 0;
    }

    submitBtn.disabled = !isValid;

    if (isValid) {
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Voucher';
    } else {
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
        if (totalDebit === 0 && totalCredit === 0) {
            submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Add Entries';
        } else {
            submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Balance Required';
        }
    }
}
</script>
@endsection
