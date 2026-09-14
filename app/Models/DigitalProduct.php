<?php

namespace App\Models;

use Database\Factories\DigitalProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProduct extends Model
{
    /** @use HasFactory<DigitalProductFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shopify_product_id',
        'shopify_product_title',
        'shopify_variant_id',
        'shopify_variant_title',
        'status',
        'max_downloads',
        'expiration_value',
        'expiration_unit',
        'revoke_on_refund',
    ];

    protected function casts(): array
    {
        return [
            'revoke_on_refund' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function downloadTokens(): HasMany
    {
        return $this->hasMany(DownloadToken::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
