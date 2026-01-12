<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 12/02/25, 5:03 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Models\Supplier;
use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Schema;

class SalesInvoice extends Model
{
    use HasAdvancedFilter, HasFactory, CompanyScopeTrait;

    protected ?float $calculatedTotalPaid = null;
    protected ?string $calculatedPaymentStatus = null;

    public $table = 'sales_invoices';

    protected $appends = [
        'type_label',
        'total_paid',
        'pending_amount',
        'payment_status_label',
        'payment_status_badge',
    ];

    

    public const TYPE_SELECT = [
        [
            'label' => 'Pickup',
            'value' => 'p',
        ],
        [
            'label' => 'Delivery',
            'value' => 'd',
        ],
    ];

    public const ORDER_TYPE_SELECT = [
        [
            'label' => 'Product',
            'value' => 'product',
        ],
        [
            'label' => 'Service',
            'value' => 'service',
        ],
        [
            'label' => 'Contract',
            'value' => 'contract',
        ],
    ];

    protected $orderable = [
        'id',
        'invoice_number',
        'sales_order.so_number',
        'payment_term.name',
        'date',
        'due_date',
        'buyer.code',
        'remark',
        'user.name',
        'type',
        'order_type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'commission',
        'commission_total',
        'is_taxable',
        'payment_status',
    ];

    protected $filterable = [
        'id',
        'invoice_number',
        'sales_order.so_number',
        'payment_term.name',
        'date',
        'due_date',
        'buyer.code',
        'remark',
        'user.name',
        'type',
        'order_type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'commission',
        'commission_total',
        'is_taxable',
        'payment_status',
    ];

    protected $fillable = [
        'invoice_number',
        'company_id',
        'warehouse_id',
        'pos_session_id',
        'sales_order_id',
        'payment_term_id',
        'date',
        'due_date',
        'buyer_id',
        'agent_id',
        'remark',
        'user_id',
        'state_id',
        'type',
        'order_type',
        'reference_no',
        'sub_total',
        'tax_total',
        'tax_rate',
        'grand_total',
        'commission',
        'commission_total',
        'created_at',
        'updated_at',
        'is_taxable',
        'payment_status',
    ];

    protected array $overrideOrderFields = [
        'sub_total_text'   => 'sub_total',
        'tax_total_text'   => 'tax_total',
        'grand_total_text' => 'grand_total',
    ];

    protected $casts = [
        'date'        => 'date',
        'due_date'    => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'is_taxable'       => 'boolean',
        'sub_total'        => 'decimal:2',
        'tax_total'        => 'decimal:2',
        'grand_total'      => 'decimal:2',
        'commission'       => 'decimal:2',
        'commission_total' => 'decimal:2',
    
        'date' => 'datetime',
        'due_date' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function getDueDateAttribute($value)
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setDueDateAttribute($value)
    {
        $this->attributes['due_date'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function agent()
    {
        return $this->belongsTo(Supplier::class, 'agent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute()
    {
        return collect(static::TYPE_SELECT)->firstWhere('value', $this->type)['label'] ?? '';
    }

    public function items()
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function taxes(): MorphMany
    {
        return $this->morphMany(OrderTaxDetail::class, 'taxable')->orderBy('priority');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->hasOne(Package::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function getIsTaxableLabelAttribute(): string
    {
        return $this->is_taxable ? 'Yes' : 'No';
    }

    // Payment-related methods
    public function payments()
    {
        return $this->hasMany(Payment::class, 'sales_invoice_id');
    }

    public function getTotalPaidAttribute()
    {
        return $this->sumIncomingPayments();
    }

    public function getPendingAmountAttribute()
    {
        return max(0, $this->grand_total - $this->total_paid);
    }

    public function syncPaymentStatus(): string
    {
        $this->calculatedTotalPaid = null;
        $this->calculatedPaymentStatus = null;

        $newStatus = $this->computePaymentStatus();

        if (!Schema::hasColumn($this->getTable(), 'payment_status')) {
            return $newStatus;
        }

        if ($this->payment_status !== $newStatus) {
            $originalTimestamps = $this->timestamps;
            $this->timestamps = false;
            $this->payment_status = $newStatus;
            $this->save();
            $this->timestamps = $originalTimestamps;
        }

        return $newStatus;
    }

    protected function sumIncomingPayments(): float
    {
        if ($this->calculatedTotalPaid !== null) {
            return $this->calculatedTotalPaid;
        }

        if (!$this->exists) {
            return $this->calculatedTotalPaid = 0.0;
        }

        $query = Payment::query()
            ->where('sales_invoice_id', $this->getKey())
            ->where(function ($q) {
                $q->where('tran_type', 'receive');

                if (Schema::hasColumn('payments', 'payment_type')) {
                    $q->orWhere(function ($nested) {
                        $nested->where('tran_type', 'send')
                            ->where('payment_type', 'si');
                    });
                }
            });

        return $this->calculatedTotalPaid = (float) $query->sum('amount');
    }

    protected function computePaymentStatus(): string
    {
        if ($this->calculatedPaymentStatus !== null) {
            return $this->calculatedPaymentStatus;
        }

        $totalPaid = round((float) $this->sumIncomingPayments(), 2);
        $grandTotal = round((float) ($this->grand_total ?? 0), 2);

        $status = 'pending';
        if ($totalPaid > 0) {
            $status = $totalPaid >= $grandTotal ? 'paid' : 'partial';
        }

        return $this->calculatedPaymentStatus = $status;
    }


    public function getPaymentStatusLabelAttribute()
    {
        $status = $this->computePaymentStatus();

        switch ($status) {
            case 'pending':
                return 'Pending';
            case 'partial':
                return 'Partial';
            case 'paid':
                return 'Paid';
            default:
                return 'Unknown';
        }
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $status = $this->computePaymentStatus();

        switch ($status) {
            case 'pending':
                return '<span class="badge badge-danger">Pending</span>';
            case 'partial':
                return '<span class="badge badge-warning">Partial</span>';
            case 'paid':
                return '<span class="badge badge-success">Paid</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }
}
