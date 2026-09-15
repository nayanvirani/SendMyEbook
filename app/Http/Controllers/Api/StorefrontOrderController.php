<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Called by the Thank You page and Customer Account UI extensions
 * (extensions/thank-you-downloads, extensions/my-downloads) — both send
 * the same kind of session token as the embedded admin app, verified by
 * the same shopify.session middleware, plus the order id the extension
 * runtime hands them (never client-suppliable, so trustworthy).
 */
class StorefrontOrderController extends Controller
{
    public function downloads(Request $request, string $shopifyOrderId): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $order = Order::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_order_id', $shopifyOrderId)
            ->with('downloadTokens.digitalProduct')
            ->first();

        if (! $order) {
            return response()->json(['downloads' => []]);
        }

        $downloads = $order->downloadTokens->map(fn ($token) => [
            'product_title' => $token->digitalProduct->shopify_product_title,
            'status' => $token->status,
            'url' => $token->isRedeemable() ? route('customer.downloads.show', ['token' => $token->token]) : null,
            'license_key' => $token->license_key,
        ]);

        return response()->json(['downloads' => $downloads]);
    }
}
