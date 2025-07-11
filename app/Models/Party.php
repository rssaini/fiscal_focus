<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credit_limit',
        'credit_days',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
    ];

    /**
     * Get the contacts for the party.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(PartyContact::class);
    }

    /**
     * Get the primary contact for the party.
     */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(PartyContact::class)->where('is_primary', true);
    }

    /**
     * Get all entity relationships for this party.
     */
    public function entityRelationships(): HasMany
    {
        return $this->hasMany(PartyHasEntity::class);
    }

    /**
     * Get contacts by type.
     */
    public function getContactsByType($type)
    {
        return $this->contacts()->where('contact_type', $type)->get();
    }

    /**
     * Get phone contacts.
     */
    public function phoneContacts()
    {
        return $this->contacts()->where('phone', '!=', null);
    }

    /**
     * Get email contacts.
     */
    public function emailContacts()
    {
        return $this->contacts()->where('email', '!=', null);
    }

    /**
     * Get all entities of a specific type linked to this party.
     */
    public function getEntitiesByType(string $modelType)
    {
        return $this->entityRelationships()
                   ->where('model_type', $modelType)
                   ->with('entity')
                   ->get()
                   ->pluck('entity');
    }

    /**
     * Get customers linked to this party.
     */
    public function customers()
    {
        return $this->getEntitiesByType('App\Models\Customer');
    }

    /**
     * Get suppliers linked to this party.
     */
    public function suppliers()
    {
        return $this->getEntitiesByType('App\Models\Supplier');
    }

    /**
     * Get employees linked to this party.
     */
    public function employees()
    {
        return $this->getEntitiesByType('App\Models\Employee');
    }

    /**
     * Link an entity to this party.
     */
    public function linkEntity(Model $entity): PartyHasEntity
    {
        return PartyHasEntity::createRelationship($this->id, $entity);
    }

    /**
     * Unlink an entity from this party.
     */
    public function unlinkEntity(Model $entity): bool
    {
        return PartyHasEntity::removeRelationship($this->id, $entity);
    }

    /**
     * Check if an entity is linked to this party.
     */
    public function hasEntity(Model $entity): bool
    {
        return PartyHasEntity::relationshipExists($this->id, $entity);
    }

    /**
     * Get count of linked entities by type.
     */
    public function getEntityCountByType(): array
    {
        return PartyHasEntity::getEntityCountByType($this->id);
    }

    /**
     * Get total count of all linked entities.
     */
    public function getTotalEntitiesCountAttribute(): int
    {
        return $this->entityRelationships()->count();
    }

    /**
     * Get all linked entities regardless of type.
     */
    public function getAllLinkedEntities()
    {
        return $this->entityRelationships()
                   ->with('entity')
                   ->get()
                   ->pluck('entity');
    }

    /**
     * Get entity summary for display.
     */
    public function getEntitySummaryAttribute(): array
    {
        $counts = $this->getEntityCountByType();
        $summary = [];

        foreach ($counts as $modelType => $count) {
            $displayType = str_replace('App\\Models\\', '', $modelType);
            $summary[] = "{$count} " . str_plural($displayType, $count);
        }

        return $summary;
    }

    /**
     * Scope to search parties by name or contact information.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhereHas('contacts', function ($contactQuery) use ($search) {
                  $contactQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                              ->orWhere('mobile', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope to filter parties by linked entity type.
     */
    public function scopeWithEntityType($query, string $modelType)
    {
        return $query->whereHas('entityRelationships', function ($q) use ($modelType) {
            $q->where('model_type', $modelType);
        });
    }

    /**
     * Scope to filter parties by specific linked entity.
     */
    public function scopeWithEntity($query, Model $entity)
    {
        return $query->whereHas('entityRelationships', function ($q) use ($entity) {
            $q->where('model_type', get_class($entity))
              ->where('model_id', $entity->id);
        });
    }

    /**
     * Boot the model to add event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a party, all entity relationships are automatically deleted
        // due to cascade delete in the migration, but we can add additional cleanup here if needed
        static::deleting(function ($party) {
            // Any additional cleanup logic can go here
            // The entity relationships will be automatically deleted due to cascade delete
        });
    }

    /**
     * Get available entity types that can be linked to parties.
     */
    public static function getAvailableEntityTypes(): array
    {
        return [
            'App\Models\Customer' => 'Customers',
            'App\Models\Supplier' => 'Suppliers',
            'App\Models\Employee' => 'Employees',
            // Add more entity types as needed
        ];
    }

    /**
     * Get formatted display of linked entities.
     */
    public function getLinkedEntitiesDisplayAttribute(): string
    {
        $summary = $this->entity_summary;

        if (empty($summary)) {
            return 'No linked entities';
        }

        return implode(', ', $summary);
    }
}
