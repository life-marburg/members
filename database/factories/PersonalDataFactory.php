<?php

namespace Database\Factories;

use App\Instruments;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalDataFactory extends Factory
{
    public function definition()
    {
        return [
            'instrument' => $this->faker->randomElement(array_keys(Instruments::INSTRUMENT_GROUPS)),
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
        ];
    }
}
