<?php

use App\Models\Entity;
use App\Models\Observation;
use App\Models\Relationship;

test('can create an entity', function() {
    $entity = Entity::factory()->create([
        'type' => 'project',
        'name' => 'Test Project',
        'metadata' => ['status' => 'active']
    ]);

    expect($entity)
        ->toBeInstanceOf(Entity::class)
        ->name->toBe('Test Project')
        ->type->toBe('project')
        ->getMetadataValue('status')->toBe('active');
});

test('can add an observation to entity', function() {
    $entity = Entity::factory()->create();
    
    $observation = $entity->addObservation(
        content: 'Test observation',
        source: 'test',
        confidence: 0.9,
        metadata: ['key' => 'value']
    );

    expect($observation)
        ->toBeInstanceOf(Observation::class)
        ->content->toBe('Test observation')
        ->confidence->toBe(0.9)
        ->source->toBe('test');

    expect($entity->observations)->toHaveCount(1);
});

test('can create relationships between entities', function() {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();

    $relationship = $entity1->relateTo($entity2, 'depends_on', ['critical' => true]);

    expect($relationship)
        ->toBeInstanceOf(Relationship::class)
        ->type->toBe('depends_on')
        ->from_entity_id->toBe($entity1->id)
        ->to_entity_id->toBe($entity2->id);

    expect($relationship->metadata)
        ->toBeArray()
        ->toHaveKey('critical', true);
});

test('can find entities by type', function() {
    Entity::factory()->count(2)->create(['type' => 'user']);
    Entity::factory()->count(3)->create(['type' => 'project']);

    expect(Entity::ofType('user')->get())->toHaveCount(2);
    expect(Entity::ofType('project')->get())->toHaveCount(3);
});

test('can search entities by name', function() {
    Entity::factory()->create(['name' => 'Test Project Alpha']);
    Entity::factory()->create(['name' => 'Test Project Beta']);
    Entity::factory()->create(['name' => 'Something Else']);

    expect(Entity::nameLike('Test Project')->get())
        ->toHaveCount(2)
        ->each(fn($entity) => 
            $entity->name->toContain('Test Project')
        );
});

test('can filter entities by metadata', function() {
    Entity::factory()->create(['metadata' => ['status' => 'active']]);
    Entity::factory()->create(['metadata' => ['status' => 'inactive']]);

    expect(Entity::withMetadata('status', 'active')->get())
        ->toHaveCount(1)
        ->first()
        ->metadata->toHaveKey('status', 'active');
});

test('can update metadata without overwriting existing values', function() {
    $entity = Entity::factory()->create([
        'metadata' => ['existing' => 'value']
    ]);

    $entity->updateMetadata(['new' => 'data']);

    expect($entity->metadata)
        ->toHaveKey('existing', 'value')
        ->toHaveKey('new', 'data');
});

test('can traverse relationships of specific type', function() {
    $root = Entity::factory()->create();
    createGraphStructure($root, 2);

    expect($root->getRelatedOfType('contains'))
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Entity::class);
});

test('can get observations within confidence range', function() {
    $entity = Entity::factory()->create();
    
    $entity->addObservation('High confidence', 'test', 0.9);
    $entity->addObservation('Medium confidence', 'test', 0.5);
    $entity->addObservation('Low confidence', 'test', 0.2);

    $highConfidenceObs = $entity->getObservationsWithConfidence(0.8, 1.0);
    
    expect($highConfidenceObs)
        ->toHaveCount(1)
        ->first()
        ->content->toBe('High confidence');
});

test('validates entity types')
    ->with([
        'valid user' => ['user', true],
        'valid project' => ['project', true],
        'invalid type' => ['invalid_type', false],
    ])
    ->expect(fn ($type, $valid) => 
        fn() => Entity::factory()->create(['type' => $type])
    )
    ->toBeValid();

test('entity soft deletes preserve relationships', function() {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    
    $entity1->relateTo($entity2, 'owns');
    $entity1->delete();
    
    expect(Entity::withTrashed()->find($entity1->id))
        ->not->toBeNull()
        ->outgoingRelationships
        ->toHaveCount(1);
});
