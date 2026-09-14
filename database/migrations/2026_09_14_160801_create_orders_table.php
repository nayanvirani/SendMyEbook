<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('shopify_order_id');
            $table->string('shopify_order_number')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('financial_status')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->boolean('is_refunded')->default(false);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['shop_id', 'shopify_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
