<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Shop;
use App\Services\DigitalDelivery\DeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs after the orders/paid webhook has stored the raw order: matches line
 * items to digital products, creates download access, and queues the
 * delivery email. Kept out of the webhook controller so the webhook can
 * respond to Shopify in well under its timeout.
 */
class ProcessOrderPaidJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int, array{product_id: string|int, variant_id: string|int|null}>  $lineItems
     */
    public function __construct(
        public Shop $shop,
        public Order $order,
        public array $lineItems,
    ) {}

    public function handle(DeliveryService $deliveryService): void
    {
        $tokens = $deliveryService->createAccessForOrder($this->shop, $this->order, $this->lineItems);

        if ($tokens->isEmpty()) {
            return; // Order contains no mapped digital products.
        }

        SendDigitalDeliveryEmailJob::dispatch($this->shop, $this->order, $tokens);
    }
}
