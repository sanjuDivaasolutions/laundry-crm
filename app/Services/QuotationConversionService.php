<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuotationConversionService
{
    /**
     * Convert quotation to sales order
     *
     * @param Quotation $quotation
     * @param array $data
     * @return SalesOrder
     */
    public function convertToSalesOrder(Quotation $quotation, array $data): SalesOrder
    {
        return DB::transaction(function () use ($quotation, $data) {
            // Create sales order
            $salesOrder = $this->createSalesOrder($quotation, $data);
            
            // Create sales order items
            $this->createSalesOrderItems($salesOrder, $quotation, $data);
            
            // Update quotation status
            $quotation->update(['status' => 'converted']);
            
            return $salesOrder;
        });
    }

    /**
     * Generate preview of sales order data
     *
     * @param Quotation $quotation
     * @return array
     */
    public function previewSalesOrder(Quotation $quotation): array
    {
        $items = $quotation->items()->with('product')->get();
        
        return [
            'quotation_number' => $quotation->quotation_number,
            'customer' => [
                'id' => $quotation->buyer_id,
                'name' => $quotation->buyer->name,
                'email' => $quotation->buyer->email,
                'phone' => $quotation->buyer->phone,
            ],
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percentage' => $item->discount_percentage,
                    'tax_rate' => $item->tax_rate,
                    'total' => $item->total,
                ];
            }),
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'discount_amount' => $quotation->discount_amount,
            'total_amount' => $quotation->total_amount,
        ];
    }

    /**
     * Create sales order from quotation
     *
     * @param Quotation $quotation
     * @param array $data
     * @return SalesOrder
     */
    protected function createSalesOrder(Quotation $quotation, array $data): SalesOrder
    {
        $salesOrderData = [
            'sales_order_number' => $this->generateSalesOrderNumber(),
            'buyer_id' => $quotation->buyer_id,
            'quotation_id' => $quotation->id,
            'order_date' => now(),
            'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
            'customer_notes' => $data['customer_notes'] ?? $quotation->notes,
            'terms_and_conditions' => $data['terms_and_conditions'] ?? $quotation->terms_and_conditions,
            'payment_terms' => $data['payment_terms'] ?? $quotation->payment_terms,
            'sales_person_id' => $data['sales_person_id'] ?? $quotation->sales_person_id,
            'warehouse_id' => $data['warehouse_id'],
            'status' => 'pending',
            'subtotal' => $quotation->subtotal,
            'tax_amount' => $quotation->tax_amount,
            'discount_amount' => $quotation->discount_amount,
            'total_amount' => $quotation->total_amount,
            'company_id' => $quotation->company_id,
            'department_id' => $quotation->department_id,
        ];

        return SalesOrder::create($salesOrderData);
    }

    /**
     * Create sales order items
     *
     * @param SalesOrder $salesOrder
     * @param Quotation $quotation
     * @param array $data
     * @return void
     */
    protected function createSalesOrderItems(SalesOrder $salesOrder, Quotation $quotation, array $data): void
    {
        $convertAll = $data['convert_all_items'] ?? true;
        $selectedItems = $data['selected_items'] ?? [];

        $quotationItems = $quotation->items()
            ->when(!$convertAll, function ($query) use ($selectedItems) {
                $itemIds = collect($selectedItems)->pluck('id');
                return $query->whereIn('id', $itemIds);
            })
            ->get();

        foreach ($quotationItems as $quotationItem) {
            $selectedItemData = collect($selectedItems)->firstWhere('id', $quotationItem->id);
            
            $salesOrderItemData = [
                'sales_order_id' => $salesOrder->id,
                'product_id' => $quotationItem->product_id,
                'description' => $quotationItem->description,
                'quantity' => $selectedItemData['quantity'] ?? $quotationItem->quantity,
                'unit_price' => $selectedItemData['unit_price'] ?? $quotationItem->unit_price,
                'discount_percentage' => $quotationItem->discount_percentage,
                'tax_rate' => $quotationItem->tax_rate,
                'total' => $this->calculateItemTotal(
                    $selectedItemData['quantity'] ?? $quotationItem->quantity,
                    $selectedItemData['unit_price'] ?? $quotationItem->unit_price,
                    $quotationItem->discount_percentage,
                    $quotationItem->tax_rate
                ),
                'company_id' => $quotationItem->company_id,
                'department_id' => $quotationItem->department_id,
            ];

            SalesOrderItem::create($salesOrderItemData);
        }
    }

    /**
     * Calculate item total
     *
     * @param float $quantity
     * @param float $unitPrice
     * @param float $discountPercentage
     * @param float $taxRate
     * @return float
     */
    protected function calculateItemTotal(float $quantity, float $unitPrice, float $discountPercentage, float $taxRate): float
    {
        $subtotal = $quantity * $unitPrice;
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = $afterDiscount * ($taxRate / 100);
        
        return $afterDiscount + $taxAmount;
    }

    /**
     * Generate unique sales order number
     *
     * @return string
     */
    protected function generateSalesOrderNumber(): string
    {
        $prefix = 'SO';
        $year = date('Y');
        $month = date('m');
        
        $latestOrder = SalesOrder::where('sales_order_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('sales_order_number', 'desc')
            ->first();
        
        if ($latestOrder) {
            $lastSequence = intval(substr($latestOrder->sales_order_number, -4));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return "{$prefix}{$year}{$month}" . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }
}