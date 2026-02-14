<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PersonalDataFactory extends Factory
{
    public function definition()
    {
        return [
            'street' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'phone' => $this->faker->e164PhoneNumber,
            'mobile_phone' => $this->faker->e164PhoneNumber,
        ];
    }
}
