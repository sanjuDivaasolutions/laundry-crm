<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\ModelCache;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasAdvancedFilter, SoftDeletes, HasFactory, ModelCache;

    public $table = 'permissions';

    protected $orderable = [
        'id',
        'title',
        'permission_group.name',
    ];

    protected $filterable = [
        'id',
        'title',
        'permission_group_id',
        'permission_group.name',
    ];

    protected $fillable = [
        'title',
        'permission_group_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
