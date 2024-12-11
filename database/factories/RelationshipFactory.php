<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationshipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Relationship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_entity_id' => Entity::factory(),
            'to_entity_id' => Entity::factory(),
            'type' => $this->faker->word(),
            'metadata' => null,
        ];
    }

    /**
     * Set the type of the relationship.
     */
    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Set the metadata for the relationship.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }

    /**
     * Set the source entity.
     */
    public function from(Entity $entity): static
    {
        return $this->state(fn (array $attributes) => [
            'from_entity_id' => $entity->id,
        ]);
    }

    /**
     * Set the target entity.
     */
    public function to(Entity $entity): static
    {
        return $this->state(fn (array $attributes) => [
            'to_entity_id' => $entity->id,
        ]);
    }
}
