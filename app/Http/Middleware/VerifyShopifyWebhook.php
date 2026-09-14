<?php

namespace App\Http\Middleware;

use App\Services\Shopify\ShopifyAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the HMAC signature on every incoming Shopify webhook and drops
 * duplicate deliveries (Shopify retries webhooks and does not guarantee
 * exactly-once delivery).
 */
class VerifyShopifyWebhook
{
    public function __construct(private readonly ShopifyAuthService $authService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');

        if (! $this->authService->verifyWebhookHmac($request->getContent(), $hmacHeader)) {
            abort(401, 'Invalid webhook signature.');
        }

        $webhookId = $request->header('X-Shopify-Webhook-Id');

        if ($webhookId) {
            $cacheKey = "shopify:webhook:{$webhookId}";

            if (Cache::has($cacheKey)) {
                return response()->json(['status' => 'duplicate_ignored']);
            }

            Cache::put($cacheKey, true, now()->addHours(24));
        }

        return $next($request);
    }
}
