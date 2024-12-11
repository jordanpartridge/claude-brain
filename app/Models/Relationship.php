<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relationship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from_entity_id',
        'to_entity_id',
        'type',
        'inverse_of',
        'metadata',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function fromEntity()
    {
        return $this->belongsTo(Entity::class, 'from_entity_id');
    }

    public function toEntity()
    {
        return $this->belongsTo(Entity::class, 'to_entity_id');
    }

    public function inverseRelationship()
    {
        return $this->belongsTo(Relationship::class, 'inverse_of');
    }

    public function relationType()
    {
        return $this->belongsTo(RelationshipType::class, 'type', 'name');
    }
}