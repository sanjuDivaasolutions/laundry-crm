<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasAdvancedFilter, HasFactory , Searchable;

    public $table = 'payments';

    protected $appends = [
        'payment_type_label',
        'tran_type_label',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public const TRAN_TYPE_SELECT = [
        [
            'label' => 'Send',
            'value' => 'send',
        ],
        [
            'label' => 'Receive',
            'value' => 'receive',
        ],
    ];

    public const PAYMENT_TYPE_SELECT = [
        [
            'label' => 'Sales Invoice',
            'value' => 'si',
        ],
        [
            'label' => 'Purchase Invoice',
            'value' => 'pi',
        ],
    ];

    protected $orderable = [
        'id',
        'payment_type',
        'tran_type',
        'sales_order.so_number',
        'sales_invoice.invoice_number',
        'purchase_order.po_number',
        'purchase_invoice.po_number',
        'payment_mode.name',
        'order_no',
        'reference_no',
        'payment_date',
        'remarks',
        'amount',
        'user.name',
    ];

    protected $filterable = [
        'id',
        'payment_type',
        'tran_type',
        'sales_order.so_number',
        'sales_invoice.invoice_number',
        'purchase_order.po_number',
        'purchase_invoice.po_number',
        'payment_mode.name',
        'order_no',
        'reference_no',
        'payment_date',
        'remarks',
        'amount',
        'user.name',
        'created_at',
        'updated_at',
        'date',
    ];

    protected $fillable = [
        'payment_type',
        'tran_type',
        'sales_order_id',
        'sales_invoice_id',
        'purchase_order_id',
        'purchase_invoice_id',
        'payment_mode_id',
        'order_no',
        'reference_no',
        'payment_date',
        'remarks',
        'amount',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getPaymentTypeLabelAttribute()
    {
        return collect(static::PAYMENT_TYPE_SELECT)->firstWhere('value', $this->payment_type)['label'] ?? '';
    }

    public function getTranTypeLabelAttribute()
    {
        return collect(static::TRAN_TYPE_SELECT)->firstWhere('value', $this->tran_type)['label'] ?? '';
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function getPaymentDateAttribute($value)
    {
         return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setPaymentDateAttribute($value)
    {
        $this->attributes['payment_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
