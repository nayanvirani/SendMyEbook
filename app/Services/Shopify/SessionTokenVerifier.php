<?php

namespace App\Services\Shopify;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Verifies the JWT session token that Shopify App Bridge attaches to every
 * authenticated fetch call from the embedded admin app, and the same kind
 * of token checkout/customer-account UI extensions attach via
 * shopify.sessionToken.get() (Authorization: Bearer <token>).
 */
class SessionTokenVerifier
{
    /**
     * @return array{shop: string, payload: object}|null Null when the token
     *                                                   is missing, malformed, expired, or signed with the wrong secret.
     */
    public function verify(?string $bearerToken): ?array
    {
        if (! $bearerToken) {
            return null;
        }

        try {
            $payload = JWT::decode($bearerToken, new Key((string) config('shopify.api_secret'), 'HS256'));
        } catch (Throwable $e) {
            // Every failure mode here (bad signature, expired, malformed,
            // even a misconfigured key) previously resulted in the exact
            // same silent 401 — worth knowing which one it actually is
            // when a customer-facing extension mysteriously renders
            // nothing rather than guessing blind again.
            Log::warning('Shopify session token verification failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $dest = $payload->dest ?? null; // e.g. https://my-shop.myshopify.com

        if (! $dest) {
            Log::warning('Shopify session token had no dest claim', ['payload' => (array) $payload]);

            return null;
        }

        $shop = str_replace(['https://', 'http://'], '', $dest);

        return ['shop' => $shop, 'payload' => $payload];
    }
}
