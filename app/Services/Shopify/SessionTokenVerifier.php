<?php

namespace App\Services\Shopify;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

/**
 * Verifies the JWT session token that Shopify App Bridge attaches to every
 * authenticated fetch call from the embedded admin app
 * (Authorization: Bearer <token>).
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
        } catch (SignatureInvalidException|UnexpectedValueException) {
            return null;
        }

        $dest = $payload->dest ?? null; // e.g. https://my-shop.myshopify.com

        if (! $dest) {
            return null;
        }

        $shop = str_replace(['https://', 'http://'], '', $dest);

        return ['shop' => $shop, 'payload' => $payload];
    }
}
