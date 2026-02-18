<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class SharedFolderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'path' => $this->faker->word(),
            'group_id' => Group::factory(),
        ];
    }

    public function blocked(): self
    {
        return $this->state(['group_id' => null]);
    }
}
