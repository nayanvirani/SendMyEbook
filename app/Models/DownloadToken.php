<?php

namespace App\Models;

use Database\Factories\DownloadTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DownloadToken extends Model
{
    /** @use HasFactory<DownloadTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'digital_product_id',
        'token',
        'license_key',
        'max_downloads',
        'download_count',
        'expires_at',
        'status',
        'revoked_reason',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function digitalProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedLimit(): bool
    {
        return $this->max_downloads !== null && $this->download_count >= $this->max_downloads;
    }

    /**
     * Re-evaluate and persist the token's status against its current rules.
     * Does not override an already-revoked token.
     */
    public function refreshStatus(): self
    {
        if ($this->status === 'revoked') {
            return $this;
        }

        $status = match (true) {
            $this->isExpired() => 'expired',
            $this->hasReachedLimit() => 'limit_reached',
            ! $this->digitalProduct->isActive() => $this->status, // existing access stays auditable
            default => 'active',
        };

        if ($status !== $this->status) {
            $this->status = $status;
            $this->save();
        }

        return $this;
    }

    public function isRedeemable(): bool
    {
        $this->refreshStatus();

        return $this->status === 'active';
    }

    public function revoke(string $reason): self
    {
        $this->status = 'revoked';
        $this->revoked_reason = $reason;
        $this->save();

        return $this;
    }

    public function recordDownload(File $file, ?string $ip, ?string $userAgent): Download
    {
        $download = $this->downloads()->create([
            'file_id' => $file->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'downloaded_at' => Carbon::now(),
        ]);

        $this->increment('download_count');
        $this->last_downloaded_at = Carbon::now();
        $this->save();
        $this->refreshStatus();

        return $download;
    }
}
