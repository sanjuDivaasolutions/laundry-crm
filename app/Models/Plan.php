<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuotaPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SaaS subscription plan model.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string|null $stripe_product_id
 * @property string|null $stripe_price_id
 * @property string|null $stripe_yearly_price_id
 * @property int $price_monthly
 * @property int $price_yearly
 * @property string $currency
 * @property int $trial_days
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property array|null $metadata
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_yearly_price_id',
        'price_monthly',
        'price_yearly',
        'currency',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the plan's features.
     */
    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * Get the plan's quotas.
     */
    public function quotas(): HasMany
    {
        return $this->hasMany(PlanQuota::class);
    }

    /**
     * Scope to only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the Stripe price ID for billing period.
     */
    public function getStripePriceId(string $period = 'monthly'): ?string
    {
        return match ($period) {
            'yearly', 'annual' => $this->stripe_yearly_price_id ?? $this->stripe_price_id,
            default => $this->stripe_price_id,
        };
    }

    /**
     * Get price for billing period in cents.
     */
    public function getPrice(string $period = 'monthly'): int
    {
        return match ($period) {
            'yearly', 'annual' => $this->price_yearly,
            default => $this->price_monthly,
        };
    }

    /**
     * Get formatted price for display.
     */
    public function getFormattedPrice(string $period = 'monthly'): string
    {
        $price = $this->getPrice($period) / 100;
        $symbol = match ($this->currency) {
            'usd' => '$',
            'eur' => '€',
            'gbp' => '£',
            default => strtoupper($this->currency) . ' ',
        };

        return $symbol . number_format($price, 2);
    }

    /**
     * Check if plan has a specific feature enabled.
     */
    public function hasFeature(string $featureCode): bool
    {
        return $this->features()
            ->where('feature_code', $featureCode)
            ->where('enabled', true)
            ->exists();
    }

    /**
     * Get all enabled feature codes.
     */
    public function getEnabledFeatures(): array
    {
        return $this->features()
            ->where('enabled', true)
            ->pluck('feature_code')
            ->toArray();
    }

    /**
     * Get quota limit for a specific code.
     */
    public function getQuotaLimit(string $quotaCode): int
    {
        $quota = $this->quotas()
            ->where('quota_code', $quotaCode)
            ->first();

        return $quota?->limit_value ?? 0;
    }

    /**
     * Get all quotas as array.
     */
    public function getQuotasArray(): array
    {
        return $this->quotas()
            ->get()
            ->mapWithKeys(fn ($quota) => [
                $quota->quota_code => [
                    'limit' => $quota->limit_value,
                    'period' => $quota->period,
                ],
            ])
            ->toArray();
    }

    /**
     * Provision features and quotas for a tenant.
     */
    public function provisionForTenant(Tenant $tenant): void
    {
        // Enable features
        foreach ($this->features()->where('enabled', true)->get() as $planFeature) {
            $tenant->enableFeature($planFeature->feature_code);
        }

        // Set quotas
        foreach ($this->quotas as $planQuota) {
            $period = QuotaPeriod::tryFrom($planQuota->period) ?? QuotaPeriod::MONTHLY;
            $tenant->setQuota($planQuota->quota_code, $planQuota->limit_value, $period);
        }
    }

    /**
     * Get plan data for API response.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'price_monthly_formatted' => $this->getFormattedPrice('monthly'),
            'price_yearly_formatted' => $this->getFormattedPrice('yearly'),
            'currency' => $this->currency,
            'trial_days' => $this->trial_days,
            'is_featured' => $this->is_featured,
            'features' => $this->getEnabledFeatures(),
            'quotas' => $this->getQuotasArray(),
        ];
    }
}
