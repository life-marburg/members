<?php

namespace Database\Factories;

use App\Models\Instrument;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SheetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'song_id' => Song::factory(),
            'instrument_id' => Instrument::inRandomOrder()->first()->id ?? 1,
            'part_number' => $this->faker->numberBetween(1, 4),
            'variant' => null,
            'file_path' => 'sheets/' . $this->faker->uuid() . '.pdf',
        ];
    }

    public function withVariant(string $variant): self
    {
        return $this->state(['variant' => $variant]);
    }
}
