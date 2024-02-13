<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->title(),
            'price' => $this->faker->randomDigit(),
            'limit' => $this->faker->numberBetween(1, 30),
            'recording_minutes_limit' => $this->faker->numberBetween(1, 50),
            'description' => $this->faker->text(),
            'status' => 2,
        ];
    }
}
