<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Shopify has deprecated non-expiring offline access tokens;
            // Token Exchange now issues a 1-hour access token plus a
            // 90-day refresh token that has to be exchanged for a new pair
            // before it expires.
            $table->text('refresh_token')->nullable()->after('access_token');
            $table->timestamp('access_token_expires_at')->nullable()->after('refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'access_token_expires_at']);
        });
    }
};
