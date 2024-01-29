<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => $this->faker->title() . " " . $this->faker->name(),
            'description' => $this->faker->paragraph('20'),
            'file' => $this->faker->url(),
            'thumbnail' => $this->faker->url(),
            'duration' => $this->faker->numberBetween(1, 10),
            'size' => $this->faker->numberBetween(1, 10),
            'type' => '',
            'status' => 1,
            'privacy' => null,
            'share' => $this->faker->url(),
            'embed' => $this->faker->url(),
        ];
    }
}
