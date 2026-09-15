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
            'mail_provider' => ['sometimes', 'in:platform_default,smtp,resend'],
            'mail_from_address' => ['nullable', 'email'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl'],
            'resend_api_key' => ['nullable', 'string'],
        ]);

        // Neither secret round-trips back to the browser (see present()
        // below), so an empty value here means "leave it alone", not
        // "clear it" — only overwrite when the merchant actually typed a
        // new one.
        foreach (['smtp_password', 'resend_api_key'] as $secret) {
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
            $fromAddress = $setting?->mail_from_address ?: (string) config('mail.from.address');
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
            ]),
            'has_smtp_password' => filled($setting->smtp_password),
            'has_resend_api_key' => filled($setting->resend_api_key),
        ];
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
