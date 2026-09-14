<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_domain' => fake()->unique()->domainWord().'.myshopify.com',
            'access_token' => 'shpat_'.fake()->sha256(),
            'scope' => 'read_products,read_orders,read_returns',
            'shop_name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'plan_display_name' => 'basic',
            'is_active' => true,
            'installed_at' => now(),
        ];
    }

    public function uninstalled(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'uninstalled_at' => now(),
        ]);
    }
}
