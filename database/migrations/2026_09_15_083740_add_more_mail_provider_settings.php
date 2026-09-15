<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rounds out the provider list beyond Resend with the other standard
 * transactional email APIs, each configured with only the credentials it
 * actually needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('mailgun_api_key')->nullable()->after('resend_api_key');
            $table->string('mailgun_domain')->nullable()->after('mailgun_api_key');
            $table->string('mailgun_region')->nullable()->after('mailgun_domain'); // us | eu
            $table->text('sendgrid_api_key')->nullable()->after('mailgun_region');
            $table->text('postmark_api_key')->nullable()->after('sendgrid_api_key');
            $table->text('ses_access_key_id')->nullable()->after('postmark_api_key');
            $table->text('ses_secret_access_key')->nullable()->after('ses_access_key_id');
            $table->string('ses_region')->nullable()->after('ses_secret_access_key');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'mailgun_api_key', 'mailgun_domain', 'mailgun_region',
                'sendgrid_api_key', 'postmark_api_key',
                'ses_access_key_id', 'ses_secret_access_key', 'ses_region',
            ]);
        });
    }
};
