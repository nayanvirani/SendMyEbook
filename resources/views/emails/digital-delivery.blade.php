<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your download from {{ $shop->shop_name }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f7f9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f7f9;">
<tr>
<td align="center" style="padding:40px 16px;">

<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px; width:100%; background-color:#ffffff; border-radius:14px;">
<tr>
<td style="padding:40px 40px 8px 40px;">

@if ($logoUrl)
<img src="{{ $logoUrl }}" alt="{{ $shop->shop_name }}" height="36" style="max-height:36px; margin-bottom:20px; display:block;">
@endif

<div style="display:inline-block; font-size:11px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:{{ $brandColor }}; background-color:#f6f7f9; padding:5px 12px; border-radius:999px; margin-bottom:16px;">
Order confirmed
</div>

<h1 style="font-size:21px; line-height:1.35; margin:0 0 8px 0; color:#1f2328;">
Thanks for your order{{ $order->customer_name ? ', '.explode(' ', $order->customer_name)[0] : '' }}!
</h1>
<p style="font-size:14px; line-height:1.6; color:#6b7280; margin:0 0 28px 0;">
Your purchase from <strong style="color:#1f2328;">{{ $shop->shop_name }}</strong> — order #{{ $order->shopify_order_number }} — is ready to download.
</p>

</td>
</tr>

@foreach ($downloadLinks as $link)
<tr>
<td style="padding:0 40px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #edeef1; border-radius:12px; margin-bottom:16px;">
<tr>
<td style="padding:20px 22px;">
<p style="font-size:15px; font-weight:600; color:#1f2328; margin:0 0 14px 0;">{{ $link['productTitle'] }}</p>

<table role="presentation" cellpadding="0" cellspacing="0">
<tr>
<td style="border-radius:8px; background-color:{{ $brandColor }};">
<a href="{{ $link['url'] }}" style="display:inline-block; padding:10px 20px; font-size:13px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:8px;">
Download files
</a>
</td>
</tr>
</table>

@if ($link['maxDownloads'] || $link['expiresAt'])
<p style="font-size:12.5px; color:#8a8f98; margin:14px 0 0 0;">
@if ($link['maxDownloads'])
Up to {{ $link['maxDownloads'] }} downloads.
@endif
@if ($link['expiresAt'])
Link expires {{ $link['expiresAt']->format('M j, Y g:i A') }}.
@endif
</p>
@endif

@if ($link['licenseKey'])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px; background-color:#f6f7f9; border-radius:8px;">
<tr>
<td style="padding:10px 14px;">
<span style="font-size:10.5px; font-weight:700; letter-spacing:.03em; text-transform:uppercase; color:#6b7280;">License key</span><br>
<code style="font-size:14px; font-weight:600; letter-spacing:.04em; color:#1f2328;">{{ $link['licenseKey'] }}</code>
</td>
</tr>
</table>
@endif

</td>
</tr>
</table>
</td>
</tr>
@endforeach

<tr>
<td style="padding:8px 40px 40px 40px;">
<p style="font-size:13px; line-height:1.6; color:#8a8f98; margin:20px 0 0 0; border-top:1px solid #edeef1; padding-top:20px;">
Need help with your download?
@if ($supportEmail)
Contact us at <a href="mailto:{{ $supportEmail }}" style="color:{{ $brandColor }}; text-decoration:none;">{{ $supportEmail }}</a>.
@else
Just reply to this email.
@endif
</p>
</td>
</tr>
</table>

<p style="font-size:12px; color:#9aa0a8; margin:20px 0 0 0;">{{ $shop->shop_name }} · Sent via SendMyEbook</p>

</td>
</tr>
</table>
</body>
</html>
