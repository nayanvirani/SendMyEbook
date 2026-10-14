<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DigitalProductResource;
use App\Models\DigitalProduct;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $this->shop($request)->digitalProducts()
            ->withCount('files')
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(fn ($q) => $q
                ->where('shopify_product_title', 'ilike', "%{$search}%")
                ->orWhere('shopify_product_id', 'ilike', "%{$search}%"));
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $products = $query->paginate(20)->withQueryString();

        return response()->json(DigitalProductResource::collection($products)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $data = $this->validated($request, $shop);

        $limit = $shop->currentPlan()?->max_digital_products;

        if ($limit !== null && $shop->digitalProducts()->count() >= $limit) {
            return response()->json([
                'error' => 'plan_limit_reached',
                'message' => "Your plan allows up to {$limit} digital products. Upgrade to add more.",
            ], 402);
        }

        if ($error = $this->watermarkFeatureError($shop, $data, null)) {
            return $error;
        }

        $product = $shop->digitalProducts()->create($data);

        return response()->json(new DigitalProductResource($product), 201);
    }

    public function show(Request $request, DigitalProduct $digitalProduct): JsonResponse
    {
        $this->authorizeShop($request, $digitalProduct);

        return response()->json(new DigitalProductResource($digitalProduct->load('files')));
    }

    public function update(Request $request, DigitalProduct $digitalProduct): JsonResponse
    {
        $this->authorizeShop($request, $digitalProduct);
        $data = $this->validated($request, $this->shop($request), $digitalProduct);

        if ($error = $this->watermarkFeatureError($this->shop($request), $data, $digitalProduct)) {
            return $error;
        }

        $digitalProduct->update($data);

        return response()->json(new DigitalProductResource($digitalProduct->load('files')));
    }

    public function destroy(Request $request, DigitalProduct $digitalProduct): JsonResponse
    {
        $this->authorizeShop($request, $digitalProduct);

        $digitalProduct->delete();

        return response()->json(status: 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Shop $shop, ?DigitalProduct $existing = null): array
    {
        $maxDownloadsCeiling = $shop->currentPlan()?->max_downloads_per_product;

        return $request->validate([
            'shopify_product_id' => ['required', 'string'],
            'shopify_product_title' => ['nullable', 'string'],
            'shopify_variant_id' => ['nullable', 'string'],
            'shopify_variant_title' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
            'max_downloads' => array_filter([
                'nullable', 'integer', 'min:1',
                $maxDownloadsCeiling !== null ? "max:{$maxDownloadsCeiling}" : null,
            ]),
            'expiration_value' => ['nullable', 'integer', 'min:1'],
            'expiration_unit' => ['sometimes', 'in:hours,days'],
            'revoke_on_refund' => ['sometimes', 'boolean'],
            'requires_license_key' => ['sometimes', 'boolean'],
            'watermark_pdfs' => ['sometimes', 'boolean'],
        ], [
            'max_downloads.max' => $maxDownloadsCeiling !== null
                ? "Your plan allows up to {$maxDownloadsCeiling} downloads per product. Upgrade for a higher limit, or leave it unset to use the maximum your plan allows."
                : null,
        ]);
    }

    /**
     * Only blocks when watermark_pdfs is actually being turned on (not
     * merely present, unchanged, in the payload) — a Pro-created product
     * with it already set stays untouched when the shop edits other
     * fields after downgrading to Free.
     *
     * @param  array<string, mixed>  $data
     */
    private function watermarkFeatureError(Shop $shop, array $data, ?DigitalProduct $existing): ?JsonResponse
    {
        $turningOn = ($data['watermark_pdfs'] ?? false) && ! ($existing?->watermark_pdfs ?? false);

        if ($turningOn && ! $shop->hasFeature('watermarking')) {
            return response()->json([
                'error' => 'plan_feature_required',
                'message' => 'PDF watermarking is a Pro feature. Upgrade to turn it on.',
            ], 402);
        }

        return null;
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }

    private function authorizeShop(Request $request, DigitalProduct $digitalProduct): void
    {
        abort_unless($digitalProduct->shop_id === $this->shop($request)->id, 403);
    }
}
