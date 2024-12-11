<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Entity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'name',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'type' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'metadata' => 'nullable|array'
    ];

    /**
     * Get the observations associated with this entity.
     */
    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    /**
     * Get outgoing relationships where this entity is the source.
     */
    public function outgoingRelationships()
    {
        return $this->hasMany(Relationship::class, 'from_entity_id');
    }

    /**
     * Get incoming relationships where this entity is the target.
     */
    public function incomingRelationships()
    {
        return $this->hasMany(Relationship::class, 'to_entity_id');
    }

    /**
     * Get interactions where this entity is the user.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'user_entity_id');
    }

    /**
     * Scope a query to filter entities by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to search entities by name.
     */
    public function scopeNameLike($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Scope a query to find entities with specific metadata.
     */
    public function scopeWithMetadata($query, string $key, $value)
    {
        return $query->whereJsonContains("metadata->{$key}", $value);
    }

    /**
     * Add an observation to this entity.
     */
    public function addObservation(string $content, ?string $source = null, ?float $confidence = null, ?array $metadata = null)
    {
        return $this->observations()->create([
            'content' => $content,
            'source' => $source,
            'confidence' => $confidence,
            'metadata' => $metadata
        ]);
    }

    /**
     * Create a relationship to another entity.
     */
    public function relateTo(Entity $target, string $type, ?array $metadata = null)
    {
        return $this->outgoingRelationships()->create([
            'to_entity_id' => $target->id,
            'type' => $type,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get all related entities of a specific relationship type.
     */
    public function getRelatedOfType(string $relationType)
    {
        return Entity::whereHas('incomingRelationships', function ($query) use ($relationType) {
            $query->where('from_entity_id', $this->id)
                  ->where('type', $relationType);
        })->get();
    }

    /**
     * Get observations within a specific confidence range.
     */
    public function getObservationsWithConfidence(float $min, float $max = 1.0)
    {
        return $this->observations()
            ->whereBetween('confidence', [$min, $max])
            ->get();
    }

    /**
     * Update metadata without overwriting existing values.
     */
    public function updateMetadata(array $newData)
    {
        $currentMetadata = $this->metadata ?? [];
        $mergedMetadata = array_merge($currentMetadata, $newData);
        
        $this->metadata = $mergedMetadata;
        $this->save();
        
        return $this;
    }

    /**
     * Get a metadata value using dot notation.
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return Arr::get($this->metadata ?? [], $key, $default);
    }
}
