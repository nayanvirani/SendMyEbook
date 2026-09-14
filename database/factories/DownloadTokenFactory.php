<?php

namespace Database\Factories;

use App\Models\DigitalProduct;
use App\Models\DownloadToken;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DownloadToken>
 */
class DownloadTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'digital_product_id' => DigitalProduct::factory(),
            'token' => Str::random(48),
            'max_downloads' => 5,
            'download_count' => 0,
            'expires_at' => now()->addDays(7),
            'status' => 'active',
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function limitReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'limit_reached',
            'download_count' => $attributes['max_downloads'] ?? 5,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => [
            'status' => 'revoked',
            'revoked_reason' => 'refund',
        ]);
    }
}
