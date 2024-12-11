<?php

namespace Database\Factories;

use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entity>
 */
class EntityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['user', 'project', 'concept', 'task']),
            'name' => $this->faker->words(3, true),
            'metadata' => [
                'description' => $this->faker->sentence(),
                'tags' => $this->faker->words(3)
            ]
        ];
    }

    /**
     * Configure the factory for a user entity.
     */
    public function user(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'user',
                'metadata' => [
                    'email' => $this->faker->email,
                    'last_active' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s')
                ]
            ];
        });
    }

    /**
     * Configure the factory for a project entity.
     */
    public function project(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'project',
                'metadata' => [
                    'status' => $this->faker->randomElement(['active', 'completed', 'on_hold']),
                    'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
                    'start_date' => $this->faker->date()
                ]
            ];
        });
    }
}
