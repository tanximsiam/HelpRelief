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
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'based_in' => $this->faker->address,
            'registration_no' => $this->faker->unique()->numerify('REG-#####'),
            'established_year' => $this->faker->year,
            'director_name' => $this->faker->name,
            'director_phone' => $this->faker->phoneNumber,
            'num_employees' => $this->faker->numberBetween(1, 1000),
            'logo_url' => $this->faker->imageUrl(640, 480, 'business', true),
            'approved' => true, // Assuming all created NGOs are approved
        ];
    }
}
