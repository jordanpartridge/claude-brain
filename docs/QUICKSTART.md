# Claude Brain Quick Start Guide

## Overview
Claude Brain provides persistent memory and context awareness for AI assistants through a graph-based API.

## Getting Started

### 1. Installation
```bash
# Clone the repository
git clone https://github.com/username/claude-brain
cd claude-brain

# Install dependencies
composer install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
```

### 2. Create API Token
```bash
# Generate a token for your AI instance
php artisan claude:token create instance_name
```

### 3. Basic Usage

#### Store Information
```bash
# Store a new entity
curl -X POST http://localhost/api/v1/entities \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "user",
    "name": "john_doe",
    "metadata": {
      "email": "john@example.com"
    }
  }'
```

#### Add Observation
```bash
# Add an observation to an entity
curl -X POST http://localhost/api/v1/observations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "entity_id": 1,
    "content": "Prefers Python for data analysis",
    "confidence": 0.9
  }'
```

#### Query Context
```bash
# Get entity with related information
curl http://localhost/api/v1/graph/traverse \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_id": 1,
    "depth": 2,
    "types": ["skill", "project"]
  }'
```

## Integration Examples

### Python Client
```python
import requests

class ClaudeBrainClient:
    def __init__(self, base_url, token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    
    async def store_context(self, content, entity_type="conversation"):
        response = await requests.post(
            f'{self.base_url}/api/v1/entities',
            headers=self.headers,
            json={
                'type': entity_type,
                'content': content
            }
        )
        return response.json()
    
    async def get_context(self, entity_id):
        response = await requests.get(
            f'{self.base_url}/api/v1/entities/{entity_id}',
            headers=self.headers
        )
        return response.json()
```

### Example Usage in Claude
```python
# Initialize client
brain = ClaudeBrainClient('http://localhost', 'YOUR_TOKEN')

# During conversation
async def handle_user_input(user_id, message):
    # Store new information
    await brain.store_observation(
        entity_id=user_id,
        content=message,
        confidence=0.8
    )
    
    # Get context for response
    context = await brain.get_context(user_id)
    return generate_response(message, context)
```

## Common Operations

### 1. Managing Entities
- Create entities for users, concepts, or projects
- Update entity metadata
- Delete outdated entities
- Query entities by type or attributes

### 2. Working with Observations
- Add new observations with confidence levels
- Update existing observations
- Query observations by source or confidence
- Track observation history

### 3. Handling Relationships
- Create connections between entities
- Query related entities
- Traverse relationship paths
- Update relationship metadata

## Best Practices

### 1. Data Storage
- Always include confidence levels
- Use consistent entity types
- Create meaningful relationships
- Clean up outdated information

### 2. API Usage
- Implement proper error handling
- Cache frequently accessed data
- Use batch operations when possible
- Handle rate limits gracefully

### 3. Security
- Rotate tokens regularly
- Validate all input data
- Monitor API usage
- Implement proper error handling

## Troubleshooting

### Common Issues
1. Authentication errors
   - Check token validity
   - Verify token format
   - Check rate limits

2. Data consistency
   - Verify entity exists
   - Check relationship validity
   - Validate data format

3. Performance
   - Use proper indexes
   - Implement caching
   - Optimize queries
   - Batch operations

## Support

### Getting Help
- Check documentation
- Review error logs
- Contact support team
- Submit issue tickets

### Contributing
- Follow coding standards
- Write tests
- Submit pull requests
- Update documentation