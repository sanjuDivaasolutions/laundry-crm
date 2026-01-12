<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'cities';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'state.name',
        'state.country.name',
    ];

    protected $orderable = [
        'id',
        'name',
        'state.name',
        'active',
    ];

    protected $fillable = [
        'name',
        'state_id',
        'active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
