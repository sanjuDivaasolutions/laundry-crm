<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasAdvancedFilter;
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    public $table = 'roles';

    protected $orderable = [
        'id',
        'title',
    ];

    protected $filterable = [
        'id',
        'title',
        'permissions.title',
    ];

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
