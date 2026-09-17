<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Shop;
use App\Services\Mail\ShopMailerResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $setting = $shop->setting ?? $shop->setting()->create([]);

        return response()->json($this->present($setting));
    }

    public function update(Request $request): JsonResponse
    {
        $shop = $this->shop($request);

        $data = $request->validate([
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email'],
            'default_max_downloads' => ['nullable', 'integer', 'min:1'],
            'default_expiration_days' => ['nullable', 'integer', 'min:1'],
            'logo_url' => ['nullable', 'url'],
            'brand_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'mail_enabled' => ['sometimes', 'boolean'],
            'mail_provider' => ['sometimes', 'in:platform_default,smtp,mailgun,sendgrid,postmark,ses,resend'],
            'mail_from_address' => ['nullable', 'email'],
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

        // None of these secrets round-trip back to the browser (see
        // present() below), so an empty value here means "leave it
        // alone", not "clear it" — only overwrite when the merchant
        // actually typed a new one.
        foreach ([
            'smtp_password', 'resend_api_key', 'mailgun_api_key', 'sendgrid_api_key',
            'postmark_api_key', 'ses_access_key_id', 'ses_secret_access_key',
        ] as $secret) {
            if (! filled($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        $setting = $shop->setting()->updateOrCreate([], $data);

        return response()->json($this->present($setting));
    }

    /**
     * Sends a real test email through whatever provider this shop
     * currently has configured — regardless of the mail_enabled switch,
     * since testing before switching it on is the whole point.
     */
    public function testEmail(Request $request, ShopMailerResolver $resolver): JsonResponse
    {
        $shop = $this->shop($request);
        $setting = $shop->setting;

        $data = $request->validate(['to' => ['required', 'email']]);

        try {
            $mailer = $resolver->buildMailer($setting);
            $fromAddress = $setting?->mail_from_address ?: $resolver->defaultFromAddress();
            $fromName = $setting?->email_from_name ?: $shop->shop_name;

            $mailer->raw(
                "This is a test email from {$shop->shop_name}'s SendMyEbook settings. If you received this, your email configuration works.",
                function ($message) use ($data, $fromAddress, $fromName) {
                    $message->to($data['to'])
                        ->from($fromAddress, $fromName)
                        ->subject('SendMyEbook test email');
                }
            );
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['status' => 'sent']);
    }

    /**
     * Renders the real delivery-email template with this shop's actual
     * branding (logo, color, support address) plus sample order/product
     * data, so a merchant can see exactly what a customer receives
     * without needing a real order or a delivered email.
     */
    public function emailPreview(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $setting = $shop->setting;

        $sampleOrder = new \stdClass;
        $sampleOrder->customer_name = 'Jamie';
        $sampleOrder->shopify_order_number = '1001';

        $html = view('emails.digital-delivery', [
            'shop' => $shop,
            'order' => $sampleOrder,
            'logoUrl' => $setting?->logo_url,
            'brandColor' => $setting?->brand_color ?: '#008060',
            'supportEmail' => $setting?->support_email,
            'downloadLinks' => collect([
                [
                    'productTitle' => 'Sample Digital Product',
                    'url' => '#',
                    'maxDownloads' => 5,
                    'expiresAt' => now()->addDays(7),
                    'licenseKey' => 'SAMPLE-1234-ABCD',
                ],
            ]),
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Setting $setting): array
    {
        return [
            ...$setting->only([
                'email_from_name', 'support_email', 'default_max_downloads',
                'default_expiration_days', 'logo_url', 'brand_color',
                'mail_enabled', 'mail_provider', 'mail_from_address',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption',
                'mailgun_domain', 'mailgun_region', 'ses_region',
            ]),
            'has_smtp_password' => filled($setting->smtp_password),
            'has_resend_api_key' => filled($setting->resend_api_key),
            'has_mailgun_api_key' => filled($setting->mailgun_api_key),
            'has_sendgrid_api_key' => filled($setting->sendgrid_api_key),
            'has_postmark_api_key' => filled($setting->postmark_api_key),
            'has_ses_access_key_id' => filled($setting->ses_access_key_id),
            'has_ses_secret_access_key' => filled($setting->ses_secret_access_key),
        ];
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
