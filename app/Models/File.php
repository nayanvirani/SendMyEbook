<?php

namespace App\Models;

use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    protected $fillable = [
        'digital_product_id',
        'original_filename',
        'storage_disk',
        'storage_key',
        'mime_type',
        'size_bytes',
        'status',
    ];

    public function digitalProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Human-readable file size with a KB tier — dividing straight to MB
     * rounds every file under ~50KB down to "0.0 MB", which is exactly
     * the kind of file merchants often attach (small PDFs, presets).
     */
    public function formattedSize(): string
    {
        if (! $this->size_bytes) {
            return '';
        }

        $kb = $this->size_bytes / 1024;

        if ($kb < 1024) {
            return number_format($kb, 1).' KB';
        }

        return number_format($kb / 1024, 1).' MB';
    }
}
