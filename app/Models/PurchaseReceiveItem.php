<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReceiveItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'purchase_receive_items';

    protected $orderable = [
        'id',
        'purchase_receive.code',
        'purchase_order.po_number',
        'purchase_invoice.invoice_number',
        'product.code',
        'quantity',
    ];

    protected $filterable = [
        'id',
        'purchase_receive.code',
        'purchase_order.po_number',
        'purchase_invoice.invoice_number',
        'product.code',
        'quantity',
    ];

    protected $fillable = [
        'purchase_receive_id',
        'purchase_order_id',
        'purchase_invoice_id',
        'product_id',
        'quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function purchaseReceive()
    {
        return $this->belongsTo(PurchaseReceive::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
