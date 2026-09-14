<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\Shopify\ShopifyGraphQLClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shopify-managed recurring billing (Shopify handles the actual card
 * charge; this app only creates/tracks the subscription record via the
 * Admin GraphQL API).
 */
class BillingController extends Controller
{
    public function plans(): JsonResponse
    {
        return response()->json(Plan::query()->where('is_active', true)->orderBy('sort_order')->get());
    }

    public function subscribe(Request $request): JsonResponse
    {
        $shop = $this->shop($request);

        $data = $request->validate(['plan_id' => ['required', 'exists:plans,id']]);
        $plan = Plan::query()->findOrFail($data['plan_id']);

        $client = new ShopifyGraphQLClient($shop);

        $response = $client->query(<<<'GQL'
            mutation appSubscriptionCreate($name: String!, $returnUrl: URL!, $lineItems: [AppSubscriptionLineItemInput!]!, $test: Boolean!) {
                appSubscriptionCreate(name: $name, returnUrl: $returnUrl, lineItems: $lineItems, test: $test) {
                    userErrors { field message }
                    confirmationUrl
                    appSubscription { id }
                }
            }
            GQL, [
            'name' => "SendMyEbook — {$plan->name}",
            'returnUrl' => route('billing.callback', ['shop' => $shop->shop_domain]),
            'test' => ! app()->isProduction(),
            'lineItems' => [[
                'plan' => [
                    'appRecurringPricingDetails' => [
                        'price' => ['amount' => (float) $plan->price, 'currencyCode' => 'USD'],
                        'interval' => 'EVERY_30_DAYS',
                    ],
                ],
            ]],
        ])->json('data.appSubscriptionCreate');

        if (! empty($response['userErrors'])) {
            return response()->json(['errors' => $response['userErrors']], 422);
        }

        Subscription::query()->create([
            'shop_id' => $shop->id,
            'plan_id' => $plan->id,
            'shopify_charge_id' => $response['appSubscription']['id'],
            'status' => 'pending',
        ]);

        return response()->json(['confirmation_url' => $response['confirmationUrl']]);
    }

    /**
     * Shopify redirects the merchant's top-level browser window here after
     * they approve or decline the charge on Shopify's own page.
     */
    public function callback(Request $request): RedirectResponse
    {
        $shopDomain = (string) $request->query('shop');
        $shop = Shop::query()->where('shop_domain', $shopDomain)->firstOrFail();

        $chargeId = (string) $request->query('charge_id');
        $subscription = Subscription::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_charge_id', 'like', "%{$chargeId}")
            ->latest()
            ->first();

        if ($subscription) {
            $client = new ShopifyGraphQLClient($shop);
            $node = $client->query(<<<'GQL'
                query($id: ID!) {
                    node(id: $id) {
                        ... on AppSubscription { status }
                    }
                }
                GQL, ['id' => $subscription->shopify_charge_id])->json('data.node');

            $subscription->update([
                'status' => strtolower($node['status'] ?? 'declined'),
                'current_period_end' => now()->addDays(30),
            ]);
        }

        return redirect()->route('embedded.app', ['shop' => $shopDomain]);
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
