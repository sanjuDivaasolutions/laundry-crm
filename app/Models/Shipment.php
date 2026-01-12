<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'shipments';

    protected $casts = [
        'shipment_date' => 'datetime',
        'delivery_date' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'package.code',
        'shipment_date',
        'remarks',
        'code',
        'delivery_date',
        'shipment_mode.name',
    ];

    protected $filterable = [
        'id',
        'package.code',
        'shipment_date',
        'remarks',
        'code',
        'delivery_date',
        'shipment_mode.name',
    ];

    protected $fillable = [
        'package_id',
        'shipment_date',
        'remarks',
        'code',
        'delivery_date',
        'shipment_mode_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function getShipmentDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setShipmentDateAttribute($value)
    {
        $this->attributes['shipment_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getDeliveryDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDeliveryDateAttribute($value)
    {
        $this->attributes['delivery_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function shipmentMode()
    {
        return $this->belongsTo(ShipmentMode::class);
    }
}
