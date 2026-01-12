<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable, CompanyScopeTrait;

    public $table = 'suppliers';

    protected $casts = [
        'active' => 'boolean',
        'is_agent' => 'boolean',
    ];
    protected $searchable = [
        'code',
        'display_name',
        'name',
    ];

    protected $filterable = [
        'id',
        'code',
        'display_name',
        'name',
        'payment_term.name',
        'billing_address.name',
        'shipping_address.name',
        'currency.code',
        'is_agent',
        'email',
        'phone',
        'remarks',
        'salesInvoicesAsAgent.date',
    ];

    protected $orderable = [
        'id',
        'code',
        'display_name',
        'name',
        'payment_term.name',
        'billing_address.name',
        'shipping_address.name',
        'active',
        'currency.code',
        'is_agent',
        'email',
        'phone',
        'remarks',
    ];

    protected $fillable = [
        'code',
        'display_name',
        'name',
        'company_id',
        'payment_term_id',
        'billing_address_id',
        'shipping_address_id',
        'active',
        'is_agent',
        'currency_id',
        'email',
        'phone',
        'remarks',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function billingAddress()
    {
        return $this->belongsTo(ContactAddress::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ContactAddress::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function salesInvoicesAsAgent()
    {
        return $this->hasMany(SalesInvoice::class, 'agent_id');
    }

    public function scopeAgents($query)
    {
        return $query->where('is_agent', true);
    }

    public function scopeSuppliersOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_agent')
                ->orWhere('is_agent', false);
        });
    }
}
