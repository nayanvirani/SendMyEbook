<?php

namespace App\Services\Shopify;

use App\Models\Shop;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real payment history via Shopify's Partner API — an organization-level
 * credential (PARTNER_API_TOKEN + PARTNER_ORG_ID), different from the
 * per-shop Admin API token used everywhere else in this app. Only this
 * API can see charge history from before right now; a shop's own token
 * only ever shows its current subscription snapshot.
 *
 * The Partner org this app's Partner API token belongs to can also own
 * other, unrelated Shopify apps — the transactions query itself has no
 * per-app filter, so every result is cross-checked against this app's
 * own installed shop domains before being counted as this app's revenue.
 */
class PartnerApiClient
{
    private const CACHE_KEY = 'partner_api.transactions';

    private const CACHE_TTL_SECONDS = 300;

    public function isConfigured(): bool
    {
        return filled(config('shopify.partner_api_token')) && filled(config('shopify.partner_org_id'));
    }

    /**
     * @return array<int, array{id: string, created_at: string, gross_amount: float, net_amount: float, shopify_fee: float, currency: string, billing_interval: ?string, shop_name: ?string, shop_domain: ?string}>
     */
    public function getAppTransactions(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            try {
                return $this->fetchOwnShopTransactions();
            } catch (\Throwable $e) {
                Log::error('Partner API transactions fetch failed', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Every transaction from the Partner org, filtered down to shops
     * this app actually has installed — the query itself returns every
     * app in the org, so this is the only thing that keeps another
     * app's revenue from showing up here.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchOwnShopTransactions(): array
    {
        $ownDomains = Shop::query()->pluck('shop_domain')->map(fn ($d) => strtolower($d))->flip();

        return collect($this->fetchAllTransactions())
            ->filter(fn ($t) => $t['shop_domain'] && $ownDomains->has(strtolower($t['shop_domain'])))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchAllTransactions(): array
    {
        $token = config('shopify.partner_api_token');
        $orgId = config('shopify.partner_org_id');
        $version = config('shopify.partner_api_version');

        $all = [];
        $after = null;

        // 100, not the connection's 250 max — the nested shop/pricing
        // fields push a 250-per-page query over Shopify's query-complexity
        // ceiling (confirmed: 250 came back "complexity exceeds max").
        for (; ;) {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
                'Content-Type' => 'application/json',
            ])->post("https://partners.shopify.com/{$orgId}/api/{$version}/graphql.json", [
                'query' => <<<'GQL'
                    query($after: String) {
                        transactions(first: 100, after: $after, types: [APP_SUBSCRIPTION_SALE]) {
                            edges {
                                cursor
                                node {
                                    id
                                    createdAt
                                    ... on AppSubscriptionSale {
                                        grossAmount { amount currencyCode }
                                        netAmount { amount currencyCode }
                                        shopifyFee { amount currencyCode }
                                        billingInterval
                                        shop { name myshopifyDomain }
                                    }
                                }
                            }
                            pageInfo { hasNextPage }
                        }
                    }
                    GQL,
                'variables' => ['after' => $after],
            ]);

            if (! $response->successful()) {
                Log::error('Partner API transactions HTTP error', ['status' => $response->status()]);
                break;
            }

            $body = $response->json();

            if (! empty($body['errors'])) {
                Log::error('Partner API transactions GraphQL error', ['errors' => $body['errors']]);
                break;
            }

            $edges = $body['data']['transactions']['edges'] ?? [];

            foreach ($edges as $edge) {
                $node = $edge['node'];
                $all[] = [
                    'id' => $node['id'],
                    'created_at' => $node['createdAt'],
                    'gross_amount' => (float) ($node['grossAmount']['amount'] ?? 0),
                    'net_amount' => (float) ($node['netAmount']['amount'] ?? 0),
                    'shopify_fee' => (float) ($node['shopifyFee']['amount'] ?? 0),
                    'currency' => $node['grossAmount']['currencyCode'] ?? 'USD',
                    'billing_interval' => $node['billingInterval'] ?? null,
                    'shop_name' => $node['shop']['name'] ?? null,
                    'shop_domain' => $node['shop']['myshopifyDomain'] ?? null,
                ];
            }

            $hasNextPage = $body['data']['transactions']['pageInfo']['hasNextPage'] ?? false;
            $after = count($edges) ? end($edges)['cursor'] : null;

            if (! $hasNextPage || ! $after) {
                break;
            }
        }

        return $all;
    }

    /**
     * @param  array<int, array<string, mixed>>  $transactions
     * @return array<int, array{month: string, count: int, gross: float, net: float, currency: string}>
     */
    public function groupByMonth(array $transactions): array
    {
        $months = [];

        foreach ($transactions as $t) {
            $month = substr($t['created_at'], 0, 7); // "2026-09"
            $months[$month] ??= ['month' => $month, 'count' => 0, 'gross' => 0.0, 'net' => 0.0, 'currency' => $t['currency']];
            $months[$month]['count']++;
            $months[$month]['gross'] += $t['gross_amount'];
            $months[$month]['net'] += $t['net_amount'];
        }

        $months = array_values($months);
        usort($months, fn ($a, $b) => strcmp($b['month'], $a['month']));

        return $months;
    }
}
