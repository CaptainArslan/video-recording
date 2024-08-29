<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            // 'user_name' => $this->faker->userName(),
            'email' => $this->faker->safeEmail(),
            // 'phone' => $this->faker->phoneNumber(),
            'role' => rand(0, 2),
            'location_id' => Str::random(15),
            'ghl_api_key' => Str::random(15),
            'password' => Hash::make('12345678'),
            'added_by' => 1,
            'image' => null,
            'status' => $this->faker->boolean(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
