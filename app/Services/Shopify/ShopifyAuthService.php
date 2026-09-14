<?php

namespace App\Services\Shopify;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Handles the classic Shopify OAuth install/callback flow: building the
 * merchant-consent redirect, verifying the callback, and exchanging the
 * authorization code for a permanent (offline) access token.
 */
class ShopifyAuthService
{
    public function buildAuthorizeUrl(string $shop, string $state): string
    {
        $query = http_build_query([
            'client_id' => config('shopify.api_key'),
            'scope' => implode(',', config('shopify.scopes')),
            'redirect_uri' => rtrim((string) config('shopify.app_url'), '/').'/auth/callback',
            'state' => $state,
            'grant_options[]' => '',
        ]);

        return "https://{$shop}/admin/oauth/authorize?{$query}";
    }

    /**
     * Verify the HMAC signature Shopify attaches to every OAuth callback
     * and app-proxy/admin-link request.
     *
     * @param  array<string, string>  $query
     */
    public function verifyHmac(array $query): bool
    {
        $hmac = $query['hmac'] ?? null;

        if (! $hmac) {
            return false;
        }

        $params = $query;
        unset($params['hmac'], $params['signature']);
        ksort($params);

        $computed = hash_hmac('sha256', http_build_query($params), (string) config('shopify.api_secret'));

        return hash_equals($computed, $hmac);
    }

    public function isValidShopDomain(string $shop): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop);
    }

    /**
     * Exchange the OAuth authorization code for an offline access token.
     *
     * @return array{access_token: string, scope: string}
     */
    public function exchangeCodeForToken(string $shop, string $code): array
    {
        $response = Http::asJson()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('shopify.api_key'),
            'client_secret' => config('shopify.api_secret'),
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Shopify token exchange failed: '.$response->body());
        }

        return $response->json();
    }

    public function generateState(): string
    {
        return Str::random(40);
    }

    /**
     * Token Exchange: trades the App Bridge session token (a short-lived
     * ID token) for a real offline Admin API access token, without any
     * redirect or iframe navigation. This is what `use_legacy_install_flow
     * = false` in shopify.app.toml expects the app to do itself on first
     * contact with a shop — Shopify's own "managed installation" grants
     * scopes and embeds the app before we ever see a request, so there is
     * no authorization code to exchange, only this session token.
     *
     * @return array{access_token: string, scope: string}
     */
    public function exchangeSessionTokenForOfflineToken(string $shop, string $sessionToken): array
    {
        $response = Http::asJson()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => config('shopify.api_key'),
            'client_secret' => config('shopify.api_secret'),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
            'subject_token' => $sessionToken,
            'subject_token_type' => 'urn:ietf:params:oauth:token-type:id_token',
            'requested_token_type' => 'urn:shopify:params:oauth:token-type:offline-access-token',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Shopify token exchange failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Verify the X-Shopify-Hmac-Sha256 header on an incoming webhook request.
     */
    public function verifyWebhookHmac(string $rawBody, ?string $header): bool
    {
        if (! $header) {
            return false;
        }

        $computed = base64_encode(hash_hmac('sha256', $rawBody, (string) config('shopify.api_secret'), true));

        return hash_equals($computed, $header);
    }
}
