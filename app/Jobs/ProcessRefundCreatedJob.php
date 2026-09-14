<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DigitalDelivery\DeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRefundCreatedJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Order $order) {}

    public function handle(DeliveryService $deliveryService): void
    {
        $this->order->update(['is_refunded' => true, 'financial_status' => 'refunded']);

        $deliveryService->revokeAccessForOrder($this->order);
    }
}
