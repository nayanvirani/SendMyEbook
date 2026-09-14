<?php

namespace App\Services\Shopify;

use App\Models\Shop;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Shopify Admin GraphQL API, authenticated with a
 * specific shop's offline access token.
 */
class ShopifyGraphQLClient
{
    public function __construct(private readonly Shop $shop) {}

    /**
     * @param  array<string, mixed>  $variables
     */
    public function query(string $query, array $variables = []): Response
    {
        $version = config('shopify.api_version');

        return Http::withHeaders([
            'X-Shopify-Access-Token' => $this->shop->access_token,
            'Content-Type' => 'application/json',
        ])->post("https://{$this->shop->shop_domain}/admin/api/{$version}/graphql.json", [
            'query' => $query,
            'variables' => $variables,
        ]);
    }

    /**
     * Register the webhook topics this app needs (idempotent — Shopify
     * dedupes by callback URL + topic per app).
     */
    public function registerWebhooks(): void
    {
        $callbackBase = rtrim((string) config('shopify.app_url'), '/');

        foreach (config('shopify.webhook_topics') as $topic => $path) {
            $this->query(<<<'GQL'
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
            ]);
        }
    }
}
