<?php

namespace App\Mail;

use App\Models\DownloadToken;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DigitalDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, DownloadToken>  $downloadTokens
     */
    public function __construct(
        public Shop $shop,
        public Order $order,
        public Collection $downloadTokens,
        public string $fromAddress,
        public string $fromName,
    ) {}

    public function build(): self
    {
        $setting = $this->shop->setting;

        return $this->from($this->fromAddress, $this->fromName)
            ->subject("Your download from {$this->shop->shop_name}")
            ->view('emails.digital-delivery', [
                'shop' => $this->shop,
                'order' => $this->order,
                'logoUrl' => $setting?->logo_url,
                'brandColor' => $setting?->brand_color ?: '#008060',
                'supportEmail' => $setting?->support_email,
                'downloadLinks' => $this->downloadTokens->map(fn ($token) => [
                    'productTitle' => $token->digitalProduct->shopify_product_title,
                    'url' => route('customer.downloads.show', ['token' => $token->token]),
                    'maxDownloads' => $token->max_downloads,
                    'expiresAt' => $token->expires_at,
                    'licenseKey' => $token->license_key,
                ]),
            ]);
    }
}
