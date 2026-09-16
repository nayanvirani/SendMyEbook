<?php

namespace App\Services\Shopify;

use App\Models\Shop;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin wrapper around the Shopify Admin GraphQL API, authenticated with a
 * specific shop's offline access token.
 */
class ShopifyGraphQLClient
{
    public function __construct(private readonly Shop $shop, private readonly ShopifyAuthService $authService = new ShopifyAuthService) {}

    /**
     * @param  array<string, mixed>  $variables
     */
    public function query(string $query, array $variables = []): Response
    {
        $this->ensureFreshToken();

        $version = config('shopify.api_version');

        return Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
            'Content-Type' => 'application/json',
        ])->post("https://{$this->shop->shop_domain}/admin/api/{$version}/graphql.json", [
            'query' => $query,
            // An empty PHP array json-encodes as `[]`, and Shopify's
            // GraphQL endpoint rejects that as "Invalid variables
            // parameter" for a query with no variable placeholders — it
            // wants `{}` (or the key omitted). Every other caller happens
            // to pass real variables, so this stayed latent until the
            // first no-variables query.
            'variables' => $variables ?: new \stdClass,
        ]);
    }

    /**
     * Offline access tokens now expire after an hour. Refresh proactively
     * (using the stored refresh_token) whenever the token is missing an
     * expiry, or is close to it, before making the actual API call.
     */
    private function ensureFreshToken(): void
    {
        if (! $this->shop->accessTokenNeedsRefresh() || ! $this->shop->refresh_token) {
            return;
        }

        try {
            $tokenResponse = $this->authService->refreshOfflineToken($this->shop->shop_domain, $this->shop->refresh_token);
        } catch (Throwable $e) {
            // Fall through and let the API call itself fail with the stale
            // token rather than blocking the request entirely — a refresh
            // failure here shouldn't be fatal if the current token still
            // happens to work for a few more seconds.
            Log::error('Shopify offline token refresh failed', [
                'shop' => $this->shop->shop_domain,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $this->shop->forceFill([
            'access_token' => $tokenResponse['access_token'],
            'refresh_token' => $tokenResponse['refresh_token'],
            'access_token_expires_at' => now()->addSeconds((int) $tokenResponse['expires_in']),
        ])->save();
    }

    /**
     * @return array{name: ?string, email: ?string}
     */
    public function fetchShopDetails(): array
    {
        $body = $this->query('{ shop { name email } }')->json();

        if (! empty($body['errors'])) {
            Log::error('Shopify shop details fetch failed', [
                'shop' => $this->shop->shop_domain,
                'errors' => $body['errors'],
            ]);

            return ['name' => null, 'email' => null];
        }

        return [
            'name' => $body['data']['shop']['name'] ?? null,
            'email' => $body['data']['shop']['email'] ?? null,
        ];
    }

    /**
     * Register the webhook topics this app needs (idempotent — Shopify
     * dedupes by callback URL + topic per app).
     */
    public function registerWebhooks(): void
    {
        $callbackBase = rtrim((string) config('shopify.app_url'), '/');

        foreach (config('shopify.webhook_topics') as $topic => $path) {
            $body = $this->query(<<<'GQL'
                mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!) {
                    webhookSubscriptionCreate(topic: $topic, webhookSubscription: $webhookSubscription) {
                        userErrors { field message }
                        webhookSubscription { id }
                    }
                }
                GQL, [
                'topic' => str_replace('/', '_', strtoupper($topic)),
                'webhookSubscription' => [
                    'callbackUrl' => $callbackBase.$path,
                    'format' => 'JSON',
                ],
            ])->json();

            $errors = $body['data']['webhookSubscriptionCreate']['userErrors'] ?? [];

            if (! empty($errors) || ! empty($body['errors'])) {
                Log::error('Shopify webhook registration failed', [
                    'shop' => $this->shop->shop_domain,
                    'topic' => $topic,
                    'errors' => $errors ?: $body['errors'],
                ]);
            }
        }
    }
}
