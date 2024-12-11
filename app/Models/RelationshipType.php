<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelationshipType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'inverse_name',
        'is_bidirectional',
        'allowed_entity_types',
        'required_metadata_fields',
        'validation_rules',
    ];

    protected $casts = [
        'is_bidirectional' => 'boolean',
        'allowed_entity_types' => 'array',
        'required_metadata_fields' => 'array',
        'validation_rules' => 'array',
    ];

    public function validateRelationship(Entity $from, Entity $to, array $metadata = []): bool
    {
        // Validate entity types
        if (!empty($this->allowed_entity_types)) {
            $fromType = $from->type;
            $toType = $to->type;
            
            if (!in_array($fromType, $this->allowed_entity_types['from'] ?? []) ||
                !in_array($toType, $this->allowed_entity_types['to'] ?? [])) {
                throw new \InvalidArgumentException('Invalid entity types for this relationship');
            }
        }

        // Validate required metadata fields
        if (!empty($this->required_metadata_fields)) {
            $missingFields = array_diff($this->required_metadata_fields, array_keys($metadata));
            if (!empty($missingFields)) {
                throw new \InvalidArgumentException('Missing required metadata fields: ' . implode(', ', $missingFields));
            }
        }

        // Validate metadata values
        if (!empty($this->validation_rules)) {
            $validator = \Validator::make($metadata, $this->validation_rules);
            if ($validator->fails()) {
                throw new \InvalidArgumentException('Invalid metadata: ' . $validator->errors()->first());
            }
        }

        return true;
    }
}