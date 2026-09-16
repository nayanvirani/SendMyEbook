<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderPaidJob;
use App\Jobs\ProcessRefundCreatedJob;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Handlers for Shopify's topic webhooks (see config/shopify.php for the
 * subscribed topics and app/Http/Middleware/VerifyShopifyWebhook for
 * signature verification + dedup, applied to every route in this
 * controller). Every handler responds fast and pushes real work onto the
 * queue, since Shopify expects a 2xx within a few seconds.
 */
class WebhookController extends Controller
{
    public function ordersPaid(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();

        $order = Order::query()->updateOrCreate(
            ['shop_id' => $shop->id, 'shopify_order_id' => (string) $payload['id']],
            [
                'shopify_order_number' => (string) ($payload['order_number'] ?? $payload['name'] ?? ''),
                'customer_email' => $payload['email'] ?? $payload['customer']['email'] ?? null,
                'customer_name' => trim(($payload['customer']['first_name'] ?? '').' '.($payload['customer']['last_name'] ?? '')) ?: null,
                'financial_status' => $payload['financial_status'] ?? null,
                'total_price' => $payload['total_price'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'raw_payload' => $payload,
            ]
        );

        $lineItems = collect($payload['line_items'] ?? [])
            ->map(fn ($item) => ['product_id' => $item['product_id'] ?? null, 'variant_id' => $item['variant_id'] ?? null])
            ->filter(fn ($item) => $item['product_id'] !== null)
            ->values()
            ->all();

        ProcessOrderPaidJob::dispatch($shop, $order, $lineItems);

        return response()->json(['status' => 'accepted']);
    }

    public function ordersUpdated(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();

        // Archiving an order (closed_at set) is a merchant workflow status,
        // not a cancellation or refund — a paid, fulfilled, archived order
        // is the normal happy path and must not revoke anything. Tracked
        // here purely for visibility. Cancellation (cancelled_at set) DOES
        // revoke access, same as this app's other order-invalidating
        // events — but only once, via the dedicated orders/cancelled
        // handler below; this just keeps the flag in sync if an update
        // arrives that already reflects a cancellation made elsewhere.
        Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', (string) $payload['id'])
            ->update([
                'financial_status' => $payload['financial_status'] ?? null,
                'is_cancelled' => filled($payload['cancelled_at'] ?? null),
                'is_closed' => filled($payload['closed_at'] ?? null),
                'raw_payload' => $payload,
            ]);

        return response()->json(['status' => 'accepted']);
    }

    /**
     * A cancelled order is no longer a legitimate sale — unlike the
     * merchant's per-product "revoke on refund" setting (which is about
     * refunds specifically, and optional), cancellation revokes download
     * access unconditionally, same reasoning as a hard delete. Cancelling
     * does not by itself delete or refund the order, so this is the only
     * signal for it — orders/updated reflects the resulting cancelled_at
     * field, but this dedicated topic is what actually fires the action.
     */
    public function ordersCancelled(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();

        $order = Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', (string) $payload['id'])
            ->first();

        if ($order) {
            $order->update(['is_cancelled' => true]);
            $this->revokeDownloadsForOrder($order, 'order_cancelled');
        }

        return response()->json(['status' => 'accepted']);
    }

    public function refundsCreate(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();

        $order = Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', (string) $payload['order_id'])
            ->first();

        if ($order) {
            ProcessRefundCreatedJob::dispatch($order);
        }

        return response()->json(['status' => 'accepted']);
    }

    /**
     * Shopify sends this on a hard order deletion — a different topic
     * from orders/updated, which never reflects deletion (only fields
     * like cancelled_at/financial_status on an order that still exists).
     * The order no longer exists in Shopify at all, so download access
     * is revoked unconditionally here, unlike refunds — the merchant's
     * per-product "revoke on refund" setting is about refunds
     * specifically and shouldn't gate this. The local Order/Download
     * rows are kept (not deleted) so the activity stays auditable, same
     * as every other access-revoking event in this app.
     */
    public function ordersDelete(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();

        $order = Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', (string) $payload['id'])
            ->first();

        if ($order) {
            $this->revokeDownloadsForOrder($order, 'order_deleted');
        }

        return response()->json(['status' => 'accepted']);
    }

    private function revokeDownloadsForOrder(Order $order, string $reason): void
    {
        $order->downloadTokens()
            ->where('status', '!=', 'revoked')
            ->get()
            ->each(fn ($token) => $token->revoke($reason));
    }

    public function appUninstalled(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);

        $shop->update(['is_active' => false, 'uninstalled_at' => now()]);

        return response()->json(['status' => 'accepted']);
    }

    /**
     * Mandatory GDPR compliance webhooks. The MVP holds no customer data
     * beyond what is required for delivery (email address on the Order
     * record), which these handlers redact/report on request.
     */
    public function customersDataRequest(Request $request): JsonResponse
    {
        return response()->json(['status' => 'accepted']);
    }

    public function customersRedact(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();
        $email = $payload['customer']['email'] ?? null;

        if ($email) {
            Order::query()->where('shop_id', $shop->id)->where('customer_email', $email)
                ->update(['customer_email' => null, 'customer_name' => null]);
        }

        return response()->json(['status' => 'accepted']);
    }

    public function shopRedact(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $shop->delete();

        return response()->json(['status' => 'accepted']);
    }

    /**
     * Shopify App Pricing: the only place this app ever learns that a
     * shop actually has a subscription. Fires on every status change
     * (created, activated, cancelled, frozen, ...) for a plan picked on
     * Shopify's own hosted page — this app never creates the charge.
     */
    public function appSubscriptionsUpdate(Request $request): JsonResponse
    {
        $shop = $this->resolveShop($request);
        $payload = $request->json()->all();
        $subscription = $payload['app_subscription'] ?? $payload;

        $chargeId = (string) ($subscription['admin_graphql_api_id'] ?? '');
        $planName = strtolower((string) ($subscription['name'] ?? ''));
        $status = strtolower((string) ($subscription['status'] ?? 'pending'));

        if (! $chargeId) {
            return response()->json(['status' => 'ignored']);
        }

        DB::transaction(function () use ($shop, $chargeId, $planName, $status) {
            Subscription::query()->updateOrCreate(
                ['shop_id' => $shop->id, 'shopify_charge_id' => $chargeId],
                [
                    'plan_id' => Plan::query()->where('handle', $planName)->value('id'),
                    'shopify_plan_name' => $planName ?: null,
                    'status' => $status,
                ]
            );

            // Shopify only ever has one subscription active per shop — when
            // this one activates, any other row still marked active is a
            // stale prior plan (a switch that never got its own "cancelled"
            // webhook, or arrived out of order) and must not keep gating
            // the app as if it were current.
            if ($status === 'active') {
                Subscription::query()
                    ->where('shop_id', $shop->id)
                    ->where('shopify_charge_id', '!=', $chargeId)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled']);
            }
        });

        return response()->json(['status' => 'accepted']);
    }

    private function resolveShop(Request $request): Shop
    {
        $domain = $request->header('X-Shopify-Shop-Domain');

        return Shop::query()->where('shop_domain', $domain)->firstOrFail();
    }
}
