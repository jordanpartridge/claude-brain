# MindMap Knowledge Graph System

A Laravel-based knowledge graph system for managing interconnected entities, relationships, and observations with strong validation and type safety.

## Overview

MindMap is a flexible knowledge graph implementation that allows for:
- Entity management with strict validation
- Type-safe relationships between entities
- Observational data collection
- Metadata enrichment
- Graph traversal and analysis

## System Requirements

- PHP 8.2+
- Laravel 11.x
- MySQL/PostgreSQL
- Composer

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd mindmap
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations and seeders:
```bash
php artisan migrate:fresh --seed
```

## Core Components

### Entity Model
The core of the system is the `Entity` model which supports:
- Type validation
- Metadata storage
- Relationship management
- Observational data

```php
use App\Models\Entity;

// Create an entity
$entity = Entity::create([
    'type' => 'document',
    'name' => 'Example Document',
    'metadata' => ['status' => 'active']
]);

// Add observations
$entity->addObservation(
    'Content analysis complete',
    source: 'analyzer',
    confidence: 0.95
);

// Create relationships
$entity->relateTo($otherEntity, 'depends_on');
```

### Relationship Types
The system supports strictly typed relationships with validation:
- Bidirectional relationships
- Entity type constraints
- Required metadata validation
- Custom validation rules

### Testing
Tests are written using Pest PHP and follow these principles:
- Feature tests for API endpoints
- Unit tests for core functionality
- Edge case coverage
- Performance considerations

## Current Status

The project is currently in active development with the following areas being worked on:

1. Core Features:
   - [x] Entity management
   - [x] Basic relationship handling
   - [x] Metadata support
   - [ ] Advanced graph traversal
   - [ ] Event system

2. Testing:
   - [x] Basic test infrastructure
   - [x] Entity model tests
   - [ ] Complete relationship tests
   - [ ] Performance tests

## Known Issues

1. Test Failures:
   - Some relationship validation tests failing
   - Need to verify database seeding in tests
   - Edge case handling needs improvement

2. Technical Debt:
   - Deprecation warnings from thecodingmachine/safe package
   - Need to improve type safety in some areas
   - Better error handling needed

## Next Steps for Developers

### Immediate Tasks

1. Test Coverage:
   - Fix failing relationship tests
   - Add more edge case tests
   - Implement performance benchmarks
   - Add API endpoint tests

2. Features:
   - Implement advanced graph traversal
   - Add event system for entity changes
   - Improve metadata validation
   - Add batch operation support

3. Code Quality:
   - Address deprecation warnings
   - Improve type hinting
   - Add more comprehensive documentation
   - Implement strict validation

### Future Enhancements

1. Performance:
   - Implement caching for frequently accessed entities
   - Optimize graph traversal algorithms
   - Add database indexing strategies
   - Consider using a graph database for certain operations

2. Features:
   - Add versioning for entities and relationships
   - Implement a query builder for graph traversal
   - Add support for complex relationship rules
   - Create visualization tools

3. Architecture:
   - Consider breaking into microservices
   - Add event sourcing
   - Implement CQRS pattern
   - Add API versioning

## Contributing

1. Branch Strategy:
   - Feature branches from 'feature/more-process-docs'
   - Follow conventional commits
   - Include tests for new features
   - Update documentation

2. Testing Requirements:
   - All tests must pass
   - Add tests for new features
   - Include edge cases
   - Performance impact considered

3. Code Style:
   - Follow PSR-12
   - Use Laravel conventions
   - Document public methods
   - Type hint where possible

## Documentation

For more detailed documentation, see:
- [IMPLEMENTATION_PROGRESS.md](docs/IMPLEMENTATION_PROGRESS.md)
- [RELATIONSHIPS.md](docs/RELATIONSHIPS.md)
- API Documentation (TODO)

## Support

For support:
- Create issues for bugs
- Use discussions for questions
- Tag appropriate reviewers
- Reference MindMap graph structure
