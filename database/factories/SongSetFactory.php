<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SongSetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
        ];
    }
}
