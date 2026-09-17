<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\Mail\ShopMailerResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * The platform owner's own email provider configuration — used as every
 * shop's "platform_default" mailer (see ShopMailerResolver). Railway
 * doesn't provide an email-sending service itself, so a shop that never
 * configures its own provider needs this set up here or nothing actually
 * sends (Laravel's own MAIL_MAILER env config starts out as "log").
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'setting' => PlatformSetting::query()->first() ?? new PlatformSetting,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_from_address' => ['nullable', 'email'],
            'mail_provider' => ['nullable', 'in:smtp,mailgun,sendgrid,postmark,ses,resend'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl'],
            'resend_api_key' => ['nullable', 'string'],
            'mailgun_api_key' => ['nullable', 'string'],
            'mailgun_domain' => ['nullable', 'string', 'max:255'],
            'mailgun_region' => ['nullable', 'in:us,eu'],
            'sendgrid_api_key' => ['nullable', 'string'],
            'postmark_api_key' => ['nullable', 'string'],
            'ses_access_key_id' => ['nullable', 'string'],
            'ses_secret_access_key' => ['nullable', 'string'],
            'ses_region' => ['nullable', 'string', 'max:32'],
        ]);

        // None of these secrets round-trip back to the browser (see the
        // view, which only ever shows a "saved" placeholder), so an
        // empty value here means "leave it alone", not "clear it" — only
        // overwrite when the operator actually typed a new one.
        foreach ([
            'smtp_password', 'resend_api_key', 'mailgun_api_key',
            'sendgrid_api_key', 'postmark_api_key', 'ses_access_key_id', 'ses_secret_access_key',
        ] as $secret) {
            if (! filled($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        $setting = PlatformSetting::query()->first();

        if ($setting) {
            $setting->update($data);
        } else {
            PlatformSetting::create($data);
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Email settings saved.');
    }

    public function sendTest(Request $request, ShopMailerResolver $resolver): RedirectResponse
    {
        $data = $request->validate(['test_email' => ['required', 'email']]);

        try {
            $mailer = $resolver->buildPlatformDefaultMailer();
            $fromAddress = $resolver->defaultFromAddress();

            $mailer->raw(
                'This is a test email from SendMyEbook\'s platform email settings. If you received this, your configuration works.',
                function ($message) use ($data, $fromAddress) {
                    $message->to($data['test_email'])
                        ->from($fromAddress, 'SendMyEbook')
                        ->subject('SendMyEbook platform test email');
                }
            );
        } catch (Throwable $e) {
            return redirect()->route('admin.settings.edit')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.settings.edit')->with('status', "Test email sent to {$data['test_email']}.");
    }
}
