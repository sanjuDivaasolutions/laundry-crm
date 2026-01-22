<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Quota definition for a plan.
 *
 * @property int $id
 * @property int $plan_id
 * @property string $quota_code
 * @property int $limit_value
 * @property string $period
 */
class PlanQuota extends Model
{
    protected $fillable = [
        'plan_id',
        'quota_code',
        'limit_value',
        'period',
    ];

    protected $casts = [
        'limit_value' => 'integer',
    ];

    /**
     * Get the plan that owns this quota.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if this quota is unlimited.
     */
    public function isUnlimited(): bool
    {
        return $this->limit_value === -1;
    }
}
