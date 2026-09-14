<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'plan_id' => Plan::factory(),
            'shopify_charge_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ];
    }
}
