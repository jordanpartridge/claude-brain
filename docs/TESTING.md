# Testing Standards and Guidelines

## Overview
This document outlines the testing standards and practices for the Claude Brain project. We use Pest PHP as our primary testing framework for its expressive syntax and powerful features that align well with our knowledge graph testing needs.

## Testing Structure

### Directory Organization
```
tests/
├── Feature/            # Feature/integration tests
│   ├── Api/           # API endpoint tests
│   ├── Graph/         # Knowledge graph operation tests
│   └── Web/          # Web interface tests
├── Unit/              # Unit tests
│   ├── Models/       # Model tests
│   ├── Services/     # Service layer tests
│   └── Support/      # Helper/utility tests
└── Pest.php          # Pest configuration
```

## Testing Approaches

### Unit Tests
- Test individual components in isolation
- Use mock objects for external dependencies
- Focus on single responsibility
- Follow Arrange-Act-Assert pattern

Example:
```php
test('entity can add observation', function() {
    // Arrange
    $entity = Entity::factory()->create();
    
    // Act
    $observation = $entity->addObservation(
        content: 'Test observation',
        confidence: 0.9
    );
    
    // Assert
    expect($observation)
        ->toBeInstanceOf(Observation::class)
        ->content->toBe('Test observation')
        ->confidence->toBe(0.9);
});
```

### Feature Tests
- Test complete features end-to-end
- Focus on user stories and business requirements
- Test integration between components

Example:
```php
test('can create entity with relationships', function() {
    // Test complete graph operations
    $response = postJson('/api/entities', [
        'type' => 'project',
        'name' => 'Knowledge Graph',
        'relationships' => [
            ['type' => 'owns', 'target_id' => $userId]
        ]
    ]);
    
    $response->assertCreated();
    expect(Entity::first())
        ->outgoingRelationships->toHaveCount(1);
});
```

## Best Practices

### 1. Naming Conventions
- Use descriptive, behavior-focused test names
- Follow the pattern: `it_does_something_when_condition`
```php
test('it retrieves related entities by type', function() {});
test('it fails gracefully with invalid relationship type', function() {});
```

### 2. Data Factories
- Use factories for test data
- Create specific states for common scenarios
```php
Entity::factory()->user()->create();
Entity::factory()->project()->withRelationships(3)->create();
```

### 3. Datasets
Use Pest's dataset feature for testing multiple scenarios:
```php
test('it validates entity types')
    ->with([
        'user' => ['user', true],
        'project' => ['project', true],
        'invalid' => ['invalid_type', false],
    ])
    ->expect(fn ($type, $valid) => 
        Entity::factory()->create(['type' => $type])
    )->toBeValid($valid);
```

### 4. Higher-Order Testing
Utilize Pest's higher-order testing for cleaner assertions:
```php
expect($entity->observations)
    ->toHaveCount(5)
    ->each(fn ($observation) => 
        $observation->toBeInstanceOf(Observation::class)
    );
```

## Test Coverage Requirements

### Critical Components
- Models: 100% coverage
- Services: 95% coverage
- API endpoints: 100% coverage
- Graph operations: 100% coverage

### General Components
- Helper functions: 90% coverage
- Console commands: 85% coverage

## Common Test Scenarios

### 1. Entity Creation and Validation
```php
test('entity requires valid type and name', function() {
    expect(fn() => Entity::factory()->create(['type' => '']))
        ->toThrow(ValidationException::class);
});
```

### 2. Relationship Testing
```php
test('entities can establish bidirectional relationships', function() {
    [$user, $project] = Entity::factory()->count(2)->create();
    
    $user->relateTo($project, 'owns');
    expect($project->incomingRelationships)->toHaveCount(1);
    expect($user->outgoingRelationships)->toHaveCount(1);
});
```

### 3. Graph Traversal
```php
test('can traverse graph relationships', function() {
    $root = Entity::factory()->create();
    createGraphStructure($root, 3); // Helper to create test graph
    
    expect($root->getAllRelated(depth: 2))
        ->toHaveCount(4)
        ->each->toBeInstanceOf(Entity::class);
});
```

### 4. Metadata Handling
```php
test('metadata is properly stored and retrieved', function() {
    $entity = Entity::factory()->create([
        'metadata' => ['key' => 'value']
    ]);
    
    expect($entity->getMetadataValue('key'))->toBe('value');
    expect($entity->getMetadataValue('missing', 'default'))->toBe('default');
});
```

## Running Tests
```bash
# Run all tests
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit

# Run with coverage report
./vendor/bin/pest --coverage

# Filter tests by name
./vendor/bin/pest --filter=entity
```

## CI/CD Integration
- All tests must pass before merge
- Coverage reports generated and archived
- Performance benchmarks tracked
- Failed tests block deployment

## Tips for Writing Good Tests
1. Keep tests focused and concise
2. Use meaningful test data
3. Test edge cases and error conditions
4. Avoid test interdependence
5. Use appropriate scope (unit vs. feature)
6. Comment complex test arrangements
7. Use test doubles appropriately

## Debugging Tests
- Use `ray()` for debugging
- Enable Pest's verbose mode
- Review test logs in `storage/logs`
- Use `dd()` in tests when needed

## Performance Considerations
- Clean up after tests
- Use database transactions
- Avoid unnecessary database operations
- Mock heavy operations
- Use setup/teardown hooks efficiently