<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Observation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Observation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'content' => $this->faker->paragraph,
            'source' => $this->faker->randomElement(['user', 'system', 'analysis', 'inference']),
            'confidence' => $this->faker->randomFloat(4, 0, 1),
            'metadata' => [
                'timestamp' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'context' => $this->faker->sentence,
                'tags' => $this->faker->words(3)
            ]
        ];
    }

    /**
     * Configure the factory for a high confidence observation.
     */
    public function highConfidence(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'confidence' => $this->faker->randomFloat(4, 0.8, 1.0)
            ];
        });
    }

    /**
     * Configure the factory for a low confidence observation.
     */
    public function lowConfidence(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'confidence' => $this->faker->randomFloat(4, 0, 0.3)
            ];
        });
    }

    /**
     * Configure the factory for a user-sourced observation.
     */
    public function fromUser(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'source' => 'user',
                'metadata' => [
                    'user_id' => $this->faker->numberBetween(1, 100),
                    'timestamp' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s')
                ]
            ];
        });
    }
}
