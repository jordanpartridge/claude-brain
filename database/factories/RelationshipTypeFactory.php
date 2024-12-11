<?php

namespace Database\Factories;

use App\Models\RelationshipType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationshipTypeFactory extends Factory
{
    protected $model = RelationshipType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'inverse_name' => $this->faker->word,
            'is_bidirectional' => $this->faker->boolean,
            'allowed_entity_types' => [
                'from' => ['document', 'task', 'project'],
                'to' => ['project', 'folder', 'task']
            ],
            'required_metadata_fields' => ['created_by'],
            'validation_rules' => [
                'created_by' => 'required|string',
                'priority' => 'sometimes|integer|min:1|max:5'
            ]
        ];
    }

    public function bidirectional(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_bidirectional' => true,
                'inverse_name' => $this->faker->word
            ];
        });
    }

    public function withoutValidation(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'required_metadata_fields' => null,
                'validation_rules' => null
            ];
        });
    }
}