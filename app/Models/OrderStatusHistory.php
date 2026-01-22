<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory;

    protected $table = 'order_status_history';

    public $timestamps = false; // We use changed_at as our primary timestamp

    protected $fillable = [
        'tenant_id',
        'order_id',
        'status_type',
        'old_status_id',
        'new_status_id',
        'changed_by_employee_id',
        'remarks',
        'changed_at',
    ];

    protected $casts = [
        'status_type' => \App\Enums\OrderStatusTypeEnum::class,
        'changed_at' => 'datetime',
        'old_status_id' => 'integer',
        'new_status_id' => 'integer',
        'changed_by_employee_id' => 'integer',
    ];

    protected $orderable = [
        'id',
        'status_type',
        'changed_at',
    ];

    protected $filterable = [
        'id',
        'order_id',
        'status_type',
        'changed_by_employee_id',
    ];

    protected $searchable = [
        'id',
        'remarks',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
