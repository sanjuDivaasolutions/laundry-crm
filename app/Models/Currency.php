<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\ModelCache;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, ModelCache;

    public $table = 'currencies';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'code',
        'name',
        'symbol',
        'rate',
    ];

    protected $orderable = [
        'id',
        'code',
        'name',
        'symbol',
        'rate',
        'active',
    ];

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate',
        'active',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
