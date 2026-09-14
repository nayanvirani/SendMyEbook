<?php

namespace Database\Factories;

use App\Models\DigitalProduct;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'digital_product_id' => DigitalProduct::factory(),
            'original_filename' => fake()->word().'.pdf',
            'storage_disk' => config('filedelivery.disk'),
            'storage_key' => 'files/'.Str::uuid()->toString().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(10_000, 10_000_000),
            'status' => 'ready',
        ];
    }
}
