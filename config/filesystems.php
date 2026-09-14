<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Private, S3-compatible object storage for merchant-uploaded digital
        // files. Currently backed by a Railway Storage Bucket, but nothing in
        // the application talks to this disk name directly except the
        // FileStorage service (see app/Services/FileStorage) — swapping to a
        // different S3-compatible provider later only requires changing the
        // env values below, per the storage abstraction requirement in the
        // MVP scope document.
        'railway' => [
            // "s3" here names the wire protocol Railway Buckets speak
            // (S3-compatible), not Amazon — every credential and endpoint
            // below points at Railway, never at AWS.
            'driver' => 's3',
            'key' => env('RAILWAY_BUCKET_ACCESS_KEY_ID'),
            'secret' => env('RAILWAY_BUCKET_SECRET_ACCESS_KEY'),
            'region' => env('RAILWAY_BUCKET_REGION', 'auto'),
            'bucket' => env('RAILWAY_BUCKET_NAME'),
            'endpoint' => env('RAILWAY_BUCKET_ENDPOINT'),
            'use_path_style_endpoint' => env('RAILWAY_BUCKET_USE_PATH_STYLE', false),
            'visibility' => 'private',
            'throw' => true,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
