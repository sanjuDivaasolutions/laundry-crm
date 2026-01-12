<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AgentCommission extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    protected $fillable = [
        'agent_id',
        'commissionable_type',
        'commissionable_id',
        'commission_amount',
        'commission_rate',
        'commission_type',
        'status',
        'commission_date',
        'paid_date',
        'notes',
        'approved_by',
        'paid_by',
    ];

    protected $orderable = [
        'id',
        'agent.name',
        'commission_amount',
        'commission_rate',
        'commission_type',
        'status',
        'commission_date',
        'paid_date',
    ];

    protected $filterable = [
        'id',
        'agent.name',
        'commissionable_type',
        'commission_amount',
        'commission_rate',
        'commission_type',
        'status',
        'commission_date',
        'paid_date',
        'approvedBy.name',
        'paidBy.name',
    ];

    protected $casts = [
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_date' => 'date',
        'paid_date' => 'date',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function commissionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function approve(?User $user = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->approved_by = $user?->id ?? auth()->id();
        $this->save();

        return true;
    }

    public function markAsPaid(?User $user = null): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'paid';
        $this->paid_date = now();
        $this->paid_by = $user?->id ?? auth()->id();
        $this->save();

        return true;
    }

    public function cancel(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }

        $this->status = 'cancelled';
        $this->save();

        return true;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForAgent($query, Agent|int $agent)
    {
        $agentId = $agent instanceof Agent ? $agent->id : $agent;
        return $query->where('agent_id', $agentId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('commission_date', [$startDate, $endDate]);
    }
}