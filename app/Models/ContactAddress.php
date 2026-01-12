<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactAddress extends Model
{
    use HasAdvancedFilter, HasFactory,Searchable;

    public $table = 'contact_addresses';

    protected $orderable = [
        'id',
        'name',
        'address_1',
        'address_2',
        'country.name',
        'state.name',
        'city.name',
        'postal_code',
        'phone',
    ];

    protected $filterable = [
        'id',
        'name',
        'address_1',
        'address_2',
        'country.name',
        'state.name',
        'city.name',
        'postal_code',
        'phone',
    ];

    protected $fillable = [
        'name',
        'address_1',
        'address_2',
        'country_id',
        'state_id',
        'city_id',
        'postal_code',
        'phone',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
