<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Announcement Model
 *
 * System-wide announcements for maintenance notices, new features, etc.
 * Can target all tenants, specific tenants, or specific plans.
 *
 * Design Decisions (from interview):
 * - Both in-app banner + optional email notification
 * - Full Dashboard for super-admin
 * - Target: all, specific_tenants, or specific_plans
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $type (info, warning, maintenance, feature, promotion)
 * @property string $priority (low, normal, high, urgent)
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 * @property bool $is_dismissible
 * @property bool $send_email
 * @property string $email_target (admin_only, all_users)
 * @property string $target (all, specific_tenants, specific_plans)
 * @property array|null $target_ids
 * @property string|null $action_url
 * @property string|null $action_text
 * @property bool $is_active
 * @property int|null $created_by
 */
class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'starts_at',
        'ends_at',
        'is_dismissible',
        'send_email',
        'email_target',
        'target',
        'target_ids',
        'action_url',
        'action_text',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_dismissible' => 'boolean',
        'send_email' => 'boolean',
        'is_active' => 'boolean',
        'target_ids' => 'array',
    ];

    /**
     * Type options with display info.
     */
    public const TYPES = [
        'info' => ['label' => 'Information', 'color' => 'blue', 'icon' => 'info-circle'],
        'warning' => ['label' => 'Warning', 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
        'maintenance' => ['label' => 'Maintenance', 'color' => 'orange', 'icon' => 'tools'],
        'feature' => ['label' => 'New Feature', 'color' => 'green', 'icon' => 'star'],
        'promotion' => ['label' => 'Promotion', 'color' => 'purple', 'icon' => 'gift'],
    ];

    /**
     * Priority options.
     */
    public const PRIORITIES = [
        'low' => 1,
        'normal' => 2,
        'high' => 3,
        'urgent' => 4,
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * User who created the announcement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users who have dismissed this announcement.
     */
    public function dismissedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_dismissals')
            ->withPivot('dismissed_at');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope to active announcements.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to currently visible announcements (within date range).
     */
    public function scopeVisible(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope to announcements for a specific tenant.
     */
    public function scopeForTenant(Builder $query, Tenant $tenant): Builder
    {
        return $query->where(function ($q) use ($tenant) {
            // All tenants
            $q->where('target', 'all')
                // Specific tenants - check if tenant ID is in target_ids
                ->orWhere(function ($q) use ($tenant) {
                    $q->where('target', 'specific_tenants')
                        ->whereJsonContains('target_ids', $tenant->id);
                })
                // Specific plans - check if tenant's plan is in target_ids
                ->orWhere(function ($q) use ($tenant) {
                    $planCode = $tenant->getCurrentPlanCode();
                    if ($planCode) {
                        $q->where('target', 'specific_plans')
                            ->whereJsonContains('target_ids', $planCode);
                    }
                });
        });
    }

    /**
     * Scope to announcements not dismissed by a user.
     */
    public function scopeNotDismissedBy(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('dismissedBy', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /**
     * Scope ordered by priority and start date.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderBy('starts_at', 'desc');
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Check if announcement is currently visible.
     */
    public function isVisible(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if announcement applies to a tenant.
     */
    public function appliesToTenant(Tenant $tenant): bool
    {
        if ($this->target === 'all') {
            return true;
        }

        if ($this->target === 'specific_tenants') {
            return in_array($tenant->id, $this->target_ids ?? []);
        }

        if ($this->target === 'specific_plans') {
            $planCode = $tenant->getCurrentPlanCode();
            return $planCode && in_array($planCode, $this->target_ids ?? []);
        }

        return false;
    }

    /**
     * Dismiss announcement for a user.
     */
    public function dismissFor(User $user): void
    {
        if (!$this->is_dismissible) {
            return;
        }

        $this->dismissedBy()->attach($user->id, [
            'dismissed_at' => now(),
        ]);
    }

    /**
     * Check if announcement was dismissed by user.
     */
    public function isDismissedBy(User $user): bool
    {
        return $this->dismissedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Get display configuration for the type.
     */
    public function getTypeConfig(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['info'];
    }

    /**
     * Get priority weight for sorting.
     */
    public function getPriorityWeight(): int
    {
        return self::PRIORITIES[$this->priority] ?? 2;
    }

    // =========================================================================
    // STATIC METHODS
    // =========================================================================

    /**
     * Get visible announcements for a tenant and user.
     */
    public static function getForTenantUser(Tenant $tenant, User $user): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->visible()
            ->forTenant($tenant)
            ->notDismissedBy($user)
            ->ordered()
            ->get();
    }

    /**
     * Get all active announcements (for admin).
     */
    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get announcements that need email sending.
     */
    public static function getPendingEmails(): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where('send_email', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->whereDoesntHave('emailsSent') // Would need another table to track
            ->get();
    }
}
