<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'estimates';

    protected $appends = [
        'type_label',
    ];

    protected $casts = [
        'date' => 'datetime',
        'estimated_shipment_date' => 'datetime',
    ];

    public const TYPE_SELECT = [
        [
            'label' => 'Pickup',
            'value' => 'p',
        ],
        [
            'label' => 'Delivery',
            'value' => 'd',
        ],
    ];

    protected $orderable = [
        'id',
        'quo_number',
        'reference_no',
        'warehouse.name',
        'type',
        'buyer.code',
        'date',
        'estimated_shipment_date',
        'payment_term.name',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
    ];

    protected $filterable = [
        'id',
        'quo_number',
        'reference_no',
        'warehouse.name',
        'type',
        'buyer.code',
        'date',
        'estimated_shipment_date',
        'payment_term.name',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
    ];

    protected $fillable = [
        'quo_number',
        'reference_no',
        'warehouse_id',
        'type',
        'buyer_id',
        'date',
        'estimated_shipment_date',
        'payment_term_id',
        'remarks',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getTypeLabelAttribute()
    {
        return collect(static::TYPE_SELECT)->firstWhere('value', $this->type)['label'] ?? '';
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getEstimatedShipmentDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format(config('project.date_format')) : null;
    }

    public function setEstimatedShipmentDateAttribute($value)
    {
        $this->attributes['estimated_shipment_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }
}
