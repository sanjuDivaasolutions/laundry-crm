<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'countries';

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

    public function states()
    {
        return $this->hasMany(State::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
