<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'address',
        'commission_rate',
        'commission_type',
        'fixed_commission',
        'active',
        'user_id',
        'notes',
    ];

    protected $orderable = [
        'id',
        'code',
        'name',
        'email',
        'commission_rate',
        'commission_type',
        'active',
    ];

    protected $filterable = [
        'id',
        'code',
        'name',
        'email',
        'commission_rate',
        'commission_type',
        'active',
        'user.name',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'fixed_commission' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AgentCommission::class);
    }

    public function pendingCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'pending');
    }

    public function approvedCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'approved');
    }

    public function paidCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'paid');
    }

    public function calculateCommission(float $amount): float
    {
        if ($this->commission_type === 'fixed') {
            return $this->fixed_commission;
        }

        return ($amount * $this->commission_rate) / 100;
    }

    public function getTotalPendingCommissionsAttribute(): float
    {
        return $this->pendingCommissions()->sum('commission_amount');
    }

    public function getTotalApprovedCommissionsAttribute(): float
    {
        return $this->approvedCommissions()->sum('commission_amount');
    }

    public function getTotalPaidCommissionsAttribute(): float
    {
        return $this->paidCommissions()->sum('commission_amount');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCommissionType($query, string $type)
    {
        return $query->where('commission_type', $type);
    }
}