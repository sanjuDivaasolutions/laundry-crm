<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'states';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'country.name',
    ];

    protected $orderable = [
        'id',
        'name',
        'active',
        'country.name',
    ];

    protected $fillable = [
        'name',
        'active',
        'country_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
