<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'shopify_order_id' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'shopify_order_number' => (string) fake()->unique()->numberBetween(1000, 9999),
            'customer_email' => fake()->safeEmail(),
            'customer_name' => fake()->name(),
            'financial_status' => 'paid',
            'total_price' => fake()->randomFloat(2, 5, 200),
            'currency' => 'USD',
            'is_refunded' => false,
            'raw_payload' => [],
        ];
    }

    public function refunded(): static
    {
        return $this->state(fn () => [
            'is_refunded' => true,
            'financial_status' => 'refunded',
        ]);
    }
}
