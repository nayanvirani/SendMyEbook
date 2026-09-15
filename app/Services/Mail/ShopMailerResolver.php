<?php

namespace App\Services\Mail;

use App\Models\Setting;
use App\Models\Shop;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Resend\Contracts\Client as ResendClientContract;

/**
 * Standardized outgoing-mail resolution for a shop: one master on/off
 * switch (mail_enabled), and — when on — a choice of provider:
 *
 *  - "platform_default": this app's own shared sender (whatever
 *    MAIL_MAILER is set to). Needs no configuration; what every shop
 *    gets until it sets up its own.
 *  - "smtp": the merchant's own SMTP credentials (any provider that
 *    offers SMTP — Gmail, SendGrid, Mailgun, Postmark, their own
 *    server, ...).
 *  - "resend": the merchant's own Resend API key — no SMTP setup needed.
 *
 * Every mailer is built fresh under a one-off name on every call rather
 * than reusing a shop-keyed name, so a merchant changing their
 * credentials takes effect on the very next email — Laravel's mail
 * manager and the Resend package's underlying client are both cached by
 * name/singleton, which would otherwise keep serving stale credentials
 * for the lifetime of the (long-running) queue worker process.
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
        return match ($setting?->mail_provider) {
            'smtp' => $this->buildSmtpMailer($setting),
            'resend' => $this->buildResendMailer($setting),
            default => Mail::mailer(),
        };
    }

    private function buildSmtpMailer(Setting $setting): Mailer
    {
        $mailerName = 'dynamic_smtp_'.Str::random(8);

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $setting->smtp_host,
            'port' => $setting->smtp_port,
            'username' => $setting->smtp_username,
            'password' => $setting->smtp_password,
            'encryption' => $setting->smtp_encryption ?: null,
        ]);

        return Mail::mailer($mailerName);
    }

    private function buildResendMailer(Setting $setting): Mailer
    {
        $mailerName = 'dynamic_resend_'.Str::random(8);

        // The Resend package's client is a container singleton read once
        // from config('resend.api_key') — force it to rebuild with this
        // shop's key before the mailer (also freshly named) resolves it.
        Config::set('resend.api_key', $setting->resend_api_key);
        app()->forgetInstance(ResendClientContract::class);

        Config::set("mail.mailers.{$mailerName}", ['transport' => 'resend']);

        return Mail::mailer($mailerName);
    }
}
