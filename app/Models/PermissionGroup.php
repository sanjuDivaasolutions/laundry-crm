<?php

namespace App\Models;

use App\Traits\Searchable;
use \DateTimeInterface;
use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasAdvancedFilter;
    use HasFactory;
    use Searchable;

    public $table = 'permission_groups';

    protected $orderable = [
        'id',
        'name',
    ];

    protected $filterable = [
        'id',
        'name',
    ];

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
