<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standardizes outgoing mail as: one master on/off switch, plus a choice
 * of provider when on. "platform_default" (the app's own shared sender,
 * whatever this app's own MAIL_MAILER is set to) is always available with
 * no configuration; "smtp" and "resend" are the two ways a merchant can
 * send from their own account instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('mail_enabled')->default(true)->after('email_from_name');
            $table->string('mail_provider')->default('platform_default')->after('mail_enabled'); // platform_default | smtp | resend
            $table->text('resend_api_key')->nullable()->after('smtp_encryption');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['mail_enabled', 'mail_provider', 'resend_api_key']);
        });
    }
};
