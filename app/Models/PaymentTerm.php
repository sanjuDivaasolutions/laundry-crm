<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\ModelCache;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    use HasAdvancedFilter, HasFactory,Searchable,ModelCache;

    public $table = 'payment_terms';
    public $cacheKey = 'payment-terms';

    protected $orderable = [
        'id',
        'name',
        'days',
    ];

    protected $filterable = [
        'id',
        'name',
        'days',
    ];

    protected $fillable = [
        'name',
        'days',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
