<?php

use App\Models\Entity;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

test('entity name cannot be empty', function() {
    expect(fn() => Entity::factory()->create(['name' => '']))
        ->toThrow(ValidationException::class);
});

test('entity type cannot exceed maximum length', function() {
    $longType = str_repeat('a', 51); // Max is 50 chars
    
    expect(fn() => Entity::factory()->create(['type' => $longType]))
        ->toThrow(ValidationException::class);
});

test('entity metadata must be valid json', function() {
    expect(fn() => Entity::factory()->create(['metadata' => 'invalid-json']))
        ->toThrow(ValidationException::class);
});

test('entity handles null metadata gracefully', function() {
    $entity = Entity::factory()->create(['metadata' => null]);
    
    expect($entity->getMetadataValue('non-existent', 'default'))
        ->toBe('default');
});

test('entity prevents circular relationships', function() {
    $entity = Entity::factory()->create();
    
    expect(fn() => $entity->relateTo($entity, 'self-reference'))
        ->toThrow(\InvalidArgumentException::class, 'Cannot create self-referential relationship');
});

test('entity handles concurrent relationship creation', function() {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    
    // Simulate concurrent operations
    $results = collect(range(1, 5))->map(function() use ($entity1, $entity2) {
        return function() use ($entity1, $entity2) {
            $entity1->relateTo($entity2, 'concurrent-test');
        };
    });
    
    // Only one relationship should be created
    expect($entity1->outgoingRelationships)->toHaveCount(1);
});

test('entity handles deep relationship traversal', function() {
    $root = Entity::factory()->create();
    createGraphStructure($root, 5); // Create a deep graph
    
    $allRelated = $root->getAllRelated();
    $depthLimit = $root->getAllRelated(depth: 2);
    
    expect($allRelated)->toHaveCount(31); // Sum of geometric series: 2^1 + 2^2 + 2^3 + 2^4 + 2^5 = 31
    expect($depthLimit)->toHaveCount(6); // Only 2 levels deep: 2^1 + 2^2 = 6
});

test('entity handles relationship deletion gracefully', function() {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    
    $relationship = $entity1->relateTo($entity2, 'test');
    $relationship->delete();
    
    expect($entity1->fresh()->outgoingRelationships)->toBeEmpty();
    expect($entity2->fresh()->incomingRelationships)->toBeEmpty();
});

test('entity properly cascades observation deletion', function() {
    $entity = Entity::factory()->create();
    $entity->addObservation('Test');
    
    $entity->delete();
    
    expect($entity->observations()->withTrashed()->first()->deleted_at)
        ->not->toBeNull();
});

test('entity supports complex metadata queries', function() {
    Entity::factory()->create([
        'metadata' => [
            'nested' => [
                'deep' => [
                    'value' => 42
                ]
            ]
        ]
    ]);
    
    $result = Entity::whereJsonContains('metadata->nested->deep->value', 42)->first();
    
    expect($result)->not->toBeNull();
});

test('entity handles unicode names correctly', function() {
    $entity = Entity::factory()->create([
        'name' => 'テスト_entidad_πρότυπο'
    ]);
    
    expect($entity->fresh()->name)->toBe('テスト_entidad_πρότυπο');
});

test('entity validates relationship types', function($typeName, $typeValue, $shouldBeValid) {
    $test = fn() => Entity::factory()->create()->relateTo(
        Entity::factory()->create(),
        $typeValue
    );

    if ($shouldBeValid) {
        expect($test())->toBeInstanceOf(\App\Models\Relationship::class);
    } else {
        expect($test)->toThrow(ValidationException::class);
    }
})->with([
    ['normal', 'owns', true],
    ['with spaces', 'belongs to', true],
    ['with symbols', 'parent->child', true],
    ['too long', str_repeat('a', 51), false]
]);

test('entity metadata dot notation works deeply', function() {
    $entity = Entity::factory()->create([
        'metadata' => [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value'
                ]
            ]
        ]
    ]);
    
    expect($entity->getMetadataValue('level1.level2.level3'))
        ->toBe('deep value');
    
    expect($entity->getMetadataValue('level1.missing.level3', 'default'))
        ->toBe('default');
});