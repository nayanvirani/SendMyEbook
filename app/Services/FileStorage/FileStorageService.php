<?php

namespace App\Services\FileStorage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Storage abstraction for merchant digital-product files.
 *
 * Every part of the application that needs to store, presign, or delete a
 * digital file goes through this service instead of touching the
 * `filesystems.disks.railway` config directly. The MVP backs this with a
 * Railway Storage Bucket (S3-compatible); switching to another
 * S3-compatible provider later only means changing config/filesystems.php
 * and the RAILWAY_BUCKET_* env values — nothing here, and nothing in the
 * digital-product/order/download systems, needs to change.
 */
class FileStorageService
{
    public function disk(): string
    {
        return config('filedelivery.disk');
    }

    public function generateStorageKey(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $key = 'digital-files/'.Str::uuid()->toString();

        return $extension ? "{$key}.{$extension}" : $key;
    }

    /**
     * A presigned PUT URL the merchant's browser can upload directly to,
     * without the file ever passing through the Laravel app server.
     */
    public function presignedUploadUrl(string $storageKey, string $mimeType): string
    {
        $client = $this->s3Client();

        $command = $client->getCommand('PutObject', [
            'Bucket' => config("filesystems.disks.{$this->disk()}.bucket"),
            'Key' => $storageKey,
            'ContentType' => $mimeType,
        ]);

        $request = $client->createPresignedRequest(
            $command,
            '+'.config('filedelivery.upload_url_ttl_minutes').' minutes'
        );

        return (string) $request->getUri();
    }

    /**
     * A presigned, time-limited GET URL for a customer's browser to download
     * directly from the bucket. The bucket itself stays private.
     */
    public function presignedDownloadUrl(string $storageKey, string $downloadFilename): string
    {
        return Storage::disk($this->disk())->temporaryUrl(
            $storageKey,
            now()->addMinutes((int) config('filedelivery.download_url_ttl_minutes')),
            [
                'ResponseContentDisposition' => 'attachment; filename="'.$downloadFilename.'"',
            ]
        );
    }

    public function delete(string $storageKey): void
    {
        Storage::disk($this->disk())->delete($storageKey);
    }

    public function exists(string $storageKey): bool
    {
        return Storage::disk($this->disk())->exists($storageKey);
    }

    public function size(string $storageKey): int
    {
        return Storage::disk($this->disk())->size($storageKey);
    }

    private function s3Client(): S3Client
    {
        return Storage::disk($this->disk())->getClient();
    }
}
