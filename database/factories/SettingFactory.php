<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'email_from_name' => fake()->company(),
            'support_email' => fake()->safeEmail(),
            'default_max_downloads' => 5,
            'default_expiration_days' => 7,
        ];
    }
}
