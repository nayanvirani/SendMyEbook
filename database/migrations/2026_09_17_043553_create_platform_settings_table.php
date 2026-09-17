<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single-row table holding the platform owner's own email provider
 * configuration — used as every shop's "platform_default" mailer.
 * Railway doesn't provide an email-sending service itself, so without
 * this configured, platform_default falls back to Laravel's own
 * MAIL_MAILER env config (which starts out as "log" — nothing actually
 * sends). Mirrors the same provider fields already on the per-shop
 * settings table so ShopMailerResolver can build a mailer from either
 * one identically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_provider')->nullable(); // null = use Laravel's own MAIL_MAILER env config
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->text('resend_api_key')->nullable();
            $table->text('mailgun_api_key')->nullable();
            $table->string('mailgun_domain')->nullable();
            $table->string('mailgun_region')->nullable();
            $table->text('sendgrid_api_key')->nullable();
            $table->text('postmark_api_key')->nullable();
            $table->text('ses_access_key_id')->nullable();
            $table->text('ses_secret_access_key')->nullable();
            $table->string('ses_region')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
