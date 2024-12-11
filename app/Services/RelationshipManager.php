<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Relationship;
use Illuminate\Support\Collection;

class RelationshipManager
{
    protected RelationshipTypeRegistry $typeRegistry;

    public function __construct(RelationshipTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function createRelationship(
        Entity $from,
        Entity $to,
        string $type,
        array $metadata = []
    ): Relationship {
        // Validate relationship type and metadata
        $this->typeRegistry->validateRelationship($type, $from, $to, $metadata);

        // Create the relationship
        $relationship = new Relationship([
            'from_entity_id' => $from->id,
            'to_entity_id' => $to->id,
            'type' => $type,
            'metadata' => $metadata,
        ]);
        $relationship->save();

        // If bidirectional, create inverse relationship
        $relationType = $this->typeRegistry->get($type);
        if ($relationType->is_bidirectional) {
            $inverseType = $relationType->inverse_name ?? $type;
            $inverseRelationship = new Relationship([
                'from_entity_id' => $to->id,
                'to_entity_id' => $from->id,
                'type' => $inverseType,
                'metadata' => $metadata,
                'inverse_of' => $relationship->id,
            ]);
            $inverseRelationship->save();
            
            $relationship->inverse_of = $inverseRelationship->id;
            $relationship->save();
        }

        return $relationship;
    }

    public function getRelationships(
        Entity $entity,
        ?string $type = null,
        bool $incoming = false
    ): Collection {
        $query = Relationship::query()
            ->where($incoming ? 'to_entity_id' : 'from_entity_id', $entity->id);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function deleteRelationship(Relationship $relationship, bool $force = false): void
    {
        // Handle bidirectional relationship deletion
        if ($relationship->inverse_of) {
            $inverse = Relationship::find($relationship->inverse_of);
            if ($inverse) {
                $force ? $inverse->forceDelete() : $inverse->delete();
            }
        }

        $force ? $relationship->forceDelete() : $relationship->delete();
    }

    public function traverse(
        Entity $startEntity,
        array $relationshipTypes,
        int $maxDepth = 3
    ): Collection {
        $visited = collect();
        $this->traverseRecursive($startEntity, $relationshipTypes, $visited, $maxDepth);
        return $visited;
    }

    protected function traverseRecursive(
        Entity $entity,
        array $relationshipTypes,
        Collection $visited,
        int $maxDepth,
        int $currentDepth = 0
    ): void {
        if ($currentDepth >= $maxDepth || $visited->contains($entity->id)) {
            return;
        }

        $visited->push($entity->id);

        foreach ($relationshipTypes as $type) {
            $relationships = $this->getRelationships($entity, $type);
            foreach ($relationships as $relationship) {
                $nextEntity = Entity::find($relationship->to_entity_id);
                if ($nextEntity) {
                    $this->traverseRecursive(
                        $nextEntity,
                        $relationshipTypes,
                        $visited,
                        $maxDepth,
                        $currentDepth + 1
                    );
                }
            }
        }
    }
}