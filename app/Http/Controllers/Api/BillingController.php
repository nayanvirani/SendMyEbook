<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\Plan;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Billing is Shopify App Pricing (Managed Pricing): plans, prices and
 * trials live in the Partner Dashboard, and merchants pick a plan on
 * Shopify's own hosted page — this app never creates a subscription
 * itself. It only displays the plans (for a consistent in-app preview)
 * and links out to that hosted page; app_subscriptions/update webhooks
 * (see Webhooks\WebhookController) are the only way this app learns a
 * subscription actually happened.
 */
class BillingController extends Controller
{
    public function plans(): JsonResponse
    {
        return response()->json(Plan::query()->where('is_active', true)->orderBy('sort_order')->get());
    }

    /**
     * Lets the embedded app decide, before hitting anything else, whether
     * to show the paywall or the real app — and gives it the link to
     * Shopify's hosted plan page for the paywall's call to action.
     */
    public function status(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $subscription = $shop->activeSubscription;
        $plan = $subscription?->plan;

        return response()->json([
            'active' => (bool) $subscription,
            'plan' => $plan?->only(['id', 'name', 'handle']),
            'manage_plan_url' => $this->managePlanUrl($shop),
            'usage' => $this->usage($shop, $plan),
            'features' => [
                'watermarking' => (bool) $plan?->hasFeature('watermarking'),
                'custom_branding' => (bool) $plan?->hasFeature('custom_branding'),
                'download_analytics' => (bool) $plan?->hasFeature('download_analytics'),
                'priority_support' => (bool) $plan?->hasFeature('priority_support'),
            ],
            'limits' => [
                'max_downloads_per_product' => $plan?->max_downloads_per_product,
                'max_file_size_mb' => $plan?->max_file_size_mb,
            ],
        ]);
    }

    /**
     * @return array{digital_products: array{used: int, limit: ?int}, deliveries_this_month: array{used: int, limit: ?int}}
     */
    private function usage(Shop $shop, ?Plan $plan): array
    {
        $deliveriesThisMonth = EmailLog::query()
            ->where('shop_id', $shop->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->whereIn('status', ['sent', 'queued'])
            ->count();

        return [
            'digital_products' => [
                'used' => $shop->digitalProducts()->count(),
                'limit' => $plan?->max_digital_products,
            ],
            'deliveries_this_month' => [
                'used' => $deliveriesThisMonth,
                'limit' => $plan?->max_deliveries_per_month,
            ],
        ];
    }

    private function managePlanUrl(Shop $shop): string
    {
        $shopHandle = str_replace('.myshopify.com', '', $shop->shop_domain);
        $appHandle = config('shopify.app_handle');

        return "https://admin.shopify.com/store/{$shopHandle}/charges/{$appHandle}/pricing_plans";
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
