{{-- resources/views/purchases/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Purchase Details - #' . $purchase->rec_no)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Purchase Record Details
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog"></i> Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                                        <i class="fas fa-print"></i> Print
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportToPdf()">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i> Delete Record
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        {{-- Purchase Information --}}
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Purchase Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Record Number:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-primary fs-6">{{ $purchase->rec_no }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Token Number:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-info fs-6">{{ $purchase->token_no }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Date & Time:</strong></div>
                                        <div class="col-sm-8">
                                            <i class="fas fa-calendar"></i>
                                            {{ $purchase->datetime->format('d M, Y') }}
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i>
                                                {{ $purchase->datetime->format('h:i A') }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Usage Type:</strong></div>
                                        <div class="col-sm-8">
                                            {!! $purchase->use_at_badge !!}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Commission:</strong></div>
                                        <div class="col-sm-8">
                                            <span class="fw-bold text-success">{{ $purchase->formatted_commission }}</span>
                                        </div>
                                    </div>

                                    @if($purchase->notes)
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Notes:</strong></div>
                                        <div class="col-sm-8">
                                            <div class="alert alert-light p-2 mb-0">
                                                <small>{{ $purchase->notes }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Weight Information --}}
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-weight"></i> Weight Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Gross Weight:</strong></div>
                                        <div class="col-sm-7">
                                            <span class="fw-bold text-primary">{{ $purchase->formatted_gross_wt }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Tare Weight:</strong></div>
                                        <div class="col-sm-7">
                                            <span class="fw-bold text-warning">{{ $purchase->formatted_tare_wt }}</span>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Net Weight:</strong></div>
                                        <div class="col-sm-7">
                                            <span class="fw-bold text-success fs-5">{{ $purchase->formatted_net_wt }}</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Weight (Tons):</strong></div>
                                        <div class="col-sm-7">
                                            <span class="fw-bold text-dark fs-4">{{ $purchase->formatted_wt_ton }}</span>
                                        </div>
                                    </div>

                                    {{-- Weight Calculation Breakdown --}}
                                    <div class="alert alert-info p-2 mt-3">
                                        <small>
                                            <strong>Calculation:</strong><br>
                                            Net = {{ number_format($purchase->gross_wt) }} - {{ number_format($purchase->tare_wt) }} = {{ number_format($purchase->net_wt) }} kg<br>
                                            Tons = {{ number_format($purchase->net_wt) }} ÷ 1000 = {{ number_format($purchase->wt_ton, 2) }} tons
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        {{-- Mines Information --}}
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-mountain"></i> Mines Information</h6>
                                </div>
                                <div class="card-body">
                                    @if($purchase->mines)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>ML Number:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-warning text-dark">{{ $purchase->mines->ml_number }}</span>
                                            </div>
                                        </div>

                                        @if($purchase->mines->mines_name)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Mines Name:</strong></div>
                                            <div class="col-sm-8">{{ $purchase->mines->mines_name }}</div>
                                        </div>
                                        @endif

                                        @if($purchase->mines->owner_name)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Owner:</strong></div>
                                            <div class="col-sm-8">{{ $purchase->mines->owner_name }}</div>
                                        </div>
                                        @endif

                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Status:</strong></div>
                                            <div class="col-sm-8">{!! $purchase->mines->status_badge !!}</div>
                                        </div>

                                        @if($purchase->mines->contact_info != 'No contact information')
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Contact:</strong></div>
                                            <div class="col-sm-8">
                                                <small>{{ $purchase->mines->contact_info }}</small>
                                            </div>
                                        </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Mines information not available
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Vehicle Information --}}
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-truck"></i> Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    @if($purchase->vehicle)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Vehicle Number:</strong></div>
                                            <div class="col-sm-8">
                                                <span class="badge bg-info fs-6">{{ $purchase->vehicle->vehicle_number }}</span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Driver:</strong></div>
                                            <div class="col-sm-8">
                                                <i class="fas fa-user"></i> <strong>{{ $purchase->driver }}</strong>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Status:</strong></div>
                                            <div class="col-sm-8">{!! $purchase->vehicle->status_badge !!}</div>
                                        </div>

                                        @if($purchase->vehicle->tare_weight)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Vehicle Tare:</strong></div>
                                            <div class="col-sm-8">{{ $purchase->vehicle->formatted_tare_weight }}</div>
                                        </div>
                                        @endif

                                        @if($purchase->vehicle->load_capacity)
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Load Capacity:</strong></div>
                                            <div class="col-sm-8">{{ $purchase->vehicle->formatted_load_capacity }}</div>
                                        </div>
                                        @endif

                                        @if($purchase->vehicle->contact_info != 'No contact information')
                                        <div class="row mb-3">
                                            <div class="col-sm-4"><strong>Contact:</strong></div>
                                            <div class="col-sm-8">
                                                <small>{{ $purchase->vehicle->contact_info }}</small>
                                            </div>
                                        </div>
                                        @endif
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Vehicle information not available
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Record Metadata --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-history"></i> Record Metadata</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Created:</strong><br>
                                            <small class="text-muted">
                                                {{ $purchase->created_at->format('d M, Y h:i A') }}
                                                <br>({{ $purchase->created_at->diffForHumans() }})
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Last Updated:</strong><br>
                                            <small class="text-muted">
                                                {{ $purchase->updated_at->format('d M, Y h:i A') }}
                                                <br>({{ $purchase->updated_at->diffForHumans() }})
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Record ID:</strong><br>
                                            <span class="badge bg-secondary">#{{ $purchase->id }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Database Status:</strong><br>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-light">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group flex-wrap gap-2" role="group">
                                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Purchase
                                        </a>
                                        <a href="{{ route('purchases.create') }}" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Create Similar
                                        </a>
                                        <button type="button" class="btn btn-info" onclick="copyToClipboard()">
                                            <i class="fas fa-copy"></i> Copy Details
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="emailRecord()">
                                            <i class="fas fa-envelope"></i> Email Record
                                        </button>
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

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to delete this purchase record?</strong></p>
                <div class="alert alert-warning">
                    <ul class="mb-0">
                        <li>Record #: <strong>{{ $purchase->rec_no }}</strong></li>
                        <li>Token #: <strong>{{ $purchase->token_no }}</strong></li>
                        <li>Date: <strong>{{ $purchase->datetime->format('d M, Y') }}</strong></li>
                        <li>Weight: <strong>{{ $purchase->formatted_wt_ton }}</strong></li>
                    </ul>
                </div>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Purchase
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    @media print {
        .card-tools, .btn-group, .quick-actions, .card-header .btn {
            display: none !important;
        }

        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }

        .card-header {
            background: #f8f9fa !important;
            color: #000 !important;
        }

        .badge {
            border: 1px solid #000 !important;
        }
    }

    .fs-6 {
        font-size: 1rem !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // Global functions
    window.confirmDelete = function() {
        deleteModal.show();
    };

    window.exportToPdf = function() {
        // You can implement PDF export functionality here
        alert('PDF export functionality can be implemented based on your requirements.');
    };

    window.copyToClipboard = function() {
        const details = `
Purchase Record Details:
Record #: {{ $purchase->rec_no }}
Token #: {{ $purchase->token_no }}
Date: {{ $purchase->datetime->format('d M, Y h:i A') }}
Mines: {{ $purchase->mines->display_name ?? 'N/A' }}
Vehicle: {{ $purchase->vehicle->vehicle_number ?? 'N/A' }}
Driver: {{ $purchase->driver }}
Gross Weight: {{ $purchase->formatted_gross_wt }}
Tare Weight: {{ $purchase->formatted_tare_wt }}
Net Weight: {{ $purchase->formatted_net_wt }}
Weight (Tons): {{ $purchase->formatted_wt_ton }}
Commission: {{ $purchase->formatted_commission }}
Usage: {{ ucfirst($purchase->use_at) }}
        `.trim();

        navigator.clipboard.writeText(details).then(function() {
            showAlert('Purchase details copied to clipboard!', 'success');
        }).catch(function() {
            showAlert('Failed to copy to clipboard', 'error');
        });
    };

    window.emailRecord = function() {
        // You can implement email functionality here
        alert('Email functionality can be implemented based on your requirements.');
    };

    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert alert at the top of the card body
        const cardBody = document.querySelector('.card-body');
        cardBody.insertAdjacentHTML('afterbegin', alertHtml);

        // Auto-remove after 3 seconds
        setTimeout(function() {
            const alert = cardBody.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000);
    }
});
</script>
@endpush
