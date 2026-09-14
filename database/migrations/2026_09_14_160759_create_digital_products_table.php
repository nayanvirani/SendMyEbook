<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('shopify_product_id');
            $table->string('shopify_product_title')->nullable();
            $table->string('shopify_variant_id')->nullable();
            $table->string('shopify_variant_title')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Download rules. Null max_downloads = unlimited, null
            // expiration_value = link never expires.
            $table->unsignedInteger('max_downloads')->nullable();
            $table->unsignedInteger('expiration_value')->nullable();
            $table->enum('expiration_unit', ['hours', 'days'])->default('days');
            $table->boolean('revoke_on_refund')->default(true);

            $table->timestamps();

            $table->index(['shop_id', 'shopify_product_id']);
            $table->unique(['shop_id', 'shopify_product_id', 'shopify_variant_id'], 'digital_products_shop_product_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_products');
    }
};
