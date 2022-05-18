<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'      => $this->faker->company(),
            'longitude' => $this->faker->longitude(-75, -74),
            'latitude'  => $this->faker->latitude(40, 41),
            'address'   => $this->faker->address(),
            'postcode'  => $this->faker->postcode(),
            'country'   => $this->faker->country(),
        ];
    }
}
