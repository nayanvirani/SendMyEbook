<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DownloadToken;
use App\Models\File;
use App\Services\FileStorage\FileStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated customer download page. Access is controlled
 * entirely by the unguessable token in the URL plus the rule checks on
 * DownloadToken — never by a customer account or session.
 */
class DownloadAccessController extends Controller
{
    public function __construct(private readonly FileStorageService $storage) {}

    public function show(string $token): View
    {
        $downloadToken = DownloadToken::query()
            ->with(['digitalProduct.files', 'order'])
            ->where('token', $token)
            ->first();

        if (! $downloadToken) {
            return view('customer.download-denied', ['reason' => 'invalid']);
        }

        if (! $downloadToken->isRedeemable()) {
            return view('customer.download-denied', ['reason' => $downloadToken->status]);
        }

        return view('customer.download-show', [
            'downloadToken' => $downloadToken,
            'files' => $downloadToken->digitalProduct->files,
        ]);
    }

    public function download(Request $request, string $token, File $file): RedirectResponse
    {
        $downloadToken = DownloadToken::query()->where('token', $token)->firstOrFail();

        abort_unless($downloadToken->isRedeemable(), 403, 'This download link is no longer valid.');
        abort_unless($file->digital_product_id === $downloadToken->digital_product_id, 403, 'File does not belong to this download.');
        abort_unless($file->status === 'ready', 409, 'File is not ready yet.');

        $downloadToken->recordDownload($file, $request->ip(), $request->userAgent());

        $url = $this->storage->presignedDownloadUrl($file->storage_key, $file->original_filename);

        return redirect()->away($url);
    }
}
