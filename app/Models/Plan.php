<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'handle',
        'price',
        'max_digital_products',
        'max_deliveries_per_month',
        'max_file_size_mb',
        'trial_days',
        'features',
        'marketing_bullets',
        'is_active',
        'sort_order',
    ];

    /**
     * Every feature slug actually checked somewhere in the app via
     * hasFeature() — the admin Plans form renders exactly this list as
     * checkboxes, so a new gate added in code needs a matching entry
     * here to become admin-editable instead of silently unreachable.
     */
    public const KNOWN_FEATURES = [
        'watermarking' => 'PDF watermarking',
        'custom_branding' => 'Custom branding (logo/colors)',
        'download_analytics' => 'Download analytics',
        'priority_support' => 'Priority support',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'marketing_bullets' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }
}
