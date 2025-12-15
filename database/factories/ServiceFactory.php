<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'AC Repair',
                'House Cleaning',
                'Plumbing Service',
                'Electrical Work',
                'Painting Service',
                'Gardening',
                'Computer Repair',
            ]),
            'price' => fake()->numberBetween(50000, 500000),
        ];
    }
}
