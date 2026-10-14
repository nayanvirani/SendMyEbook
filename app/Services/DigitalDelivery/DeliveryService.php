<?php

namespace App\Services\DigitalDelivery;

use App\Models\DigitalProduct;
use App\Models\DownloadToken;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Turns a paid Shopify order into secure download access: matches each line
 * item against the shop's digital-product mappings and creates one
 * DownloadToken per matched digital product.
 */
class DeliveryService
{
    /**
     * @param  array<int, array{product_id: string|int, variant_id: string|int|null}>  $lineItems
     * @return Collection<int, DownloadToken>
     */
    public function createAccessForOrder(Shop $shop, Order $order, array $lineItems): Collection
    {
        $tokens = collect();

        foreach ($lineItems as $lineItem) {
            $digitalProduct = $this->matchDigitalProduct($shop, $lineItem);

            if (! $digitalProduct || ! $digitalProduct->isActive()) {
                continue;
            }

            $tokens->push($this->createTokenForProduct($order, $digitalProduct));
        }

        return $tokens;
    }

    /**
     * @param  array{product_id: string|int, variant_id: string|int|null}  $lineItem
     */
    private function matchDigitalProduct(Shop $shop, array $lineItem): ?DigitalProduct
    {
        $productId = (string) $lineItem['product_id'];
        $variantId = $lineItem['variant_id'] !== null ? (string) $lineItem['variant_id'] : null;

        // Prefer an exact variant-level mapping, fall back to a
        // whole-product mapping (shopify_variant_id is null).
        return $shop->digitalProducts()
            ->where('shopify_product_id', $productId)
            ->where(function ($query) use ($variantId) {
                $query->whereNull('shopify_variant_id')
                    ->orWhere('shopify_variant_id', $variantId);
            })
            ->orderByRaw('shopify_variant_id IS NULL') // exact variant match first
            ->first();
    }

    private function createTokenForProduct(Order $order, DigitalProduct $digitalProduct): DownloadToken
    {
        // Idempotent: a duplicate webhook delivery for the same order/product
        // reuses the existing token instead of minting a second one.
        $existing = DownloadToken::query()
            ->where('order_id', $order->id)
            ->where('digital_product_id', $digitalProduct->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DownloadToken::create([
            'order_id' => $order->id,
            'digital_product_id' => $digitalProduct->id,
            'token' => Str::random(48),
            'license_key' => $digitalProduct->requires_license_key ? $this->generateLicenseKey() : null,
            'max_downloads' => $this->effectiveMaxDownloads($digitalProduct),
            'download_count' => 0,
            'expires_at' => $this->calculateExpiry($digitalProduct),
            'status' => 'active',
        ]);
    }

    /**
     * Resolution order: the product's own override, else the shop's
     * store-wide default (Settings → "Default maximum downloads"), else
     * no limit from either — always bounded by the plan's ceiling. The
     * merchant's values are validated against that same ceiling when
     * they're set (see DigitalProductController/SettingController), but
     * leaving both unset means "unlimited" at the model level, which
     * would silently bypass the ceiling for any product/shop nobody
     * ever typed a number into. Re-applying it here, at the point the
     * limit actually takes effect, closes that gap: null only ever means
     * truly unlimited when the plan itself has no ceiling either.
     */
    private function effectiveMaxDownloads(DigitalProduct $digitalProduct): ?int
    {
        $shop = $digitalProduct->shop;
        $value = $digitalProduct->max_downloads ?? $shop->setting?->default_max_downloads;
        $planCeiling = $shop->currentPlan()?->max_downloads_per_product;

        return match (true) {
            $value === null => $planCeiling,
            $planCeiling === null => $value,
            default => min($value, $planCeiling),
        };
    }

    /**
     * A merchant-facing serial, not a cryptographic secret — it's shown to
     * the customer, not used to authorize anything on its own. Grouped for
     * readability: XXXX-XXXX-XXXX-XXXX.
     */
    private function generateLicenseKey(): string
    {
        return collect(range(1, 4))
            ->map(fn () => Str::upper(Str::random(4)))
            ->implode('-');
    }

    /**
     * Same fallback order as effectiveMaxDownloads(): the product's own
     * override, else the shop's store-wide default (Settings → "Default
     * link expiration"), else the link never expires.
     */
    private function calculateExpiry(DigitalProduct $digitalProduct): ?Carbon
    {
        if ($digitalProduct->expiration_value !== null) {
            return match ($digitalProduct->expiration_unit) {
                'hours' => now()->addHours($digitalProduct->expiration_value),
                default => now()->addDays($digitalProduct->expiration_value),
            };
        }

        $defaultDays = $digitalProduct->shop->setting?->default_expiration_days;

        return $defaultDays !== null ? now()->addDays($defaultDays) : null;
    }

    /**
     * Revoke every active download token for an order (used on refund).
     */
    public function revokeAccessForOrder(Order $order, string $reason = 'refund'): void
    {
        $order->downloadTokens()
            ->where('status', '!=', 'revoked')
            ->get()
            ->each(function (DownloadToken $token) use ($reason) {
                if ($token->digitalProduct->revoke_on_refund) {
                    $token->revoke($reason);
                }
            });
    }
}
