<?php

namespace Database\Factories;

use App\Models\Download;
use App\Models\DownloadToken;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Download>
 */
class DownloadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'download_token_id' => DownloadToken::factory(),
            'file_id' => File::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'downloaded_at' => now(),
        ];
    }
}
