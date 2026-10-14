<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The exact bullet copy shown on the merchant-facing Billing page was
 * hardcoded in the frontend, keyed by plan handle — meaning an admin
 * editing a plan's limits in /admin/plans never changed what merchants
 * actually see. Storing the display copy as data lets Billing.jsx just
 * render whatever the admin wrote, so every part of a plan (price,
 * limits, feature flags, marketing text) is editable from one place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('marketing_bullets')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('marketing_bullets');
        });
    }
};
