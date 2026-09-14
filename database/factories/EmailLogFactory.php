<?php

namespace Database\Factories;

use App\Models\EmailLog;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'order_id' => Order::factory(),
            'recipient_email' => fake()->safeEmail(),
            'subject' => 'Your digital download is ready',
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => 'SMTP connection refused',
            'sent_at' => null,
        ]);
    }
}
