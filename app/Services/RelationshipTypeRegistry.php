<?php

namespace App\Services;

use App\Models\RelationshipType;
use App\Models\Entity;
use Illuminate\Support\Collection;

class RelationshipTypeRegistry
{
    protected Collection $types;

    public function __construct()
    {
        $this->types = collect();
        $this->loadTypes();
    }

    protected function loadTypes(): void
    {
        $this->types = RelationshipType::all()->keyBy('name');
    }

    public function register(array $typeData): RelationshipType
    {
        $type = RelationshipType::create($typeData);
        $this->types->put($type->name, $type);
        return $type;
    }

    public function get(string $name): ?RelationshipType
    {
        return $this->types->get($name);
    }

    public function exists(string $name): bool
    {
        return $this->types->has($name);
    }

    public function validateRelationship(string $typeName, Entity $from, Entity $to, array $metadata = []): bool
    {
        $type = $this->get($typeName);
        
        if (!$type) {
            throw new \InvalidArgumentException("Relationship type '{$typeName}' does not exist");
        }

        return $type->validateRelationship($from, $to, $metadata);
    }

    public function getAllTypes(): Collection
    {
        return $this->types;
    }
}