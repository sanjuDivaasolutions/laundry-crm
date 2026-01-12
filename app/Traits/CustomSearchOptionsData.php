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
 *  *  Last modified: 12/02/25, 4:53 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Traits;

use App\Models\Buyer;
use App\Models\Category;
use App\Models\Company;
use App\Models\ContractRevision;
use App\Models\ContractTerm;
use App\Models\Currency;
use App\Models\ExpenseType;
use App\Models\Feature;
use App\Models\InventoryAdjustment;
use App\Models\Package;
use App\Models\PackingType;
use App\Models\PaymentMode;
use App\Models\PaymentTerm;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Shelf;
use App\Models\ShipmentMode;
use App\Models\State;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\CompanyService;
use App\Services\InvoiceService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CustomSearchOptionsData
{
    private function permissionGroups()
    {
        return PermissionGroup::query()->orderBy('name')->get(['name', 'id']);
    }

    private function permissions()
    {
        return Permission::query()->orderBy('title')->get(['title', 'id']);
    }

    private function states()
    {
        return State::query()->orderBy('name')->get(['name', 'id']);
    }

    private function roles()
    {
        return Role::query()->orderBy('title')->get(['title', 'id']);
    }

    private function paymentTerms()
    {
        return PaymentTerm::query()->orderBy('name')->get(['name', 'id']);
    }

    private function currencies()
    {
        return Currency::query()->orderBy('name')->get(['name', 'id', 'code', 'rate']);
    }

    private function units()
    {
        return Unit::query()->orderBy('name')->get(['name', 'id']);
    }

    private function shipmentModes()
    {
        return ShipmentMode::query()->orderBy('name')->get(['name', 'id']);
    }

    private function yesNos()
    {
        return [
            ['label' => 'Yes', 'value' => 'yes',],
            ['label' => 'No', 'value' => 'no',],
        ];
    }

    private function creditDebitTypes()
    {
        return [
            ['label' => 'Credit', 'value' => 'cr',],
            ['label' => 'Debit', 'value' => 'dr',],
        ];
    }

    private function packingTypes()
    {
        return PackingType::query()->orderBy('name')->get(['name', 'id']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function parentCategories()
    {
        $categories = Category::query()
            ->where('parent_id', null)
            ->orderBy('name');
        if (request()->get('id')) {
            $categories->where('id', '!=', request()->get('id'));
        }
        return $categories->get(['name', 'id']);
    }

    private function categories()
    {
        return Category::query()->company()->get(['id', 'name']);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function companyCategories()
    {
        $categories = Category::query();
        $filterId = request()->get('cid');
        if ($filterId) {
            $categories->where('company_id', '=', $filterId);
        }
        return $categories->get(['id', 'name']);
    }

    private function warehouses()
    {
        return Warehouse::query()
            ->company()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function shelves()
    {
        $warehouseId = request()->get('wid');
        $shelves = Shelf::query()
            ->company()
            ->orderBy('name');
        if ($warehouseId) {
            $shelves->where('warehouse_id', '=', $warehouseId);
        }

        return $shelves->get(['name', 'id', 'warehouse_id']);
    }

    private function features()
    {
        return Feature::get(['id', 'name']);
    }

    private function purchase_products()
    {
        $searchFields = ['name', 'sku'];
        $query = trim(request()->get('q'));
        $items = Product::query()
            ->company()
            ->onlyProducts()
            ->with([
                'prices',
                'unit_01:id,name',
            ]);
        if ($query) {
            $items->where(function ($q) use ($searchFields, $query) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', '%' . $query . '%');
                }
            });
        }
        $items = $items->get(['id', 'name', 'unit_01_id']);
        $result = [];
        if ($items) {
            foreach ($items as $i) {

                $firstPrice = $i->prices->first();
                $rate = $firstPrice ? $firstPrice->purchase_price : 0;

                $result[] = [
                    'id'      => $i->id,
                    'name'    => $i->name,
                    'unit_01' => $i->unit_01,
                    'rate'    => $rate,
                ];
            }
        }
        return $result;
    }

    private function salesProducts()
    {
        $q = trim(request()->get('q'));
        $items = Product::query()
            ->onlyProducts()
            ->with(['prices', 'unit_01:id,name',]);
        if ($q) {
            $items->where('name', 'like', '%' . $q . '%');
        }
        $items = $items->get(['id', 'name', 'unit_01_id']);
        if ($items) {
            $items = $items->toArray();
            foreach ($items as &$i) {
                $i['unit'] = $i['unit_01'];
                unset($i['unit_01']);
                $i['rate'] = $i['prices'][0]['sale_price'];
            }
        }
        return $items;
    }

    private function purchase_orders()
    {
        return PurchaseOrder::get(['id', 'po_number']);
    }

    private function sales_orders()
    {
        return SalesOrder::get(['id', 'so_number']);
    }

    private function buyers()
    {
        return Buyer::get(['id', 'display_name']);
    }

    private function suppliers()
    {
        return Supplier::query()
            ->orderBy('display_name')
            ->get(['id', 'display_name']);
    }

    private function dummy_array()
    {
        return [];
    }

    private function packages()
    {
        return Package::get(['id', 'code']);
    }

    private function shipment_modes()
    {
        return ShipmentMode::get(['id', 'name']);
    }

    private function paymentModes()
    {
        return PaymentMode::get(['id', 'name']);
    }

    private function companies()
    {
        return Company::query()->orderBy('name')->get(['id', 'name']);
    }

    private function productTypes()
    {
        return Product::TYPE_SELECT;
    }

    private function services()
    {
        return Product::query()
            ->where('type', 'service')
            ->with(['price'])
            ->orderBy('name')
            ->get(['name', 'id', 'description']);
    }

    private function contractTerms()
    {
        return ContractTerm::query()->orderBy('name')->get(['name', 'id']);
    }

    private function contractTypes()
    {
        return ContractRevision::CONTRACT_TYPE_SELECT;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function warehouseShelves()
    {
        $result = Shelf::query()
            ->company()
            ->orderBy('name');
        $warehouseId = request()->get('wid');

        if ($warehouseId) {
            $result->where('warehouse_id', '=', $warehouseId);
        }

        return $result->get(['name', 'id']);
    }

    private function paymentMethods()
    {
        return PaymentMode::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['name', 'id']);
    }

    private function expenseTypes()
    {
        return ExpenseType::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['name', 'id']);
    }

    private function productWarehouseShelves()
    {
        $pid = request()->get('pid');
        $wid = request()->get('wid');

        if (!$pid || !$wid) {
            return [];
        }

        return InvoiceService::getProductWarehouseShelves($pid, $wid);
    }

    private function productShelves()
    {
        $pid = request()->get('pid');

        if (!$pid) {
            return [];
        }

        return InvoiceService::getProductWarehouseShelves($pid);
    }

    private function activeProductShelves()
    {
        $pid = request()->get('pid');

        if (!$pid) {
            return [];
        }

        return InvoiceService::getProductShelvesData($pid);
    }

    private function activeShelves()
    {
        return Shelf::query()
            ->company()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['name', 'id']);
    }

    private function activeProducts()
    {
        return Product::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['name', 'id']);
    }

    private function packageSalesInvoices()
    {
        // Get the list of sales invoices where the package is not already assigned
        return SalesInvoice::query()
            ->company()
            ->whereDoesntHave('package')
            ->orderByDesc('date')
            ->orderByDesc('invoice_number')
            ->get(['id', 'invoice_number']);
        //return SalesInvoice::query()->get(['id', 'invoice_number']);
    }

    private function inventoryAdjustmentReasons()
    {
        return InventoryAdjustment::REASON_SELECT;
    }

    private function taxRates()
    {
        return TaxRate::query()->get();
    }

    private function paymentStatuses()
    {
        return [
            ['label' => 'All', 'value' => null],
            ['label' => 'Pending', 'value' => 'pending'],
            ['label' => 'Partial', 'value' => 'partial'],
            ['label' => 'Paid', 'value' => 'paid'],
        ];
    }
}
