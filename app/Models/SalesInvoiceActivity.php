<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceActivity extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'sales_invoice_activities';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'title',
        'description',
        'sale_invoice.invoice_number',
        'user.name',
    ];

    protected $orderable = [
        'id',
        'title',
        'description',
        'sale_invoice.invoice_number',
        'user.name',
        'is_active',
    ];

    protected $fillable = [
        'title',
        'description',
        'sale_invoice_id',
        'user_id',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function saleInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
