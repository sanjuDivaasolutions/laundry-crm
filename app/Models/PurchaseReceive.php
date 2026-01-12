<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceive extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'purchase_receives';

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $orderable = [
        'id',
        'code',
        'date',
        'remarks',
        'user.name',
    ];

    protected $filterable = [
        'id',
        'code',
        'date',
        'remarks',
        'user.name',
    ];

    protected $fillable = [
        'code',
        'date',
        'remarks',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d', $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
