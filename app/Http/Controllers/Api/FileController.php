<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileResource;
use App\Models\DigitalProduct;
use App\Models\File;
use App\Models\Shop;
use App\Services\FileStorage\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Direct-upload flow: the browser never sends the file bytes to Laravel.
 * 1. POST here to get a presigned PUT URL and a pending File record.
 * 2. Browser PUTs the file straight to the bucket using that URL.
 * 3. PATCH .../complete to mark the File ready once the upload succeeds.
 */
class FileController extends Controller
{
    public function __construct(private readonly FileStorageService $storage) {}

    public function store(Request $request, DigitalProduct $digitalProduct): JsonResponse
    {
        abort_unless($digitalProduct->shop_id === $this->shop($request)->id, 403);

        $data = $request->validate([
            'original_filename' => ['required', 'string', 'max:255'],
            'mime_type' => ['required', 'string', 'in:'.implode(',', config('filedelivery.allowed_mime_types'))],
            'size_bytes' => ['required', 'integer', 'min:1', 'max:'.(config('filedelivery.max_upload_size_mb') * 1024 * 1024)],
        ]);

        $storageKey = $this->storage->generateStorageKey($data['original_filename']);

        $file = $digitalProduct->files()->create([
            'original_filename' => $data['original_filename'],
            'storage_disk' => $this->storage->disk(),
            'storage_key' => $storageKey,
            'mime_type' => $data['mime_type'],
            'size_bytes' => $data['size_bytes'],
            'status' => 'pending',
        ]);

        return response()->json([
            'file' => new FileResource($file),
            'upload_url' => $this->storage->presignedUploadUrl($storageKey, $data['mime_type']),
        ], 201);
    }

    public function complete(Request $request, File $file): JsonResponse
    {
        $this->authorizeShop($request, $file);

        abort_unless($this->storage->exists($file->storage_key), 422, 'Upload did not complete.');

        $file->update(['status' => 'ready', 'size_bytes' => $this->storage->size($file->storage_key)]);

        return response()->json(new FileResource($file));
    }

    public function destroy(Request $request, File $file): JsonResponse
    {
        $this->authorizeShop($request, $file);

        $this->storage->delete($file->storage_key);
        $file->delete();

        return response()->json(status: 204);
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }

    private function authorizeShop(Request $request, File $file): void
    {
        abort_unless($file->digitalProduct->shop_id === $this->shop($request)->id, 403);
    }
}
