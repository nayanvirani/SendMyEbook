<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('digital_product_id')->constrained()->cascadeOnDelete();

            // Random, non-guessable, unique per shop/order/digital product.
            $table->string('token', 64)->unique();

            $table->unsignedInteger('max_downloads')->nullable(); // null = unlimited
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // null = never
            $table->enum('status', ['active', 'expired', 'revoked', 'limit_reached'])->default('active');
            $table->string('revoked_reason')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_tokens');
    }
};
