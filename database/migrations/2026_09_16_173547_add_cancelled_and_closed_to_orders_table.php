<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cancellation (orders/cancelled) and archiving (reflected via
 * orders/updated's closed_at, since Shopify has no separate "archived"
 * webhook topic) are distinct order states this app never tracked as
 * real columns before — only buried in raw_payload. Cancellation is
 * acted on (revokes download access, same as a hard delete); archiving
 * is purely a merchant workflow status and must NOT revoke anything —
 * a paid, fulfilled, archived order is the normal happy path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_cancelled')->default(false)->after('is_refunded');
            $table->boolean('is_closed')->default(false)->after('is_cancelled');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_cancelled', 'is_closed']);
        });
    }
};
