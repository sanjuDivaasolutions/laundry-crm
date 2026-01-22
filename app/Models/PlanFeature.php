<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Feature assignment for a plan.
 *
 * @property int $id
 * @property int $plan_id
 * @property string $feature_code
 * @property bool $enabled
 * @property array|null $config
 */
class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_code',
        'enabled',
        'config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get the plan that owns this feature.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
