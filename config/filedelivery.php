<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digital File Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk (see config/filesystems.php) used for merchant
    | digital-product files. The whole application reads this value instead
    | of hardcoding a disk name, so the storage provider can be swapped
    | without touching the digital-product, order or download systems.
    |
    */

    'disk' => env('DIGITAL_FILES_DISK', 'railway'),

    /*
    |--------------------------------------------------------------------------
    | Default Download Rules
    |--------------------------------------------------------------------------
    |
    | Applied when a merchant creates a digital product without explicitly
    | overriding these values.
    */

    'default_max_downloads' => env('DEFAULT_MAX_DOWNLOADS', 5),
    'default_expiration_days' => env('DEFAULT_EXPIRATION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Presigned URL Lifetimes
    |--------------------------------------------------------------------------
    */

    // How long a direct-upload URL (merchant -> bucket) stays valid.
    'upload_url_ttl_minutes' => env('UPLOAD_URL_TTL_MINUTES', 15),

    // How long a temporary download URL (bucket -> customer) stays valid.
    'download_url_ttl_minutes' => env('DOWNLOAD_URL_TTL_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */

    'max_upload_size_mb' => env('MAX_UPLOAD_SIZE_MB', 500),

    'allowed_mime_types' => [
        'application/pdf',
        'application/zip',
        'application/epub+zip',
        'application/x-mobipocket-ebook',
        'audio/mpeg',
        'audio/wav',
        'video/mp4',
        'video/quicktime',
        'image/png',
        'image/jpeg',
        'application/octet-stream',
    ],
];
