<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderPaidJob;
use App\Jobs\ProcessRefundCreatedJob;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', (string) $payload['id'])
            ->update([
                'financial_status' => $payload['financial_status'] ?? null,
                'raw_payload' => $payload,
            ]);

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

    private function resolveShop(Request $request): Shop
    {
        $domain = $request->header('X-Shopify-Shop-Domain');

        return Shop::query()->where('shop_domain', $domain)->firstOrFail();
    }
}
