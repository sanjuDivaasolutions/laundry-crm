<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DeliverySchedule extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'customer_id',
        'type',
        'scheduled_date',
        'scheduled_time',
        'address',
        'notes',
        'assigned_to_employee_id',
        'status',
        'completed_at',
    ];

    protected $orderable = [
        'id',
        'type',
        'scheduled_date',
        'status',
    ];

    protected $filterable = [
        'id',
        'order_id',
        'customer_id',
        'type',
        'scheduled_date',
        'status',
        'assigned_to_employee_id',
    ];

    protected $searchable = [
        'id',
        'address',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeForDate(Builder $query, string $date): void
    {
        $query->whereDate('scheduled_date', $date);
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('scheduled_date', today());
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_id', 'customer_id', 'type', 'scheduled_date', 'status', 'assigned_to_employee_id'])
            ->logOnlyDirty()
            ->useLogName('delivery')
            ->setDescriptionForEvent(fn (string $eventName): string => "Delivery schedule #{$this->id} was {$eventName}");
    }
}
