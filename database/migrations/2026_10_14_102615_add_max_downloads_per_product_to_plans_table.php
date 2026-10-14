<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A plan-level ceiling on the download-count limit a merchant can set
 * (both their Settings default and any single product's override) —
 * same "super admin sets the cap, store owner sets their own value
 * within it" pattern as max_file_size_mb.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_downloads_per_product')->nullable()->after('max_file_size_mb');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_downloads_per_product');
        });
    }
};
