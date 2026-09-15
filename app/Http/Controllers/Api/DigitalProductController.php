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
        $products = $this->shop($request)->digitalProducts()
            ->withCount('files')
            ->latest()
            ->paginate(20);

        return response()->json(DigitalProductResource::collection($products)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $data = $this->validated($request);

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

        $digitalProduct->update($this->validated($request, $digitalProduct));

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
    private function validated(Request $request, ?DigitalProduct $existing = null): array
    {
        return $request->validate([
            'shopify_product_id' => ['required', 'string'],
            'shopify_product_title' => ['nullable', 'string'],
            'shopify_variant_id' => ['nullable', 'string'],
            'shopify_variant_title' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
            'max_downloads' => ['nullable', 'integer', 'min:1'],
            'expiration_value' => ['nullable', 'integer', 'min:1'],
            'expiration_unit' => ['sometimes', 'in:hours,days'],
            'revoke_on_refund' => ['sometimes', 'boolean'],
            'requires_license_key' => ['sometimes', 'boolean'],
            'watermark_pdfs' => ['sometimes', 'boolean'],
        ]);
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
