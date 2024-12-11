# Entity Model Documentation

## Overview
The Entity model serves as the foundation of our knowledge graph system. It represents any discrete concept, object, or actor that can be tracked and related to other entities.

## Properties
- `id`: Primary key
- `type`: String (50 chars) - Type of entity (user, project, concept, etc.)
- `name`: String (255 chars) - Identifier for the entity
- `metadata`: JSON - Flexible storage for entity-specific data
- `created_at`, `updated_at`, `deleted_at`: Timestamps

## Relationships
```php
// One Entity has many Observations
public function observations()
{
    return $this->hasMany(Observation::class);
}

// Entity can have many outgoing relationships (as source)
public function outgoingRelationships()
{
    return $this->hasMany(Relationship::class, 'from_entity_id');
}

// Entity can have many incoming relationships (as target)
public function incomingRelationships()
{
    return $this->hasMany(Relationship::class, 'to_entity_id');
}

// Entity can have many interactions as a user
public function interactions()
{
    return $this->hasMany(Interaction::class, 'user_entity_id');
}
```

## Query Scopes
```php
// Filter by entity type
public function scopeOfType($query, string $type)
{
    return $query->where('type', $type);
}

// Search by name
public function scopeNameLike($query, string $search)
{
    return $query->where('name', 'like', "%{$search}%");
}

// Get entities with specific metadata key-value
public function scopeWithMetadata($query, string $key, $value)
{
    return $query->whereJsonContains("metadata->{$key}", $value);
}
```

## Helper Methods
```php
// Add an observation to this entity
public function addObservation(string $content, ?string $source = null, ?float $confidence = null, ?array $metadata = null)

// Create a relationship to another entity
public function relateTo(Entity $target, string $type, ?array $metadata = null)

// Get all related entities of a specific type through outgoing relationships
public function getRelatedOfType(string $relationType)

// Get all observations within a specific confidence range
public function getObservationsWithConfidence(float $min, float $max = 1.0)

// Update metadata without overwriting existing values
public function updateMetadata(array $newData)

// Get a metadata value with dot notation support
public function getMetadataValue(string $key, $default = null)
```

## Validation Rules
```php
public static $rules = [
    'type' => 'required|string|max:50',
    'name' => 'required|string|max:255',
    'metadata' => 'nullable|json'
];
```

## Factory Definition
```php
public function definition(): array
{
    return [
        'type' => $this->faker->randomElement(['user', 'project', 'concept', 'task']),
        'name' => $this->faker->words(3, true),
        'metadata' => [
            'description' => $this->faker->sentence(),
            'tags' => $this->faker->words(3)
        ]
    ];
}
```

## Usage Examples
```php
// Create a new entity
$entity = Entity::create([
    'type' => 'project',
    'name' => 'Knowledge Graph Implementation',
    'metadata' => [
        'status' => 'active',
        'priority' => 'high'
    ]
]);

// Add an observation
$entity->addObservation(
    'Initial database schema completed',
    'commit-log',
    1.0,
    ['commit_hash' => 'abc123']
);

// Create a relationship
$entity->relateTo($otherEntity, 'depends_on', ['critical' => true]);

// Find entities by type with specific metadata
$activeProjects = Entity::ofType('project')
    ->withMetadata('status', 'active')
    ->get();
```