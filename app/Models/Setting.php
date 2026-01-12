<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'settings';

    protected $casts = [
        'serialized' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'group',
        'key',
        'value',
    ];

    protected $orderable = [
        'id',
        'group',
        'key',
        'value',
        'serialized',
    ];

    protected $fillable = [
        'group',
        'key',
        'value',
        'serialized',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
