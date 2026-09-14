<?php

namespace App\Models;

use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_domain',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'scope',
        'shop_name',
        'email',
        'plan_display_name',
        'is_active',
        'installed_at',
        'uninstalled_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'access_token_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'installed_at' => 'datetime',
            'uninstalled_at' => 'datetime',
        ];
    }

    /**
     * Whether the stored access token is expired or close enough to
     * expiring that it should be refreshed before use.
     */
    public function accessTokenNeedsRefresh(): bool
    {
        return $this->access_token_expires_at !== null
            && $this->access_token_expires_at->subMinutes(5)->isPast();
    }

    public function digitalProducts(): HasMany
    {
        return $this->hasMany(DigitalProduct::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }
}
