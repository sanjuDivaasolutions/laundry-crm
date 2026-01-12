<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTerm extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'contract_terms';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'description',
        'sequence',
    ];

    protected $orderable = [
        'id',
        'name',
        'description',
        'sequence',
        'active',
    ];

    protected $fillable = [
        'name',
        'description',
        'sequence',
        'active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
