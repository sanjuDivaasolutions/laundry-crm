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
 *  *  Last modified: 05/02/25, 7:31 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Contract;
use App\Models\Inward;
use App\Models\OrderTaxDetail;
use App\Models\Product;
use App\Models\ProductStockShelf;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Shelf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class InvoiceService
{
    public static function prepareItems($items, $parentField, $parentId = null): array
    {
        $result = [];
        foreach ($items as $item) {
            $item[$parentField] = $parentId;
            $item['product_id'] = $item['product']['id'];
            $result[] = $item;
        }
        return $result;
    }

    public static function updateTotals(Invoice $invoice): void
    {
        $invoice->payment_total = $invoice->payments()->sum('amount');
        $invoice->sub_total = $invoice->items()->sum('amount');
        $invoice->tax_total = $invoice->sub_total * ($invoice->tax_rate / 100);
        $invoice->grand_total = $invoice->sub_total + $invoice->tax_total;

        if ($invoice->payment_total >= $invoice->grand_total) {
            $invoice->payment_status = 'paid';
        } elseif ($invoice->payment_total > 0) {
            $invoice->payment_status = 'partial';
        } else {
            $invoice->payment_status = 'pending';
        }

        $invoice->save();
    }

    public static function generateInvoiceFromContract(Contract $contract): SalesInvoice
    {
        $contract->load(['revision.items.product']);

        $revision = $contract->revision;
        $revisionItems = $revision->items;
        $invoice = new SalesInvoice();
        $invoice->company_id = $contract->company_id;
        $invoice->order_type = 'contract';
        $invoice->invoice_number = self::getInvoiceCode();
        $invoice->contract_revision_id = $revision->id;
        $invoice->date = Carbon::now()->format(config('project.date_format'));
        $invoice->buyer_id = $contract->buyer_id;
        $invoice->remark = 'Invoice for ' . $contract->code;
        $invoice->sub_total = $revision->sub_total;
        $invoice->tax_total = $revision->tax_total;
        $invoice->tax_rate = $revision->tax_rate;
        $invoice->grand_total = $revision->grand_total;
        $invoice->user_id = adminAuth()->id();
        $invoice->state_id = $contract?->buyer?->billingAddress?->state_id ?? config('system.defaults.state.id', 1);
        $invoice->save();

        foreach ($revisionItems as $item) {
            $sii = new SalesInvoiceItem();
            $sii->sales_invoice_id = $invoice->id;
            $sii->product_id = $item->product_id;
            $sii->description = $item->description;
            $sii->sku = $item->product->sku;
            $sii->unit_id = $item->product->unit_id;
            $sii->remark = $item->remark;
            $sii->quantity = 1;
            $sii->rate = $item->amount;
            $sii->original_rate = $item->amount;
            $sii->amount = $item->amount;
            $sii->save();
        }

        self::setupTaxes($invoice);

        return $invoice;
    }

    public static function generateInvoiceFromInstallment(Installment $installment): Invoice
    {
        $invoice = new Invoice();
        $invoice->invoice_number = $installment->code;
        $invoice->date = $installment->date;
        $invoice->client_id = $installment->contract->client_id;
        $invoice->remark = 'Invoice for ' . $installment->code;
        $invoice->sub_total = $installment->sub_total;
        $invoice->tax_total = $installment->tax_total;
        $invoice->tax_rate = $installment->tax_rate;
        $invoice->grand_total = $installment->grand_total;
        $invoice->save();

        $items = [];
        foreach ($installment->contract->items as $item) {
            $items[] = [
                'service_id' => $item->service_id,
                'amount'     => $item->amount,
            ];
        }
        $items = self::prepareItems($items, 'invoice_id', $invoice->id);
        $invoice->items()->createMany($items);

        return $invoice;
    }

    public static function getInvoiceCode(): int|string
    {
        $field = 'invoice_number';
        $config = [
            'table'  => 'sales_invoices',
            'field'  => $field,
            'prefix' => 'SI-'
        ];
        return UtilityService::generateCode($config);
    }

    public static function getItemsObject($request): array
    {
        $items = stringToArray($request->input('items', []));
        foreach ($items as &$i) {
            $p = Product::query()->find($i['product']['id']);
            if ($p) {
                $p = $p->toArray();
                $i['product_id'] = $i['product']['id'];
                unset($p['rate'], $p['unit_id'], $p['id'], $p['created_at'], $p['updated_at'], $p['description']);
                $i = array_merge($i, $p);
                unset($i['product']);
            }
            if (isset($i['unit']) && $i['unit']) {
                $i['unit_id'] = $i['unit']['id'];
                unset($i['unit']);
            }
            if (isset($i['shelf']) && $i['shelf']) {
                $i['shelf_id'] = $i['shelf']['id'];
                unset($i['shelf']);
            }
            unset($i['type']);
        }
        return $items;
    }

    public static function getProductWarehouseShelves($pid, $wid = null): array
    {
        $shelves = ProductStockShelf::query()
            ->whereHas('productStock', function ($q) use ($pid, $wid) {
                if ($wid) {
                    $q->where('warehouse_id', $wid);
                }
                $q->whereHas('product', function ($q1) use ($pid) {
                    $q1->where('id', $pid);
                });
            })
            ->with(['shelf:id,name'])
            ->orderBy('on_hand', 'desc')
            ->get();

        $result = [];
        foreach ($shelves as $shelf) {
            $name = $shelf->shelf->name . ' (' . $shelf->on_hand . ')';
            $result[] = [
                'id'   => $shelf->shelf->id,
                'name' => $name,
            ];
        }

        $existingShelfIds = collect($result)->pluck('id')->all();
        $fallbackShelves = Shelf::query()
            ->orderBy('name');
        if ($wid) {
            $fallbackShelves->where('warehouse_id', $wid);
        }
        $fallbackShelves = $fallbackShelves->get(['id', 'name']);

        foreach ($fallbackShelves as $shelf) {
            if (in_array($shelf->id, $existingShelfIds, true)) {
                continue;
            }
            $result[] = [
                'id'   => $shelf->id,
                'name' => $shelf->name . ' (0)',
            ];
        }

        return $result;
    }

    public static function getProductShelvesData($pid): array
    {
        $shelves = Shelf::query()
            ->orderBy('name')
            ->where('active', 1)
            ->get(['id', 'name']);

        $productShelfStock = ProductStockShelf::query()
            ->whereHas('productStock', function ($q) use ($pid) {
                $q->where('product_id', $pid);
            })
            ->get();

        return $shelves->map(function ($shelf) use ($pid, $productShelfStock) {
            $onHand = $productShelfStock->where('shelf_id', $shelf->id)->sum('on_hand');
            return [
                'id'         => $shelf->id,
                'name'       => $shelf->name . ' (' . $onHand . ')',
                'product_id' => $pid,
                'on_hand'    => $onHand,
            ];
        })->toArray();
    }

    public static function updateTaxes($request, $obj, $class): void
    {
        $taxes = $obj->is_taxable ? $request->input('taxes', []) : [];
        foreach ($taxes as &$tax) {
            $tax['taxable_type'] = $class;
        }
        $request->merge(['taxes' => $taxes]);
        ControllerService::updateChild($request, $obj, 'taxes', OrderTaxDetail::class, 'taxes', 'taxable_id');
    }

    public static function upgradeSITaxes(): void
    {
        $invoices = SalesInvoice::query()
            ->with([
                'buyer:id,billing_address_id',
                'buyer.billingAddress:id,state_id',
                'buyer.billingAddress.state:id,name',
                'taxes',
            ])
            ->get();
        $defaultStateId = 1;
        foreach ($invoices as $invoice) {
            $stateId = $invoice?->buyer?->billingAddress?->state?->id ?? $defaultStateId;
            $invoice->state_id = $stateId;
            $invoice->save();

            self::setupTaxes($invoice);
        }
    }

    public static function upgradeInwardTaxes(): void
    {
        $invoices = Inward::query()
            ->with([
                'supplier:id,billing_address_id',
                'supplier.billingAddress:id,state_id',
                'supplier.billingAddress.state:id,name',
                'taxes',
            ])
            ->get();
        $defaultStateId = 1;
        foreach ($invoices as $invoice) {
            $stateId = $invoice?->supplier?->billingAddress?->state?->id ?? $defaultStateId;
            $invoice->state_id = $stateId;
            $invoice->save();

            self::setupTaxes($invoice);

        }
    }

    public static function setupTaxes($i): void
    {
        if (!$i->is_taxable) {
            $i->taxes()->delete();
            $i->tax_total = 0;
            $i->grand_total = $i->sub_total;
            $i->save();
            return;
        }

        $stateId = $i->state_id;

        $taxes = $i->taxes;
        $stateTaxes = CanadaTaxService::getTaxesByState($stateId);

        $updatedTaxes = [];
        $taxTotal = 0;
        foreach ($stateTaxes as $stateTax) {
            $tax = $taxes ? $taxes->where('tax_id', $stateTax->id)->first() : null;
            $taxAmount = $i->sub_total * ($stateTax->rate / 100);
            $taxTotal += $taxAmount;
            if ($tax) {
                $tax->amount = $taxAmount;
                //$tax->save();
                $updatedTaxes[] = $tax->id;
            } else {
                $tax = new OrderTaxDetail();
                $tax->taxable_id = $i->id;
                $tax->taxable_type = get_class($i);
                $tax->tax_rate_id = $stateTax->id;
                $tax->amount = $taxAmount;
                $tax->save();
                /*$newTax = $i->taxes()->create([
                    'tax_id' => $stateTax->id,
                    'amount' => $taxAmount,
                ]);*/
                $updatedTaxes[] = $tax->id;
            }
        }

        //Remove extra taxes from invoice
        $i->taxes()->whereNotIn('id', $updatedTaxes)->delete();

        $i->tax_total = $taxTotal;
        $i->grand_total = $i->sub_total + $taxTotal;
        $i->save();
    }
    
    public static function getOutstandingInvoicesForBuyer(SalesInvoice $currentInvoice, int $limit = 20): array
    {
        if (!$currentInvoice->buyer_id) {
            return [
                'invoices' => collect(),
                'total'    => 0,
            ];
        }

        $table = (new SalesInvoice())->getTable();
        $hasPaymentStatusColumn = Schema::hasColumn($table, 'payment_status');

        $invoices = SalesInvoice::query()
            ->with(['payments' => function ($query) {
                $query->where(function ($q) {
                    $q->where('tran_type', 'receive')
                        ->orWhere(function ($nested) {
                            $nested->where('tran_type', 'send')
                                ->where('payment_type', 'si');
                        });
                });
            }])
            ->where('buyer_id', $currentInvoice->buyer_id)
            ->when($currentInvoice->company_id, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('id', '<>', $currentInvoice->id)
            ->when($hasPaymentStatusColumn, function ($query) {
                $query->whereIn('payment_status', ['pending', 'partial']);
            })
            ->orderByDesc('date')
            ->limit($limit)
            ->get();

        $formatted = $invoices->map(function (SalesInvoice $invoice) use ($hasPaymentStatusColumn) {
            $paidAmount = $invoice->payments->sum('amount');
            $pendingAmount = max(0, $invoice->grand_total - $paidAmount);

            if ($pendingAmount <= 0) {
                return null;
            }

            $status = 'pending';
            if ($hasPaymentStatusColumn) {
                $status = $invoice->payment_status ?: 'pending';
            } else {
                if ($paidAmount >= $invoice->grand_total) {
                    $status = 'paid';
                } elseif ($paidAmount > 0) {
                    $status = 'partial';
                }
            }

            return [
                'id'              => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'date'            => $invoice->date,
                'pending_amount'  => $pendingAmount,
                'status_label'    => $status === 'pending'
                    ? 'Unpaid'
                    : ucfirst($status ?? 'Pending'),
            ];
        })->filter()->values();

        return [
            'invoices' => $formatted,
            'total'    => $formatted->sum('pending_amount'),
        ];
    }
}
