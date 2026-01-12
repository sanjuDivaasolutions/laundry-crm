<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCategory extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'parent_categories';

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
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
