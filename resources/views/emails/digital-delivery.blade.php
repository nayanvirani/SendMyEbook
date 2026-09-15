@component('mail::message')
# Thanks for your order, {{ $order->customer_name ?? 'there' }}!

Your purchase from **{{ $shop->shop_name }}** (order {{ $order->shopify_order_number }}) is ready to download.

@foreach ($downloadLinks as $link)
@component('mail::button', ['url' => $link['url']])
Download {{ $link['productTitle'] }}
@endcomponent

@if ($link['maxDownloads'])
You can download this up to {{ $link['maxDownloads'] }} times.
@endif
@if ($link['expiresAt'])
This link expires on {{ $link['expiresAt']->format('M j, Y g:i A') }}.
@endif
@if ($link['licenseKey'])
**License key:** {{ $link['licenseKey'] }}
@endif

@endforeach

If you have any trouble accessing your files, just reply to this email.

Thanks,<br>
{{ $shop->shop_name }}
@endcomponent
