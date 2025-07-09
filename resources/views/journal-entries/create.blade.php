@extends('layouts.app_')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Create Journal Entry</h4>
            </div>
            <div class="card-body">
                <form id="journalEntryForm" action="{{ route('journal-entries.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="transaction_date" class="form-label">Transaction Date</label>
                            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror"
                                   id="transaction_date" name="transaction_date"
                                   value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="particular" class="form-label">Particular/Description</label>
                            <input type="text" class="form-control @error('particular') is-invalid @enderror"
                                   id="particular" name="particular" value="{{ old('particular') }}"
                                   placeholder="e.g., Sale to Customer ABC" required>
                            @error('particular')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Journal Entries</h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="addEntry()">
                                <i class="fas fa-plus"></i> Add Entry
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="entriesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%">Ledger</th>
                                        <th style="width: 20%">Debit</th>
                                        <th style="width: 20%">Credit</th>
                                        <th style="width: 25%">Notes</th>
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
                                <div class="alert alert-info">
                                    <strong>Total Debit:</strong>
                                    <span id="totalDebit" class="fw-bold">0.00</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <strong>Total Credit:</strong>
                                    <span id="totalCredit" class="fw-bold">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @error('entries')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save"></i> Save Journal Entry
                        </button>
                        <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Entries
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let entryCount = 0;
const ledgers = @json($ledgers);

// Add initial two entries
document.addEventListener('DOMContentLoaded', function() {
    addEntry();
    addEntry();
});

function addEntry() {
    const tbody = document.getElementById('entriesTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select class="form-select" name="entries[${entryCount}][ledger_id]" required onchange="updateTotals()">
                <option value="">Select Ledger</option>
                ${ledgers.map(ledger => `<option value="${ledger.id}">${ledger.name}</option>`).join('')}
            </select>
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
            <input type="text" class="form-control" name="entries[${entryCount}][notes]"
                   placeholder="Optional notes">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeEntry(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    entryCount++;
}

function removeEntry(button) {
    const tbody = document.getElementById('entriesTableBody');
    if (tbody.children.length > 2) {
        button.closest('tr').remove();
        updateTotals();
    } else {
        alert('At least 2 entries are required for a journal entry.');
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

    // Enable/disable submit button based on balance
    const submitBtn = document.getElementById('submitBtn');
    const isBalanced = Math.abs(totalDebit - totalCredit) < 0.01 && totalDebit > 0;
    submitBtn.disabled = !isBalanced;

    if (isBalanced) {
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    } else {
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
    }
}
</script>
@endsection
