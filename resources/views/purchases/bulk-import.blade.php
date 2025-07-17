{{-- Enhanced resources/views/purchases/bulk-import.blade.php with preview --}}
@extends('layouts.app')

@section('title', 'Bulk Import Purchases')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload"></i> Bulk Import Purchase Records
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Purchases
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-times-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Import Errors:</h6>
                            <ul class="mb-0" style="max-height: 200px; overflow-y: auto;">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Step 1: Template Download --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-download"></i> Step 1: Download Template</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small">Download the CSV template with sample data to understand the required format.</p>
                                    <a href="{{ route('purchases.download-template') }}" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: File Selection --}}
                        <div class="col-md-4">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-file-csv"></i> Step 2: Select File</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <input type="file"
                                               class="form-control form-control-sm"
                                               id="csv_file_preview"
                                               accept=".csv,.txt">
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm w-100" id="previewBtn" disabled>
                                        <i class="fas fa-eye"></i> Preview Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Import --}}
                        <div class="col-md-4">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-upload"></i> Step 3: Import</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small">Import your validated CSV file into the system.</p>
                                    <button type="button" class="btn btn-success btn-sm w-100" id="importBtn" disabled>
                                        <i class="fas fa-upload"></i> Import Records
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Section --}}
                    <div id="previewSection" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-eye"></i> File Preview</h6>
                        </div>
                        <div class="card-body">
                            <div id="previewContent">
                                <!-- Preview content will be loaded here -->
                            </div>
                        </div>
                    </div>

                    {{-- Import Form --}}
                    <div id="importSection" class="card" style="display: none;">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cog"></i> Import Settings</h6>
                        </div>
                        <div class="card-body">
                            <form id="importForm" action="{{ route('purchases.process-bulk-import') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <input type="file" id="csv_file_import" name="csv_file" style="display: none;">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="skip_duplicates" name="skip_duplicates" value="1">
                                                <label class="form-check-label" for="skip_duplicates">
                                                    <strong>Skip Duplicates</strong><br>
                                                    <small class="text-muted">Skip records that already exist (based on rec_no + token_no)</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="update_existing" name="update_existing" value="1">
                                                <label class="form-check-label" for="update_existing">
                                                    <strong>Update Existing</strong><br>
                                                    <small class="text-muted">Update existing records if duplicates are found</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" id="backToPreview">
                                        <i class="fas fa-arrow-left"></i> Back to Preview
                                    </button>
                                    <button type="submit" class="btn btn-success" id="finalImportBtn">
                                        <i class="fas fa-upload"></i> Start Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Format Reference --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> CSV Format Reference</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="15%">Column</th>
                                            <th width="25%">Description</th>
                                            <th width="10%">Required</th>
                                            <th width="50%">Format/Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>datetime</code></td>
                                            <td>Purchase date and time</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>YYYY-MM-DD HH:MM:SS or YYYY-MM-DD</td>
                                        </tr>
                                        <tr>
                                            <td><code>mines_id</code></td>
                                            <td>Mines ID</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Valid mines ID from system</td>
                                        </tr>
                                        <tr>
                                            <td><code>rec_no</code></td>
                                            <td>Record number</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Unique record number (max 255 chars)</td>
                                        </tr>
                                        <tr>
                                            <td><code>token_no</code></td>
                                            <td>Token number</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Token number (max 255 chars)</td>
                                        </tr>
                                        <tr>
                                            <td><code>vehicle_id</code></td>
                                            <td>Vehicle ID</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Valid vehicle ID from system</td>
                                        </tr>
                                        <tr>
                                            <td><code>gross_wt</code></td>
                                            <td>Gross weight</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Weight in kg (integer, must be > tare_wt)</td>
                                        </tr>
                                        <tr>
                                            <td><code>tare_wt</code></td>
                                            <td>Tare weight</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Weight in kg (integer, must be < gross_wt)</td>
                                        </tr>
                                        <tr>
                                            <td><code>driver</code></td>
                                            <td>Driver name</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Driver name (max 255 chars)</td>
                                        </tr>
                                        <tr>
                                            <td><code>commission</code></td>
                                            <td>Commission amount</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Decimal number (e.g., 500.00)</td>
                                        </tr>
                                        <tr>
                                            <td><code>use_at</code></td>
                                            <td>Usage type</td>
                                            <td><span class="badge bg-danger">Yes</span></td>
                                            <td>Either "stock" or "manufacturing"</td>
                                        </tr>
                                        <tr>
                                            <td><code>notes</code></td>
                                            <td>Additional notes</td>
                                            <td><span class="badge bg-secondary">No</span></td>
                                            <td>Optional text notes</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Reference Data --}}
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-mountain"></i> Available Mines</h6>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>ML Number</th>
                                                    <th>Name</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($mines as $mine)
                                                <tr>
                                                    <td>{{ $mine->id }}</td>
                                                    <td>{{ $mine->ml_number }}</td>
                                                    <td>{{ $mine->name }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-truck"></i> Available Vehicles</h6>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Vehicle Number</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vehicles as $vehicle)
                                                <tr>
                                                    <td>{{ $vehicle->id }}</td>
                                                    <td>{{ $vehicle->vehicle_number }}</td>
                                                    <td>{{ $vehicle->type ?? 'N/A' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Progress Modal --}}
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h6 id="progressText">Processing Import...</h6>
                <p class="text-muted">Please wait while we process your file.</p>
                <div class="progress mt-3">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedFile = null;
    let previewData = null;

    const csvFilePreview = document.getElementById('csv_file_preview');
    const csvFileImport = document.getElementById('csv_file_import');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');
    const previewSection = document.getElementById('previewSection');
    const importSection = document.getElementById('importSection');
    const importForm = document.getElementById('importForm');
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));

    // File selection handler
    csvFilePreview.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            if (!validateFile(file)) {
                this.value = '';
                return;
            }

            selectedFile = file;
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview Data';
        } else {
            selectedFile = null;
            previewBtn.disabled = true;
            importBtn.disabled = true;
            hidePreviewAndImport();
        }
    });

    // Preview button handler
    previewBtn.addEventListener('click', function() {
        if (!selectedFile) return;

        previewBtn.disabled = true;
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        previewCsvFile();
    });

    // Import button handler
    importBtn.addEventListener('click', function() {
        showImportSection();
    });

    // Back to preview button
    document.getElementById('backToPreview').addEventListener('click', function() {
        hideImportSection();
    });

    // Form submission
    importForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!selectedFile) {
            alert('Please select a file first.');
            return;
        }

        // Transfer file to import form
        const dt = new DataTransfer();
        dt.items.add(selectedFile);
        csvFileImport.files = dt.files;

        // Show progress modal
        progressModal.show();

        // Submit form
        this.submit();
    });

    // Checkbox logic
    const skipDuplicates = document.getElementById('skip_duplicates');
    const updateExisting = document.getElementById('update_existing');

    skipDuplicates.addEventListener('change', function() {
        if (this.checked) {
            updateExisting.checked = false;
        }
    });

    updateExisting.addEventListener('change', function() {
        if (this.checked) {
            skipDuplicates.checked = false;
        }
    });

    // Helper functions
    function validateFile(file) {
        // Check file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            return false;
        }

        // Check file type
        const allowedTypes = ['text/csv', 'text/plain'];
        if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().endsWith('.csv')) {
            alert('Please select a valid CSV file');
            return false;
        }

        return true;
    }

    function previewCsvFile() {
        const formData = new FormData();
        formData.append('csv_file', selectedFile);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('{{ route("purchases.preview-import") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            previewData = data;
            showPreviewSection(data);
            previewBtn.innerHTML = '<i class="fas fa-check"></i> Preview Loaded';
            importBtn.disabled = false;
        })
        .catch(error => {
            console.error('Preview error:', error);
            alert('Error previewing file: ' + error.message);
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview Data';
        });
    }

    function showPreviewSection(data) {
        let html = `
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>${data.total_rows}</h5>
                            <small>Total Rows</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ${data.missing_headers.length > 0 ? 'bg-danger' : 'bg-success'} text-white">
                        <div class="card-body text-center">
                            <h5>${data.headers.length}</h5>
                            <small>Columns Found</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card ${data.validation_errors ? 'bg-warning' : 'bg-success'} text-white">
                        <div class="card-body text-center">
                            <h5>${data.validation_errors ? 'Issues' : 'Valid'}</h5>
                            <small>Structure</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>${Math.round(selectedFile.size / 1024)} KB</h5>
                            <small>File Size</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (data.missing_headers.length > 0) {
            html += `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Missing Required Columns:</h6>
                    <ul class="mb-0">
                        ${data.missing_headers.map(header => `<li><code>${header}</code></li>`).join('')}
                    </ul>
                </div>
            `;
        }

        if (data.extra_headers.length > 0) {
            html += `
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle"></i> Extra Columns (will be ignored):</h6>
                    <ul class="mb-0">
                        ${data.extra_headers.map(header => `<li><code>${header}</code></li>`).join('')}
                    </ul>
                </div>
            `;
        }

        html += `
            <h6><i class="fas fa-table"></i> Preview (First 5 rows):</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            ${data.headers.map(header => `<th><code>${header}</code></th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${data.preview_data.map(row => `
                            <tr>
                                ${row.map(cell => `<td>${cell || '<em>empty</em>'}</td>`).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('previewContent').innerHTML = html;
        previewSection.style.display = 'block';

        // Scroll to preview
        previewSection.scrollIntoView({ behavior: 'smooth' });
    }

    function showImportSection() {
        importSection.style.display = 'block';
        importSection.scrollIntoView({ behavior: 'smooth' });
    }

    function hideImportSection() {
        importSection.style.display = 'none';
    }

    function hidePreviewAndImport() {
        previewSection.style.display = 'none';
        importSection.style.display = 'none';
        previewData = null;
    }
});
</script>
@endpush
