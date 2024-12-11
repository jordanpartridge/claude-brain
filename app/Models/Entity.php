<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Entity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entity) {
            $validator = Validator::make($entity->getAttributes(), [
                'name' => 'required|string|min:1',
                'type' => 'required|string|max:50',
                'metadata' => 'nullable|json'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        });

        static::deleting(function ($entity) {
            $entity->observations()->delete();
            $entity->outgoingRelationships()->delete();
            $entity->incomingRelationships()->delete();
        });

        static::restoring(function ($entity) {
            $entity->observations()->withTrashed()->restore();
            $entity->outgoingRelationships()->withTrashed()->restore();
            $entity->incomingRelationships()->withTrashed()->restore();
        });
    }

    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    public function outgoingRelationships()
    {
        return $this->hasMany(Relationship::class, 'from_entity_id');
    }

    public function incomingRelationships()
    {
        return $this->hasMany(Relationship::class, 'to_entity_id');
    }

    public function addObservation($content, ?string $source = null, float $confidence = 1.0, ?array $metadata = null)
    {
        return $this->observations()->create([
            'content' => $content,
            'source' => $source,
            'confidence' => $confidence,
            'metadata' => $metadata
        ]);
    }

    public function relateTo(Entity $target, string $type, ?array $metadata = null)
    {
        // Prevent self-referential relationships
        if ($this->id === $target->id) {
            throw new \InvalidArgumentException('Cannot create self-referential relationship');
        }

        // Create the relationship
        $relationship = $this->outgoingRelationships()->create([
            'to_entity_id' => $target->id,
            'type' => $type,
            'metadata' => $metadata
        ]);

        return $relationship;
    }

    public function getRelatedOfType(string $type)
    {
        return Entity::whereHas('incomingRelationships', function ($query) use ($type) {
            $query->where('from_entity_id', $this->id)
                  ->where('type', $type);
        })->get();
    }

    public function updateMetadata(array $newData)
    {
        $currentMetadata = $this->metadata ?? [];
        $mergedMetadata = array_merge($currentMetadata, $newData);
        
        $this->metadata = $mergedMetadata;
        $this->save();
        
        return $this;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return data_get($this->metadata ?? [], $key, $default);
    }

    public function getObservationsWithConfidence(float $min, float $max)
    {
        return $this->observations()
            ->whereBetween('confidence', [$min, $max])
            ->get();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNameLike($query, string $pattern)
    {
        return $query->where('name', 'LIKE', "%{$pattern}%");
    }

    public function scopeWithMetadata($query, string $key, $value)
    {
        return $query->whereJsonContains("metadata->{$key}", $value);
    }

    public function getAllRelated(int $depth = null)
    {
        $result = collect();
        $visited = collect([$this->id]);
        $queue = collect([$this]);
        $currentDepth = 0;

        while ($queue->isNotEmpty() && (!$depth || $currentDepth < $depth)) {
            $currentSize = $queue->count();

            for ($i = 0; $i < $currentSize; $i++) {
                $current = $queue->shift();
                
                $related = $current->getRelatedOfType('contains');
                
                foreach ($related as $entity) {
                    if (!$visited->contains($entity->id)) {
                        $visited->push($entity->id);
                        $result->push($entity);
                        $queue->push($entity);
                    }
                }
            }

            $currentDepth++;
        }

        return $result;
    }
}
