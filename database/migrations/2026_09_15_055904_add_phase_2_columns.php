<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->boolean('requires_license_key')->default(false)->after('revoke_on_refund');
            $table->boolean('watermark_pdfs')->default(false)->after('requires_license_key');
        });

        Schema::table('download_tokens', function (Blueprint $table) {
            $table->string('license_key')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->dropColumn(['requires_license_key', 'watermark_pdfs']);
        });

        Schema::table('download_tokens', function (Blueprint $table) {
            $table->dropColumn('license_key');
        });
    }
};
