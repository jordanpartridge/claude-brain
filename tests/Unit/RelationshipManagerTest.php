<?php

namespace Tests\Unit;

use App\Models\Entity;
use App\Models\RelationshipType;
use App\Services\RelationshipManager;
use App\Services\RelationshipTypeRegistry;
use Tests\TestCase;

class RelationshipManagerTest extends TestCase
{
    protected RelationshipManager $relationshipManager;
    protected RelationshipTypeRegistry $typeRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->typeRegistry = new RelationshipTypeRegistry();
        $this->relationshipManager = new RelationshipManager($this->typeRegistry);
    }

    public function test_create_valid_relationship()
    {
        $fromEntity = Entity::factory()->create(['type' => 'document']);
        $toEntity = Entity::factory()->create(['type' => 'project']);

        $metadata = [
            'created_by' => 'test_user',
            'priority' => 3
        ];

        $relationship = $this->relationshipManager->createRelationship(
            $fromEntity,
            $toEntity,
            'belongs_to',
            $metadata
        );

        $this->assertNotNull($relationship);
        $this->assertEquals($fromEntity->id, $relationship->from_entity_id);
        $this->assertEquals($toEntity->id, $relationship->to_entity_id);
        $this->assertEquals('belongs_to', $relationship->type);
        $this->assertEquals($metadata, $relationship->metadata);

        // Test bidirectional relationship
        $inverse = $relationship->inverseRelationship;
        $this->assertNotNull($inverse);
        $this->assertEquals($toEntity->id, $inverse->from_entity_id);
        $this->assertEquals($fromEntity->id, $inverse->to_entity_id);
        $this->assertEquals('has', $inverse->type);
    }

    public function test_invalid_entity_types()
    {
        $this->expectException(\InvalidArgumentException::class);

        $fromEntity = Entity::factory()->create(['type' => 'invalid_type']);
        $toEntity = Entity::factory()->create(['type' => 'project']);

        $this->relationshipManager->createRelationship(
            $fromEntity,
            $toEntity,
            'belongs_to',
            ['created_by' => 'test_user']
        );
    }

    public function test_missing_required_metadata()
    {
        $this->expectException(\InvalidArgumentException::class);

        $fromEntity = Entity::factory()->create(['type' => 'document']);
        $toEntity = Entity::factory()->create(['type' => 'project']);

        $this->relationshipManager->createRelationship(
            $fromEntity,
            $toEntity,
            'belongs_to',
            [] // Missing required created_by field
        );
    }

    public function test_invalid_metadata_validation()
    {
        $this->expectException(\InvalidArgumentException::class);

        $fromEntity = Entity::factory()->create(['type' => 'document']);
        $toEntity = Entity::factory()->create(['type' => 'project']);

        $this->relationshipManager->createRelationship(
            $fromEntity,
            $toEntity,
            'belongs_to',
            [
                'created_by' => 'test_user',
                'priority' => 10 // Invalid priority value
            ]
        );
    }

    public function test_relationship_traversal()
    {
        // Create a chain of entities
        $entities = collect();
        for ($i = 0; $i < 5; $i++) {
            $entities->push(Entity::factory()->create([
                'type' => $i % 2 == 0 ? 'document' : 'project'
            ]));
        }

        // Create relationships between them
        for ($i = 0; $i < 4; $i++) {
            $this->relationshipManager->createRelationship(
                $entities[$i],
                $entities[$i + 1],
                'belongs_to',
                ['created_by' => 'test_user']
            );
        }

        // Test traversal with different depths
        $result = $this->relationshipManager->traverse($entities[0], ['belongs_to'], 2);
        $this->assertEquals(3, $result->count());

        $result = $this->relationshipManager->traverse($entities[0], ['belongs_to'], 4);
        $this->assertEquals(5, $result->count());
    }
}
