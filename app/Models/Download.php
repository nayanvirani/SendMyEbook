<?php

namespace App\Models;

use Database\Factories\DownloadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    /** @use HasFactory<DownloadFactory> */
    use HasFactory;

    protected $fillable = [
        'download_token_id',
        'file_id',
        'ip_address',
        'user_agent',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function downloadToken(): BelongsTo
    {
        return $this->belongsTo(DownloadToken::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
