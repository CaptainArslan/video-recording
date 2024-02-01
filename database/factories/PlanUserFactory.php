<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\PlanUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'plan_id' => Plan::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'start_date' => Carbon::now(),
            'status' => 0, // Default to inactive
            'end_date' => Carbon::now()->addDays(30),
        ];
    }

    /**
     * Set the state to active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 1, // Set status to active
            ];
        });
    }

    /**
     * Configure the model.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function ($planUser) {
            // Ensure only one user has an active plan
            if ($planUser->status == 1) {
                PlanUser::where('user_id', '!=', $planUser->user_id)
                    ->where('status', 1)
                    ->update(['status' => 0]);
            }
        });
    }
}
