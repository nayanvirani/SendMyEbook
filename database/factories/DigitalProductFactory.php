<?php

namespace Database\Factories;

use App\Models\DigitalProduct;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalProduct>
 */
class DigitalProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'shopify_product_id' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'shopify_product_title' => fake()->words(3, true),
            'shopify_variant_id' => null,
            'shopify_variant_title' => null,
            'status' => 'active',
            'max_downloads' => 5,
            'expiration_value' => 7,
            'expiration_unit' => 'days',
            'revoke_on_refund' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
