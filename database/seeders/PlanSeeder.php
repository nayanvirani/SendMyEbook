<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'handle' => 'starter',
                'price' => 0,
                'max_digital_products' => 3,
                'max_downloads_per_month' => 100,
                'features' => ['email_delivery', 'basic_analytics'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'handle' => 'growth',
                'price' => 9.99,
                'max_digital_products' => 25,
                'max_downloads_per_month' => 2000,
                'features' => ['email_delivery', 'basic_analytics', 'priority_support'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'handle' => 'pro',
                'price' => 29.99,
                'max_digital_products' => null,
                'max_downloads_per_month' => null,
                'features' => ['email_delivery', 'basic_analytics', 'priority_support', 'unlimited_products'],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(['handle' => $plan['handle']], $plan);
        }
    }
}
