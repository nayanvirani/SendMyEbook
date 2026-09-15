<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopify_product_id' => $this->shopify_product_id,
            'shopify_product_title' => $this->shopify_product_title,
            'shopify_variant_id' => $this->shopify_variant_id,
            'shopify_variant_title' => $this->shopify_variant_title,
            'status' => $this->status,
            'max_downloads' => $this->max_downloads,
            'expiration_value' => $this->expiration_value,
            'expiration_unit' => $this->expiration_unit,
            'revoke_on_refund' => $this->revoke_on_refund,
            'requires_license_key' => $this->requires_license_key,
            'watermark_pdfs' => $this->watermark_pdfs,
            'files' => FileResource::collection($this->whenLoaded('files')),
            'files_count' => $this->whenCounted('files'),
            'created_at' => $this->created_at,
        ];
    }
}
