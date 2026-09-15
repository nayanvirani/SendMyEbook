<?php

namespace App\Services\Mail;

use App\Models\Shop;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Resolves which mailer a shop's outgoing email should actually send
 * through. A merchant can plug in their own SMTP provider (any of them —
 * Gmail, SendGrid, Mailgun, their own server) in Settings so delivery
 * emails come from their own domain/brand instead of this app's shared
 * default sender; shops that haven't configured one fall back to it.
 */
class ShopMailerResolver
{
    /**
     * @return array{mailer: Mailer, fromAddress: string, fromName: string}
     */
    public function resolve(Shop $shop): array
    {
        $setting = $shop->setting;

        if ($setting?->hasCustomMailer()) {
            $mailerName = 'shop_'.$shop->id;

            Config::set("mail.mailers.{$mailerName}", [
                'transport' => 'smtp',
                'host' => $setting->smtp_host,
                'port' => $setting->smtp_port,
                'username' => $setting->smtp_username,
                'password' => $setting->smtp_password,
                'encryption' => $setting->smtp_encryption ?: null,
            ]);

            return [
                'mailer' => Mail::mailer($mailerName),
                'fromAddress' => $setting->mail_from_address ?: (string) config('mail.from.address'),
                'fromName' => $setting->email_from_name ?: $shop->shop_name,
            ];
        }

        return [
            'mailer' => Mail::mailer(),
            'fromAddress' => (string) config('mail.from.address'),
            'fromName' => $setting?->email_from_name ?: $shop->shop_name,
        ];
    }
}
