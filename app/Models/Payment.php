<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, LogsActivity;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'payment_number',
        'order_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
        'received_by_employee_id',
        'created_at',
    ];

    protected $orderable = [
        'id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
    ];

    protected $filterable = [
        'id',
        'payment_number',
        'order_id',
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'received_by_employee_id',
    ];

    protected $searchable = [
        'id',
        'payment_number',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethodEnum::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['payment_number', 'order_id', 'customer_id', 'amount', 'payment_method', 'transaction_reference'])
            ->logOnlyDirty()
            ->useLogName('payment')
            ->setDescriptionForEvent(fn (string $eventName): string => "Payment {$this->payment_number} was {$eventName}");
    }
}
