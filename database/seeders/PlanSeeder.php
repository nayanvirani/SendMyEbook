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
                'handle' => 'free',
                'name' => 'Free',
                'price' => 0,
                'max_digital_products' => null,
                'max_deliveries_per_month' => 30,
                'max_file_size_mb' => 250,
                'trial_days' => null,
                'features' => ['email_delivery', 'thank_you_widget'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'handle' => 'pro',
                'name' => 'Pro',
                'price' => 19,
                'max_digital_products' => null,
                'max_deliveries_per_month' => null,
                'max_file_size_mb' => 10240,
                'trial_days' => 14,
                'features' => [
                    'email_delivery', 'thank_you_widget', 'watermarking',
                    'custom_branding', 'download_analytics', 'priority_support',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(['handle' => $plan['handle']], $plan);
        }

        // Retired tiers — deactivated rather than deleted, so any
        // existing subscription history still resolves a real Plan row.
        Plan::query()->whereIn('handle', ['starter', 'growth'])->update(['is_active' => false]);
    }
}
