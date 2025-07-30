<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ngo>
 */
class NgoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'rep_contact' => $this->faker->name(),
            'rep_designation' => $this->faker->jobTitle(),
            'rep_email' => $this->faker->unique()->safeEmail(),
            'rep_phone' => $this->faker->phoneNumber(),
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'status' => 'approved',
        ];
    }
}
