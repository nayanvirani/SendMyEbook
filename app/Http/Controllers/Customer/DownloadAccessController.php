<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DownloadToken;
use App\Models\File;
use App\Models\Setting;
use App\Services\FileStorage\FileStorageService;
use App\Services\PdfWatermark\PdfWatermarker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Public, unauthenticated customer download page. Access is controlled
 * entirely by the unguessable token in the URL plus the rule checks on
 * DownloadToken — never by a customer account or session.
 */
class DownloadAccessController extends Controller
{
    public function __construct(
        private readonly FileStorageService $storage,
        private readonly PdfWatermarker $watermarker,
    ) {}

    public function show(string $token): View
    {
        $downloadToken = DownloadToken::query()
            ->with(['digitalProduct.files', 'order.shop.setting'])
            ->where('token', $token)
            ->first();

        if (! $downloadToken) {
            return view('customer.download-denied', ['reason' => 'invalid', ...$this->branding(null)]);
        }

        if (! $downloadToken->isRedeemable()) {
            return view('customer.download-denied', [
                'reason' => $downloadToken->status,
                ...$this->branding($downloadToken->order->shop->setting),
            ]);
        }

        return view('customer.download-show', [
            'downloadToken' => $downloadToken,
            'files' => $downloadToken->digitalProduct->files,
            ...$this->branding($downloadToken->order->shop->setting),
        ]);
    }

    /**
     * @return array{logoUrl: ?string, brandColor: string}
     */
    private function branding(?Setting $setting): array
    {
        return [
            'logoUrl' => $setting?->logo_url,
            'brandColor' => $setting?->brand_color ?: '#008060',
        ];
    }

    public function download(Request $request, string $token, File $file): RedirectResponse|BinaryFileResponse
    {
        $downloadToken = DownloadToken::query()->with(['digitalProduct', 'order'])->where('token', $token)->firstOrFail();

        abort_unless($downloadToken->isRedeemable(), 403, 'This download link is no longer valid.');
        abort_unless($file->digital_product_id === $downloadToken->digital_product_id, 403, 'File does not belong to this download.');
        abort_unless($file->status === 'ready', 409, 'File is not ready yet.');

        $downloadToken->recordDownload($file, $request->ip(), $request->userAgent());

        if ($file->mime_type === 'application/pdf' && $downloadToken->digitalProduct->watermark_pdfs) {
            return $this->downloadWatermarked($file, $downloadToken);
        }

        $url = $this->storage->presignedDownloadUrl($file->storage_key, $file->original_filename);

        return redirect()->away($url);
    }

    /**
     * Watermarking has to happen on this server (the bucket only ever
     * stores the merchant's original file), so this one path streams the
     * result instead of redirecting to a presigned bucket URL like every
     * other download.
     */
    private function downloadWatermarked(File $file, DownloadToken $downloadToken): BinaryFileResponse
    {
        $sourcePath = tempnam(sys_get_temp_dir(), 'smeb_src_');
        $outputPath = tempnam(sys_get_temp_dir(), 'smeb_wm_').'.pdf';

        file_put_contents($sourcePath, Storage::disk($this->storage->disk())->get($file->storage_key));

        $order = $downloadToken->order;
        $watermarkText = sprintf(
            'Licensed to %s — Order #%s — Do not distribute',
            $order->customer_email ?? $order->customer_name ?? 'customer',
            $order->shopify_order_number
        );

        try {
            $this->watermarker->watermark($sourcePath, $outputPath, $watermarkText);
        } finally {
            unlink($sourcePath);
        }

        return response()->download($outputPath, $file->original_filename)->deleteFileAfterSend();
    }
}
