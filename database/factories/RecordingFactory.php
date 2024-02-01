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
            'description' => $this->faker->text(50),
            'file' => $this->faker->url(),
            'file_url' => $this->faker->url(),
            'poster' => $this->faker->url(),
            'poster_url' => $this->faker->url(),
            'duration' => $this->faker->numberBetween(1, 10),
            'size' => $this->faker->numberBetween(1, 10),
            'type' => '',
            'status' => $this->faker->randomElement(['draft', 'publish']),
            'privacy' => null,
            'share' => $this->faker->url(),
            'embed' => $this->faker->url(),
        ];
    }
}
