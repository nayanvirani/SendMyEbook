<?php

namespace App\Jobs;

use App\Mail\DigitalDeliveryMail;
use App\Models\DownloadToken;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\Shop;
use App\Services\Mail\ShopMailerResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Throwable;

class SendDigitalDeliveryEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @param  Collection<int, DownloadToken>  $downloadTokens
     */
    public function __construct(
        public Shop $shop,
        public Order $order,
        public Collection $downloadTokens,
    ) {}

    public function handle(ShopMailerResolver $resolver): void
    {
        if (! $this->order->customer_email) {
            return;
        }

        $log = EmailLog::create([
            'shop_id' => $this->shop->id,
            'order_id' => $this->order->id,
            'recipient_email' => $this->order->customer_email,
            'subject' => "Your download from {$this->shop->shop_name}",
            'status' => 'queued',
        ]);

        $resolved = $resolver->resolve($this->shop);

        if (! $resolved['enabled']) {
            $log->update(['status' => 'skipped', 'error_message' => 'Email sending is turned off in Settings.']);

            return;
        }

        $limit = $this->shop->currentPlan()?->max_deliveries_per_month;

        if ($limit !== null) {
            // Count deliveries already sent/attempted this month, not
            // including the row just created above for this one.
            $sentThisMonth = EmailLog::query()
                ->where('shop_id', $this->shop->id)
                ->where('id', '!=', $log->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->whereIn('status', ['sent', 'queued'])
                ->count();

            if ($sentThisMonth >= $limit) {
                $log->update([
                    'status' => 'skipped',
                    'error_message' => "Monthly email delivery limit ({$limit}) reached on the Free plan. Upgrade to Pro for unlimited deliveries.",
                ]);

                return;
            }
        }

        try {
            $resolved['mailer']->to($this->order->customer_email)->send(new DigitalDeliveryMail(
                $this->shop,
                $this->order,
                $this->downloadTokens,
                $resolved['fromAddress'],
                $resolved['fromName'],
            ));

            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            throw $e;
        }
    }
}
