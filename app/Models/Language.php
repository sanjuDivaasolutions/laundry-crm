<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'languages';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'locale',
    ];

    protected $orderable = [
        'id',
        'name',
        'locale',
        'active',
    ];

    protected $fillable = [
        'name',
        'locale',
        'active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }
}
