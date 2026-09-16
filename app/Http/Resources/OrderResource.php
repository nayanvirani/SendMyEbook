<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shopify_order_number' => $this->shopify_order_number,
            'customer_email' => $this->customer_email,
            'customer_name' => $this->customer_name,
            'financial_status' => $this->financial_status,
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'is_refunded' => $this->is_refunded,
            'is_cancelled' => $this->is_cancelled,
            'is_closed' => $this->is_closed,
            'download_tokens' => $this->whenLoaded('downloadTokens', fn () => $this->downloadTokens->map(fn ($token) => [
                'id' => $token->id,
                'digital_product_title' => $token->digitalProduct->shopify_product_title,
                'status' => $token->status,
                'download_count' => $token->download_count,
                'max_downloads' => $token->max_downloads,
                'last_downloaded_at' => $token->last_downloaded_at,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
