<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'email_from_name',
        'support_email',
        'default_max_downloads',
        'default_expiration_days',
        'logo_url',
        'brand_color',
        'mail_enabled',
        'mail_provider',
        'mail_from_address',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'resend_api_key',
    ];

    protected function casts(): array
    {
        return [
            'mail_enabled' => 'boolean',
            'smtp_password' => 'encrypted',
            'resend_api_key' => 'encrypted',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
