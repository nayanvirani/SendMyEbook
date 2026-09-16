<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'shopify_order_id',
        'shopify_order_number',
        'customer_email',
        'customer_name',
        'financial_status',
        'total_price',
        'currency',
        'is_refunded',
        'is_cancelled',
        'is_closed',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'is_refunded' => 'boolean',
            'is_cancelled' => 'boolean',
            'is_closed' => 'boolean',
            'raw_payload' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function downloadTokens(): HasMany
    {
        return $this->hasMany(DownloadToken::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }
}
