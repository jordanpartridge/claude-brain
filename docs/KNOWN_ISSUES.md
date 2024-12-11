# Known Issues & Solutions

## Current Issues

### 1. Missing Relationship Model
```
Error: Class "App\Models\Relationship" not found
```
**Solution Required:**
- Implement the Relationship model
- Define its relationships with Entity model
- Add proper validations for relationship types

### 2. Validation Issues
```
Exception "Illuminate\Validation\ValidationException" not thrown
```
**Solution Required:**
- Implement proper validation in Entity model using Laravel's validation system
- Move validation rules from static property to a dedicated request class
- Add validation for:
  - Empty names
  - Type length constraints
  - JSON metadata format

### 3. Pest Test Expectations
```
ArgumentCountError: Too few arguments to function Pest\Expectation
```
**Solution Required:**
- Fix toBeValid custom expectation implementation
- Properly pass validation parameters
- Update dataset tests format

### 4. Soft Delete Cascade Issues
```
Expecting null not to be null
```
**Solution Required:**
- Implement proper cascading deletes
- Add soft delete traits to related models
- Configure relationship cascade settings

### 5. Safe Package Deprecation Warnings
```
Implicitly marking parameter as nullable is deprecated
```
**Solution Required:**
- Update thecodingmachine/safe package
- Add explicit nullable type hints
- Consider removing or replacing the package

## Implementation Priorities

1. Model Implementation
   ```php
   // Need to implement Relationship model
   class Relationship extends Model
   {
       use HasFactory, SoftDeletes;
       
       protected $fillable = [
           'from_entity_id',
           'to_entity_id',
           'type',
           'metadata'
       ];
       
       protected $casts = [
           'metadata' => 'array'
       ];
   }
   ```

2. Validation Implementation
   ```php
   // Create dedicated request class
   class EntityRequest extends FormRequest
   {
       public function rules()
       {
           return [
               'type' => ['required', 'string', 'max:50'],
               'name' => ['required', 'string', 'max:255'],
               'metadata' => ['nullable', 'array']
           ];
       }
   }
   ```

3. Test Fixes
   ```php
   // Update Pest expectation
   expect()->extend('toBeValid', function (bool $shouldBeValid = true) {
       if ($shouldBeValid) {
           return $this->toBeInstanceOf(Entity::class);
       }
       return $this->toThrow(ValidationException::class);
   });
   ```

## Development Guidelines

### Validation Strategy
1. Use Form Requests for complex validation
2. Implement model-level validation where appropriate
3. Add custom validation rules for specific business logic

### Testing Approach
1. Split tests into feature and unit tests
2. Use proper data providers for test cases
3. Implement custom Pest expectations correctly
4. Test both happy and error paths

### Relationship Handling
1. Implement proper model relationships
2. Add cascading deletes where needed
3. Handle soft deletes appropriately
4. Add relationship validation

### Package Management
1. Review and update deprecated packages
2. Implement proper type hints
3. Consider alternative packages where needed

## Next Steps

1. Create Relationship Model:
   - Implement model with proper relationships
   - Add validation rules
   - Create factory for testing

2. Fix Validation:
   - Create FormRequest classes
   - Implement model validation
   - Update tests to use proper validation

3. Update Test Suite:
   - Fix Pest expectations
   - Update test datasets
   - Add more edge cases

4. Handle Soft Deletes:
   - Configure cascade deletes
   - Update relationship configuration
   - Add recovery tests

5. Package Updates:
   - Update safe package
   - Add proper type hints
   - Consider replacements