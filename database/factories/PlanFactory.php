<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Starter', 'Growth', 'Pro']),
            'handle' => fake()->unique()->slug(2),
            'price' => fake()->randomElement([0, 9.99, 29.99]),
            'max_digital_products' => fake()->randomElement([5, 50, null]),
            'max_downloads_per_month' => fake()->randomElement([100, 1000, null]),
            'features' => ['analytics' => true],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
