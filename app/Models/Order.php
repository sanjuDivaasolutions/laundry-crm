<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ProcessingStatusEnum;
use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'customer_id',
        'order_date',
        'promised_date',
        'total_items',
        'subtotal',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'processing_status_id',
        'order_status_id',
        'actual_ready_date',
        'picked_up_at',
        'notes',
        'urgent',
        'hanger_number',
        'tax_rate',
        'tax_amount',
        'discount_type',
        'created_by_employee_id',
        'closed_at',
    ];

    protected $orderable = [
        'id',
        'order_number',
        'order_date',
        'promised_date',
        'total_amount',
        'balance_amount',
        'payment_status',
        'urgent',
        'created_at',
    ];

    protected $filterable = [
        'id',
        'order_number',
        'customer_id',
        'order_date',
        'promised_date',
        'payment_status',
        'processing_status_id',
        'order_status_id',
        'urgent',
        'created_by_employee_id',
    ];

    protected $searchable = [
        'id',
        'order_number',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'promised_date' => 'date',
        'actual_ready_date' => 'datetime',
        'picked_up_at' => 'datetime',
        'closed_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'urgent' => 'boolean',
        'payment_status' => PaymentStatusEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processingStatus(): BelongsTo
    {
        return $this->belongsTo(ProcessingStatus::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeUrgent(Builder $query): void
    {
        $query->where('urgent', true);
    }

    protected function isFullyPaid(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->balance_amount <= 0 && $this->payment_status === PaymentStatusEnum::Paid,
        );
    }

    public function isReadyForPickup(): bool
    {
        return $this->processingStatus?->status_name === ProcessingStatusEnum::Ready->value;
    }

    public function markAsPickedUp(): void
    {
        $deliveredStatus = ProcessingStatus::where('status_name', ProcessingStatusEnum::Delivered->value)->first();
        $closedStatus = OrderStatus::where('status_name', OrderStatusEnum::Closed->value)->first();

        $this->update([
            'processing_status_id' => $deliveredStatus?->id,
            'order_status_id' => $closedStatus?->id,
            'picked_up_at' => now(),
            'closed_at' => now(),
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'order_number', 'customer_id', 'order_date', 'promised_date',
                'total_amount', 'paid_amount', 'balance_amount', 'payment_status',
                'processing_status_id', 'order_status_id', 'urgent', 'hanger_number',
            ])
            ->logOnlyDirty()
            ->useLogName('order')
            ->setDescriptionForEvent(fn (string $eventName): string => "Order {$this->order_number} was {$eventName}");
    }
}
