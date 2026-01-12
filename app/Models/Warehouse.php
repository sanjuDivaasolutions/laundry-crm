<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Services\CompanyService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'warehouses';

    protected $orderable = [
        'id',
        'name',
        'code',
        'address_1',
        'address_2',
        'city.name',
        'state.name',
        'country.name',
        'postal_code',
        'email',
        'phone',
    ];

    protected $filterable = [
        'id',
        'name',
        'code',
        'address_1',
        'address_2',
        'city.name',
        'state.name',
        'country.name',
        'postal_code',
        'email',
        'phone',
    ];

    protected $fillable = [
        'name',
        'code',
        'address_1',
        'address_2',
        'city_id',
        'state_id',
        'country_id',
        'postal_code',
        'email',
        'phone',
        'company_id',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function scopeCompany($q): void
    {
        $warehouseIds = CompanyService::getAccessibleWarehouseIds();
        if (!empty($warehouseIds)) {
            $q->whereIn($this->getTable() . '.id', $warehouseIds);
            return;
        }

        $company = CompanyService::getCompanyById();
        if (!$company) {
            return;
        }

        $table = $this->getTable();
        $code = $company->code;
        $name = $company->name;

        $q->where(function ($query) use ($table, $code, $name) {
            $hasCondition = false;

            if ($code) {
                $query->where(function ($nested) use ($table, $code) {
                    $nested->where($table . '.name', 'like', '%' . $code . '%')
                        ->orWhere($table . '.code', 'like', '%' . $code . '%');
                });
                $hasCondition = true;
            }

            if ($name) {
                if ($hasCondition) {
                    $query->orWhere($table . '.name', 'like', '%' . $name . '%');
                } else {
                    $query->where($table . '.name', 'like', '%' . $name . '%');
                }
            }
        });
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
