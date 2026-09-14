<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file->original_filename,
            'order_number' => $this->downloadToken->order->shopify_order_number,
            'digital_product_title' => $this->downloadToken->digitalProduct->shopify_product_title,
            'ip_address' => $this->ip_address,
            'downloaded_at' => $this->downloaded_at,
        ];
    }
}
