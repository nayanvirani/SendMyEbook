<?php

namespace App\Services\Mail;

use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport;

/**
 * Standardized outgoing-mail resolution for a shop: one master on/off
 * switch (mail_enabled), and — when on — a choice of provider:
 *
 *  - "platform_default": this app's own shared sender (whatever
 *    MAIL_MAILER is set to). Needs no configuration; what every shop
 *    gets until it sets up its own.
 *  - "smtp": the merchant's own SMTP credentials (any provider that
 *    offers SMTP).
 *  - "mailgun" / "sendgrid" / "postmark" / "ses" / "resend": the
 *    merchant's own API credentials for that provider.
 *
 * Every non-default provider is built directly from a Symfony Mailer DSN
 * on every call — never cached by name or bound as a singleton — so a
 * merchant changing their credentials takes effect on the very next
 * email rather than continuing to serve stale ones for the lifetime of
 * the (long-running) queue worker process.
 */
class ShopMailerResolver
{
    /**
     * @return array{enabled: bool, mailer: ?Mailer, fromAddress: string, fromName: string}
     */
    public function resolve(Shop $shop): array
    {
        $setting = $shop->setting;
        $fromAddress = $setting?->mail_from_address ?: (string) config('mail.from.address');
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
        $dsn = match ($setting?->mail_provider) {
            'smtp' => $this->smtpDsn($setting),
            'mailgun' => $this->mailgunDsn($setting),
            'sendgrid' => $this->sendgridDsn($setting),
            'postmark' => $this->postmarkDsn($setting),
            'ses' => $this->sesDsn($setting),
            'resend' => $this->resendDsn($setting),
            default => null,
        };

        if ($dsn === null) {
            return Mail::mailer();
        }

        $transport = Transport::fromDsn($dsn);

        return new Mailer('dynamic', app('view'), $transport, app('events'));
    }

    private function smtpDsn(Setting $setting): string
    {
        $scheme = match ($setting->smtp_encryption) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        return sprintf(
            '%s://%s:%s@%s:%s',
            $scheme,
            rawurlencode((string) $setting->smtp_username),
            rawurlencode((string) $setting->smtp_password),
            $setting->smtp_host,
            $setting->smtp_port ?: 587,
        );
    }

    private function mailgunDsn(Setting $setting): string
    {
        $dsn = sprintf(
            'mailgun+api://%s:%s@default',
            rawurlencode((string) $setting->mailgun_api_key),
            rawurlencode((string) $setting->mailgun_domain),
        );

        return $setting->mailgun_region ? $dsn.'?region='.rawurlencode($setting->mailgun_region) : $dsn;
    }

    private function sendgridDsn(Setting $setting): string
    {
        return sprintf('sendgrid+api://%s@default', rawurlencode((string) $setting->sendgrid_api_key));
    }

    private function postmarkDsn(Setting $setting): string
    {
        return sprintf('postmark+api://%s@default', rawurlencode((string) $setting->postmark_api_key));
    }

    private function sesDsn(Setting $setting): string
    {
        $dsn = sprintf(
            'ses+api://%s:%s@default',
            rawurlencode((string) $setting->ses_access_key_id),
            rawurlencode((string) $setting->ses_secret_access_key),
        );

        return $dsn.'?region='.rawurlencode($setting->ses_region ?: 'us-east-1');
    }

    private function resendDsn(Setting $setting): string
    {
        return sprintf('resend+api://%s@default', rawurlencode((string) $setting->resend_api_key));
    }
}
