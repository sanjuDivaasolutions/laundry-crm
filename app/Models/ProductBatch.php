<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'product_batches';

    protected $casts = [
        'active' => 'boolean',
    
        'manufacturer_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    

    protected $filterable = [
        'id',
        'shelf.name',
        'name',
        'manufacturer_batch_no',
        'manufacturer_date',
        'expiry_date',
    ];

    protected $orderable = [
        'id',
        'shelf.name',
        'name',
        'manufacturer_batch_no',
        'manufacturer_date',
        'active',
        'expiry_date',
    ];

    protected $fillable = [
        'shelf_id',
        'name',
        'manufacturer_batch_no',
        'manufacturer_date',
        'active',
        'expiry_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function getManufacturerDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format(config('project.date_format')) : null;
    }

    public function setManufacturerDateAttribute($value)
    {
        $this->attributes['manufacturer_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getExpiryDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format(config('project.date_format')) : null;
    }

    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expiry_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }
}
