<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantTaxRate extends Model
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'is_compound',
        'priority',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_compound' => 'boolean',
        'priority' => 'integer',
    ];

    protected $orderable = [
        'id',
        'name',
        'rate',
        'priority',
    ];

    protected $filterable = [
        'id',
        'name',
        'rate',
        'is_compound',
    ];

    protected $searchable = [
        'id',
        'name',
    ];
}
