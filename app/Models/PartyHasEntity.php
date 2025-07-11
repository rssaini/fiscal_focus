<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PartyHasEntity extends Model
{
    use HasFactory;

    protected $table = 'party_has_entities';

    protected $fillable = [
        'party_id',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'party_id' => 'integer',
        'model_id' => 'integer',
    ];

    /**
     * Get the party that owns this relationship.
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Get the entity (polymorphic relation).
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Scope to filter by specific entity type.
     */
    public function scopeForModelType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by specific entity.
     */
    public function scopeForEntity($query, Model $entity)
    {
        return $query->where('model_type', get_class($entity))
                    ->where('model_id', $entity->id);
    }

    /**
     * Scope to filter by party.
     */
    public function scopeForParty($query, $partyId)
    {
        return $query->where('party_id', $partyId);
    }

    /**
     * Check if a relationship exists between party and entity.
     */
    public static function relationshipExists(int $partyId, Model $entity): bool
    {
        return static::where('party_id', $partyId)
                    ->where('model_type', get_class($entity))
                    ->where('model_id', $entity->id)
                    ->exists();
    }

    /**
     * Create a relationship between party and entity.
     */
    public static function createRelationship(int $partyId, Model $entity): self
    {
        return static::firstOrCreate([
            'party_id' => $partyId,
            'model_type' => get_class($entity),
            'model_id' => $entity->id,
        ]);
    }

    /**
     * Remove a relationship between party and entity.
     */
    public static function removeRelationship(int $partyId, Model $entity): bool
    {
        return static::where('party_id', $partyId)
                    ->where('model_type', get_class($entity))
                    ->where('model_id', $entity->id)
                    ->delete() > 0;
    }

    /**
     * Get all entities for a specific party.
     */
    public static function getEntitiesForParty(int $partyId)
    {
        return static::where('party_id', $partyId)
                    ->with('entity')
                    ->get()
                    ->pluck('entity');
    }

    /**
     * Get all parties for a specific entity.
     */
    public static function getPartiesForEntity(Model $entity)
    {
        return static::where('model_type', get_class($entity))
                    ->where('model_id', $entity->id)
                    ->with('party')
                    ->get()
                    ->pluck('party');
    }

    /**
     * Get count of entities by type for a party.
     */
    public static function getEntityCountByType(int $partyId): array
    {
        return static::where('party_id', $partyId)
                    ->selectRaw('model_type, COUNT(*) as count')
                    ->groupBy('model_type')
                    ->pluck('count', 'model_type')
                    ->toArray();
    }

    /**
     * Boot the model to add event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Add any model events if needed
        static::creating(function ($model) {
            // Validate that the entity exists before creating relationship
            if (!class_exists($model->model_type)) {
                throw new \InvalidArgumentException("Model type {$model->model_type} does not exist.");
            }

            $entityClass = $model->model_type;
            if (!$entityClass::find($model->model_id)) {
                throw new \InvalidArgumentException("Entity with ID {$model->model_id} not found in {$model->model_type}.");
            }
        });
    }

    /**
     * Get the entity's display name (if the entity has a name method/attribute).
     */
    public function getEntityDisplayNameAttribute(): string
    {
        $entity = $this->entity;

        if (!$entity) {
            return 'Unknown Entity';
        }

        // Try different common name attributes/methods
        if (method_exists($entity, 'getDisplayNameAttribute') || isset($entity->display_name)) {
            return $entity->display_name;
        }

        if (isset($entity->name)) {
            return $entity->name;
        }

        if (isset($entity->title)) {
            return $entity->title;
        }

        if (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        return class_basename($this->model_type) . ' #' . $this->model_id;
    }

    /**
     * Get the entity type in a human-readable format.
     */
    public function getEntityTypeDisplayAttribute(): string
    {
        return str_replace('App\\Models\\', '', $this->model_type);
    }
}
