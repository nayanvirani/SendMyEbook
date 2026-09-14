<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriptions are now created entirely by Shopify (App Pricing /
 * Managed Pricing — merchants pick a plan on Shopify's hosted page) and
 * this app only ever learns about one via the app_subscriptions/update
 * webhook. Two consequences:
 *  - The incoming plan name might not match any locally-defined Plan
 *    (e.g. a private/test plan created directly in the Partner
 *    Dashboard), so plan_id can no longer be required.
 *  - Shopify's subscription statuses (ACTIVE, CANCELLED, EXPIRED,
 *    FROZEN, PENDING, DECLINED, ACCEPTED) don't all fit the narrower set
 *    this column's CHECK constraint was created with.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');
        DB::statement('ALTER TABLE subscriptions ALTER COLUMN plan_id DROP NOT NULL');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('shopify_plan_name')->nullable()->after('shopify_charge_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('shopify_plan_name');
        });

        DB::statement('ALTER TABLE subscriptions ALTER COLUMN plan_id SET NOT NULL');
        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN ('pending','active','declined','expired','cancelled'))");
    }
};
