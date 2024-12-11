<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'entity_id',
        'content',
        'source',
        'confidence',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence' => 'float',
        'metadata' => 'array'
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'entity_id' => 'required|exists:entities,id',
        'content' => 'required|string',
        'source' => 'nullable|string|max:100',
        'confidence' => 'nullable|numeric|between:0,1',
        'metadata' => 'nullable|array'
    ];

    /**
     * Get the entity that owns this observation.
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Scope a query to filter by confidence threshold.
     */
    public function scopeMinConfidence($query, float $threshold)
    {
        return $query->where('confidence', '>=', $threshold);
    }

    /**
     * Scope a query to filter by source.
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Check if the observation is considered reliable based on confidence.
     */
    public function isReliable(float $threshold = 0.7): bool
    {
        return $this->confidence >= $threshold;
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
        return data_get($this->metadata, $key, $default);
    }
}
