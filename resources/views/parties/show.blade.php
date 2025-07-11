@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Party Details: {{ $party->name }}</h4>
                    <div>
                        <a href="{{ route('parties.edit', $party) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('parties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $party->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $party->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Limit:</strong></td>
                                    <td>₹{{ number_format($party->credit_limit, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Credit Days:</strong></td>
                                    <td>{{ $party->credit_days }} days</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $party->created_at->format('d M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $party->updated_at->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Summary Information -->
                        <div class="col-md-6">
                            <h5>Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-info text-white mb-3">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Total Contacts</h6>
                                            <h4>{{ $party->contacts->count() }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-success text-white mb-3">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Linked Entities</h6>
                                            <h4>{{ $party->total_entities_count }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Entity Type Breakdown -->
                            @if($entityCounts)
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Entity Breakdown</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($entityCounts as $modelType => $count)
                                            @php
                                                $displayType = str_replace('App\\Models\\', '', $modelType);
                                                $percentage = $party->total_entities_count > 0 ?
                                                    round(($count / $party->total_entities_count) * 100) : 0;
                                                $progressClass = match($displayType) {
                                                    'Customer' => 'bg-primary',
                                                    'Supplier' => 'bg-success',
                                                    'Employee' => 'bg-info',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>{{ $displayType }}s</span>
                                                    <span>{{ $count }} ({{ $percentage }}%)</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar {{ $progressClass }}"
                                                         style="width: {{ $percentage }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Contact Information</h5>
                            <hr>

                            @if ($party->contacts->count() > 0)
                                <div class="row">
                                    @foreach ($party->contacts as $contact)
                                        <div class="col-md-6 mb-4">
                                            <div class="card {{ $contact->is_primary ? 'border-primary' : '' }}">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">{{ $contact->name }}</h6>
                                                    @if ($contact->is_primary)
                                                        <span class="badge bg-primary">Primary</span>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    @if ($contact->designation)
                                                        <p class="text-muted mb-2">{{ $contact->designation }}</p>
                                                    @endif

                                                    <div class="contact-details">
                                                        @if ($contact->email)
                                                            <div class="mb-2">
                                                                <i class="fas fa-envelope text-muted me-2"></i>
                                                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                                            </div>
                                                        @endif

                                                        @if ($contact->phone)
                                                            <div class="mb-2">
                                                                <i class="fas fa-phone text-muted me-2"></i>
                                                                <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                                                            </div>
                                                        @endif

                                                        @if ($contact->mobile)
                                                            <div class="mb-2">
                                                                <i class="fas fa-mobile-alt text-muted me-2"></i>
                                                                <a href="tel:{{ $contact->mobile }}">{{ $contact->mobile }}</a>
                                                            </div>
                                                        @endif

                                                        @if (!$contact->email && !$contact->phone && !$contact->mobile)
                                                            <p class="text-muted">No contact information available</p>
                                                        @endif
                                                    </div>

                                                    @if ($contact->notes)
                                                        <div class="mt-3">
                                                            <small class="text-muted">
                                                                <strong>Notes:</strong> {{ $contact->notes }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-footer text-muted">
                                                    <small>
                                                        Added: {{ $contact->created_at->format('d M Y') }}
                                                        @if ($contact->created_at != $contact->updated_at)
                                                            | Updated: {{ $contact->updated_at->format('d M Y') }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No contacts have been added for this party yet.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Linked Entities -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Linked Entities</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#linkEntityModal">
                                    <i class="fas fa-link"></i> Link Entity
                                </button>
                            </div>
                            <hr>

                            @if($party->entityRelationships->count() > 0)
                                @php
                                    $groupedEntities = $party->entityRelationships->groupBy('model_type');
                                @endphp

                                @foreach($groupedEntities as $modelType => $relationships)
                                    @php
                                        $displayType = str_replace('App\\Models\\', '', $modelType);
                                        $iconClass = match($displayType) {
                                            'Customer' => 'fas fa-user',
                                            'Supplier' => 'fas fa-truck',
                                            'Employee' => 'fas fa-user-tie',
                                            default => 'fas fa-box'
                                        };
                                        $cardClass = match($displayType) {
                                            'Customer' => 'border-primary',
                                            'Supplier' => 'border-success',
                                            'Employee' => 'border-info',
                                            default => 'border-secondary'
                                        };
                                    @endphp

                                    <div class="card mb-3 {{ $cardClass }}">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="{{ $iconClass }} me-2"></i>
                                                {{ $displayType }}s ({{ $relationships->count() }})
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($relationships as $relationship)
                                                    @if($relationship->entity)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                                                <div>
                                                                    <strong>{{ $relationship->entity->name ?? 'Unknown' }}</strong>
                                                                    @if(isset($relationship->entity->email))
                                                                        <br><small class="text-muted">{{ $relationship->entity->email }}</small>
                                                                    @endif
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-outline-danger unlink-entity-btn"
                                                                        data-entity-type="{{ $relationship->model_type }}"
                                                                        data-entity-id="{{ $relationship->model_id }}"
                                                                        data-entity-name="{{ $relationship->entity->name ?? 'Unknown' }}">
                                                                    <i class="fas fa-unlink"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No entities are currently linked to this party.
                                    <button type="button" class="btn btn-outline-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#linkEntityModal">
                                        Link an Entity
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('parties.edit', $party) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit Party
                                    </a>
                                </div>
                                <div>
                                    <form action="{{ route('parties.destroy', $party) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this party and all associated contacts and entity relationships? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete Party
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link Entity Modal -->
<div class="modal fade" id="linkEntityModal" tabindex="-1" aria-labelledby="linkEntityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkEntityModalLabel">Link Entity to Party</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="linkEntityForm">
                    @csrf
                    <div class="mb-3">
                        <label for="entityType" class="form-label">Entity Type</label>
                        <select class="form-select" id="entityType" name="entity_type" required>
                            <option value="">Select Entity Type</option>
                            <option value="App\Models\Customer">Customer</option>
                            <option value="App\Models\Supplier">Supplier</option>
                            <option value="App\Models\Employee">Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entityId" class="form-label">Entity</label>
                        <select class="form-select" id="entityId" name="entity_id" required disabled>
                            <option value="">Select Entity Type First</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="linkEntityBtn" disabled>Link Entity</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const entityTypeSelect = document.getElementById('entityType');
    const entityIdSelect = document.getElementById('entityId');
    const linkEntityBtn = document.getElementById('linkEntityBtn');
    const partyId = {{ $party->id }};

    // Handle entity type change
    entityTypeSelect.addEventListener('change', function() {
        const entityType = this.value;
        entityIdSelect.innerHTML = '<option value="">Loading...</option>';
        entityIdSelect.disabled = true;
        linkEntityBtn.disabled = true;

        if (entityType) {
            fetch('{{ route("parties.get-entities-by-type") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ entity_type: entityType })
            })
            .then(response => response.json())
            .then(data => {
                entityIdSelect.innerHTML = '<option value="">Select Entity</option>';
                if (data.success && data.entities.length > 0) {
                    data.entities.forEach(entity => {
                        entityIdSelect.innerHTML += `<option value="${entity.id}">${entity.name}</option>`;
                    });
                    entityIdSelect.disabled = false;
                } else {
                    entityIdSelect.innerHTML = '<option value="">No entities available</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                entityIdSelect.innerHTML = '<option value="">Error loading entities</option>';
            });
        } else {
            entityIdSelect.innerHTML = '<option value="">Select Entity Type First</option>';
        }
    });

    // Handle entity selection
    entityIdSelect.addEventListener('change', function() {
        linkEntityBtn.disabled = !this.value;
    });

    // Handle link entity
    linkEntityBtn.addEventListener('click', function() {
        const entityType = entityTypeSelect.value;
        const entityId = entityIdSelect.value;

        if (!entityType || !entityId) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Linking...';

        fetch('{{ route("parties.link-entity", $party) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                entity_type: entityType,
                entity_id: entityId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show the new linked entity
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
                this.innerHTML = 'Link Entity';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while linking the entity.');
            this.disabled = false;
            this.innerHTML = 'Link Entity';
        });
    });

    // Handle unlink entity
    document.querySelectorAll('.unlink-entity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const entityType = this.dataset.entityType;
            const entityId = this.dataset.entityId;
            const entityName = this.dataset.entityName;

            if (confirm(`Are you sure you want to unlink ${entityName}?`)) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch('{{ route("parties.unlink-entity", $party) }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        entity_type: entityType,
                        entity_id: entityId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload to remove the unlinked entity
                    } else {
                        alert('Error: ' + data.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-unlink"></i>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while unlinking the entity.');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-unlink"></i>';
                });
            }
        });
    });
});
</script>
@endsection
