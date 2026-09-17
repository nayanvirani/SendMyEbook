<?php

namespace App\Services\Mail;

use App\Models\PlatformSetting;
use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport;

/**
 * Standardized outgoing-mail resolution for a shop: one master on/off
 * switch (mail_enabled), and — when on — a choice of provider:
 *
 *  - "platform_default": SendMyEbook's own shared sender. Needs no
 *    per-shop configuration; what every shop gets until it sets up its
 *    own. Actually sends through whatever the platform owner configured
 *    in the super-admin's own Settings page (see PlatformSetting) when
 *    they've configured a real provider there — Railway itself doesn't
 *    provide an email-sending service, so without that configuration
 *    this falls back to Laravel's own MAIL_MAILER env config, which
 *    starts out as "log" (i.e. nothing actually sends).
 *  - "smtp": the merchant's own SMTP credentials (any provider that
 *    offers SMTP).
 *  - "mailgun" / "sendgrid" / "postmark" / "ses" / "resend": the
 *    merchant's own API credentials for that provider.
 *
 * Every non-default provider is built directly from a Symfony Mailer DSN
 * on every call — never cached by name or bound as a singleton — so a
 * merchant (or the platform owner) changing credentials takes effect on
 * the very next email rather than continuing to serve stale ones for the
 * lifetime of the (long-running) queue worker process.
 */
class ShopMailerResolver
{
    /**
     * @return array{enabled: bool, mailer: ?Mailer, fromAddress: string, fromName: string}
     */
    public function resolve(Shop $shop): array
    {
        $setting = $shop->setting;
        $fromAddress = $setting?->mail_from_address ?: $this->defaultFromAddress();
        $fromName = $setting?->email_from_name ?: $shop->shop_name;

        if ($setting && ! $setting->mail_enabled) {
            return ['enabled' => false, 'mailer' => null, 'fromAddress' => $fromAddress, 'fromName' => $fromName];
        }

        return [
            'enabled' => true,
            'mailer' => $this->buildMailer($setting),
            'fromAddress' => $fromAddress,
            'fromName' => $fromName,
        ];
    }

    /**
     * Builds the mailer regardless of the enabled switch — used by the
     * Settings "send test email" action, which should test the
     * configured provider even before it's switched on.
     */
    public function buildMailer(?Setting $setting): Mailer
    {
        if ($setting?->mail_provider && $setting->mail_provider !== 'platform_default') {
            return $this->buildMailerFromFields($setting->mail_provider, $setting->only(self::PROVIDER_FIELDS));
        }

        return $this->buildPlatformDefaultMailer();
    }

    /**
     * The platform owner's own configured provider (super-admin Settings),
     * used both as every shop's "platform_default" and by that settings
     * page's own "send test email" action.
     */
    public function buildPlatformDefaultMailer(): Mailer
    {
        $platform = PlatformSetting::query()->first();

        if ($platform?->mail_provider) {
            return $this->buildMailerFromFields($platform->mail_provider, $platform->only(self::PROVIDER_FIELDS));
        }

        return Mail::mailer();
    }

    private const PROVIDER_FIELDS = [
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption',
        'resend_api_key',
        'mailgun_api_key', 'mailgun_domain', 'mailgun_region',
        'sendgrid_api_key',
        'postmark_api_key',
        'ses_access_key_id', 'ses_secret_access_key', 'ses_region',
    ];

    /**
     * @param  array<string, mixed>  $fields
     */
    private function buildMailerFromFields(string $provider, array $fields): Mailer
    {
        $dsn = match ($provider) {
            'smtp' => $this->smtpDsn($fields),
            'mailgun' => $this->mailgunDsn($fields),
            'sendgrid' => $this->sendgridDsn($fields),
            'postmark' => $this->postmarkDsn($fields),
            'ses' => $this->sesDsn($fields),
            'resend' => $this->resendDsn($fields),
            default => null,
        };

        if ($dsn === null) {
            return Mail::mailer();
        }

        $transport = Transport::fromDsn($dsn);

        return new Mailer('dynamic', app('view'), $transport, app('events'));
    }

    public function defaultFromAddress(): string
    {
        $platform = PlatformSetting::query()->first();

        return $platform?->mail_from_address ?: (string) config('mail.from.address');
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function smtpDsn(array $f): string
    {
        $scheme = match ($f['smtp_encryption'] ?? null) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        return sprintf(
            '%s://%s:%s@%s:%s',
            $scheme,
            rawurlencode((string) ($f['smtp_username'] ?? '')),
            rawurlencode((string) ($f['smtp_password'] ?? '')),
            $f['smtp_host'] ?? '',
            $f['smtp_port'] ?: 587,
        );
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function mailgunDsn(array $f): string
    {
        $dsn = sprintf(
            'mailgun+api://%s:%s@default',
            rawurlencode((string) ($f['mailgun_api_key'] ?? '')),
            rawurlencode((string) ($f['mailgun_domain'] ?? '')),
        );

        return ($f['mailgun_region'] ?? null) ? $dsn.'?region='.rawurlencode($f['mailgun_region']) : $dsn;
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function sendgridDsn(array $f): string
    {
        return sprintf('sendgrid+api://%s@default', rawurlencode((string) ($f['sendgrid_api_key'] ?? '')));
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function postmarkDsn(array $f): string
    {
        return sprintf('postmark+api://%s@default', rawurlencode((string) ($f['postmark_api_key'] ?? '')));
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function sesDsn(array $f): string
    {
        $dsn = sprintf(
            'ses+api://%s:%s@default',
            rawurlencode((string) ($f['ses_access_key_id'] ?? '')),
            rawurlencode((string) ($f['ses_secret_access_key'] ?? '')),
        );

        return $dsn.'?region='.rawurlencode($f['ses_region'] ?: 'us-east-1');
    }

    /**
     * @param  array<string, mixed>  $f
     */
    private function resendDsn(array $f): string
    {
        return sprintf('resend+api://%s@default', rawurlencode((string) ($f['resend_api_key'] ?? '')));
    }
}
