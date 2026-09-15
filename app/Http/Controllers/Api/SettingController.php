<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'mail_from_address' => ['nullable', 'email'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl'],
        ]);

        // The password never round-trips back to the browser (see present()
        // below), so an empty value here means "leave it alone", not "clear
        // it" — only overwrite when the merchant actually typed a new one.
        if (! filled($data['smtp_password'] ?? null)) {
            unset($data['smtp_password']);
        }

        $setting = $shop->setting()->updateOrCreate([], $data);

        return response()->json($this->present($setting));
    }

    /**
     * Sends a real test email through whatever mailer this shop is
     * currently configured to use, so a merchant can confirm their own
     * SMTP settings actually work before relying on them.
     */
    public function testEmail(Request $request, ShopMailerResolver $resolver): JsonResponse
    {
        $shop = $this->shop($request);

        $data = $request->validate(['to' => ['required', 'email']]);

        try {
            $resolved = $resolver->resolve($shop);

            $resolved['mailer']->raw(
                "This is a test email from {$shop->shop_name}'s SendMyEbook settings. If you received this, your email configuration works.",
                function ($message) use ($data, $resolved) {
                    $message->to($data['to'])
                        ->from($resolved['fromAddress'], $resolved['fromName'])
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
    private function present(mixed $setting): array
    {
        return [
            ...$setting->only([
                'email_from_name', 'support_email', 'default_max_downloads',
                'default_expiration_days', 'logo_url', 'brand_color', 'mail_from_address',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption',
            ]),
            'has_smtp_password' => filled($setting->smtp_password),
        ];
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
