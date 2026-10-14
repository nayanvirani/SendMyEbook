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
                'features' => [],
                'marketing_bullets' => [
                    '30 ebook deliveries per month',
                    'Unlimited ebook titles/products listed',
                    'Automatic email delivery after purchase',
                    'Download link on Thank You page',
                    'Upload up to 250MB per file',
                    'Basic file hosting (ePub, PDF, MOBI)',
                ],
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
                'features' => ['watermarking', 'custom_branding', 'download_analytics', 'priority_support'],
                'marketing_bullets' => [
                    'Unlimited ebook deliveries per month',
                    'Upload up to 10GB per file',
                    'PDF watermarking (protects against sharing/piracy)',
                    'Custom branding (logo, colors on delivery page/emails)',
                    'Download analytics (track opens, downloads, repeat buyers)',
                    'Priority email support',
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
