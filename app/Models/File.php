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
}
