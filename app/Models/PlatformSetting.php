<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row table: the platform owner's own email provider
 * configuration, used as every shop's "platform_default" mailer. See
 * ShopMailerResolver, which builds a mailer from this identically to how
 * it builds one from a shop's own per-shop Setting.
 */
class PlatformSetting extends Model
{
    protected $fillable = [
        'mail_from_address',
        'mail_provider',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'resend_api_key',
        'mailgun_api_key',
        'mailgun_domain',
        'mailgun_region',
        'sendgrid_api_key',
        'postmark_api_key',
        'ses_access_key_id',
        'ses_secret_access_key',
        'ses_region',
    ];

    protected function casts(): array
    {
        return [
            'smtp_password' => 'encrypted',
            'resend_api_key' => 'encrypted',
            'mailgun_api_key' => 'encrypted',
            'sendgrid_api_key' => 'encrypted',
            'postmark_api_key' => 'encrypted',
            'ses_access_key_id' => 'encrypted',
            'ses_secret_access_key' => 'encrypted',
        ];
    }
}
