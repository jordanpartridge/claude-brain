<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RelationshipType;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic relationship types needed for tests
        $types = [
            [
                'name' => 'contains',
                'inverse_name' => 'contained_in',
                'is_bidirectional' => true,
            ],
            [
                'name' => 'owns',
                'inverse_name' => 'owned_by',
                'is_bidirectional' => true,
            ],
            [
                'name' => 'depends_on',
                'inverse_name' => 'dependency_of',
                'is_bidirectional' => true,
            ],
            [
                'name' => 'belongs_to',
                'inverse_name' => 'has',
                'is_bidirectional' => true,
                'allowed_entity_types' => [
                    'from' => ['document', 'task'],
                    'to' => ['project', 'folder']
                ],
                'required_metadata_fields' => ['created_by'],
                'validation_rules' => [
                    'created_by' => 'required|string',
                    'priority' => 'sometimes|integer|min:1|max:5'
                ]
            ],
            [
                'name' => 'test',
                'inverse_name' => 'test_of',
                'is_bidirectional' => true,
            ],
            [
                'name' => 'concurrent-test',
                'inverse_name' => 'concurrent-test-of',
                'is_bidirectional' => true,
            ],
            [
                'name' => 'parent->child',
                'inverse_name' => 'child->parent',
                'is_bidirectional' => true,
            ],
        ];

        foreach ($types as $type) {
            RelationshipType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
