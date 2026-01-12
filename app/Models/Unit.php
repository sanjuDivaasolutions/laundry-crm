<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\ModelCache;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, ModelCache;

    public $table = 'units';

    protected $filterable = [
        'id',
        'name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $orderable = [
        'id',
        'name',
        'active',
    ];

    protected $fillable = [
        'name',
        'active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
