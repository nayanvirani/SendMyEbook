<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restructures plans around the new Free/Pro pricing: max_downloads_per_month
 * never had any real enforcement behind it (only shown as a usage number) —
 * renamed to max_deliveries_per_month since the actual limit is "30 email
 * deliveries/month", not downloads (downloads are unlimited on every plan).
 * Adds a per-plan file size cap (was a single global config value) and a
 * trial_days column for display only (the real trial clock is Shopify's
 * own Managed Pricing trial, configured in the Partner Dashboard).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('max_downloads_per_month', 'max_deliveries_per_month');
            $table->integer('max_file_size_mb')->nullable()->after('max_deliveries_per_month');
            $table->unsignedSmallInteger('trial_days')->nullable()->after('max_file_size_mb');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_file_size_mb', 'trial_days']);
            $table->renameColumn('max_deliveries_per_month', 'max_downloads_per_month');
        });
    }
};
