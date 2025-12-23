<?php

namespace Database\Factories;

use App\Models\ExternalLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExternalLinkFactory extends Factory
{
    protected $model = ExternalLink::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'url' => fake()->url(),
            'show_external_icon' => true,
            'position' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'target' => '_blank',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
