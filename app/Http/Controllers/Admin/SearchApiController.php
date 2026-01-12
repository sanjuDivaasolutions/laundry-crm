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
 *  *  Last modified: 22/01/25, 5:47 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shelf;
use App\Models\State;
use App\Models\User;
use App\Services\ModelCacheService;
use App\Services\QueryService;
use App\Traits\CustomSearchOptionsData;
use Illuminate\Support\Str;

class SearchApiController extends Controller
{
    use CustomSearchOptionsData;

    public string $idValue = 'value';
    public string $labelValue = 'label';

    public function keys()
    {
        return [
            'keys'    => config('system.query.search.key_preserve_list', []),
            'timeout' => config('system.query.search.key_expire_timeout', 300),
            'options' => config('system.query.search.preload_options', []),
        ];
    }

    public function options($type)
    {
        $camelType = Str::camel($type);


        $result = $this->getOptionData($camelType);
        if ($result === false) {
            return errorResponse('Invalid search type');
        }
        return okResponse($result);
    }

    public function bulkOptions($types)
    {
        $types = explode(',', $types);
        $options = [];
        foreach ($types as $type) {
            $camelType = Str::camel($type);
            $result = $this->getOptionData($camelType);
            if ($result === false) {
                return errorResponse('Invalid search type');
            }
            $options[$type] = $result;
        }
        return okResponse($options);
    }

    public function search($type)
    {
        $q = request()->get('q');
        $meta = $this->getSearchMeta($type);
        if (empty($meta)) {
            if (!method_exists($this, $type)) {
                return errorResponse('Invalid search type');
            }
            return okResponse($this->{$type}());
            
        }
        $additional = isset($meta['additional']) && $meta['additional'] ? $meta['additional'] : [];
        $scopes = isset($meta['scopes']) && $meta['scopes'] ? $meta['scopes'] : [];
        $withoutScopes = isset($meta['withoutScopes']) && $meta['withoutScopes'] ? $meta['withoutScopes'] : [];
        $wheres = isset($meta['wheres']) && $meta['wheres'] ? $meta['wheres'] : [];
        $relations = isset($meta['relations']) && $meta['relations'] ? $meta['relations'] : [];
        $appends = isset($meta['appends']) && $meta['appends'] ? $meta['appends'] : [];
        $unsets = isset($meta['unsets']) && $meta['unsets'] ? $meta['unsets'] : [];
        return okResponse(QueryService::search($meta['class'], $meta['field'], $q, $meta['idValue'], $meta['labelValue'], $scopes, $additional, $wheres, $relations, $appends, $unsets, $withoutScopes));
    }

    private function getSearchMeta($type)
    {
        switch ($type) {
            case 'users':
            {
                return [
                    'class'      => User::class,
                    'field'      => 'name',
                    'idValue'    => $this->idValue,
                    'labelValue' => $this->labelValue,
                ];
            }
            case 'leaders':
            {
                return [
                    'class'      => User::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                ];
            }
            case 'roles':
            {
                return [
                    'class'      => Role::class,
                    'field'      => 'title',
                    'idValue'    => 'id',
                    'labelValue' => 'title',
                ];
            }
            case 'currencies':
            {
                return [
                    'class'      => Currency::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'additional' => ['code', 'rate'],
                ];
            }


            case 'countries':
            {
                return [
                    'class'      => Country::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                ];
            }
            case 'cities':
            {
                return [
                    'class'      => City::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                ];
            }
            case 'states':
            {
                return [
                    'class'      => State::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                ];
            }
            case 'buyers':
            {
                return [
                    'class'      => Buyer::class,
                    'field'      => 'display_name',
                    'idValue'    => 'id',
                    'labelValue' => 'display_name',
                    'additional' => ['payment_term_id'],
                ];
            }
            case 'suppliers':
            {
                return [
                    'class'      => \App\Models\Supplier::class,
                    'field'      => 'display_name',
                    'idValue'    => 'id',
                    'labelValue' => 'display_name',
                ];
            }
            case 'agents':
            {
                return [
                    'class'      => \App\Models\Supplier::class,
                    'field'      => 'display_name',
                    'idValue'    => 'id',
                    'labelValue' => 'display_name',
                    'scopes'     => ['agents'],
                ];
            }
            case 'companies':
            {
                return [
                    'class'      => \App\Models\Company::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                ];
            }
            case 'sales-orders':
            {
                return [
                    'class'      => SalesOrder::class,
                    'field'      => 'so_number',
                    'idValue'    => 'id',
                    'labelValue' => 'so_number',
                    //'additional'=>  ['name','item_code'],
                ];
            }
            case 'sales-order-items':
            {
                $wheres = [];
                if (request()->has('id')) {
                    $wheres[] = ['field' => 'sales_order_id', 'operator' => '=', 'value' => request()->get('id')];
                }
                return [
                    'class'      => SalesOrderItem::class,
                    'field'      => 'item_code',
                    'idValue'    => 'id',
                    'labelValue' => 'item_code',
                    'wheres'     => $wheres,
                    //'additional'=>  ['rate'],
                ];
            }
            case 'shipment-order-items':
            {
                $wheres = [];
                if (request()->has('id')) {
                    $wheres[] = ['field' => 'sales_order_id', 'operator' => '=', 'value' => request()->get('id')];
                }
                return [
                    'class'      => SalesOrderItem::class,
                    'field'      => 'item_code',
                    'idValue'    => 'id',
                    'labelValue' => 'item_code',
                    'wheres'     => $wheres,
                    'scopes'     => ['readyToShip'],
                    //'additional'=>  ['rate'],
                ];
            }
            /*  case 'purchase-orders': {
                 return [
                     'class'     =>  PurchaseOrder::class,
                     'field'     =>  'po_number',
                     'idValue'   =>  'id',
                     'labelValue'=>  'po_number',
                     //'additional'=>  ['name','item_code'],
                 ];
             } */
            case 'sales-invoices':
            {
                return [
                    'class'      => SalesInvoice::class,
                    'field'      => 'invoice_number',
                    'idValue'    => 'id',
                    'labelValue' => 'invoice_number',
                    //'additional'=>  ['name','item_code'],
                ];
            }
            case 'active-sales-invoices':
            {
                return [
                    'class'      => SalesInvoice::class,
                    'field'      => 'invoice_number',
                    'idValue'    => 'id',
                    'labelValue' => 'invoice_number',
                    'scopes'     => ['active'],
                    'additional' => ['currency_id', 'buyer_id', 'grand_total'],
                    'relations'  => ['buyer:id,display_name', 'currency:id,symbol'],
                    'appends'    => ['shipment_title'],
                    'unsets'     => ['buyer', 'buyer_id', 'grand_total', 'currency'],
                ];
            }
            case 'purchase-invoices':
            {
                return [
                    'class'      => PurchaseInvoice::class,
                    'field'      => 'invoice_number',
                    'idValue'    => 'id',
                    'labelValue' => 'invoice_number',
                    //'additional'=>  ['name','item_code'],
                ];
            }
            case 'sales-services':
            {
                return [
                    'class'      => Product::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'scopes'     => ['onlyServices'],
                ];
            }
            case 'sales-products':
            {
                return [
                    'class'      => Product::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'additional' => ['unit_01_id', 'sku'],
                    'relations'  => ['unit_01:id,name', 'prices:id,product_id,sale_price'],
                    'scopes'     => ['company'],
                ];
            }
            case 'active-products':
            {
                return [
                    'class'         => Product::class,
                    'field'         => 'name',
                    'idValue'       => 'id',
                    'labelValue'    => 'name',
                    'additional'    => [],
                    'relations'     => [],
                    'scopes'        => ['onlyProducts'],
                    'withoutScopes' => ['company'],
                ];
            }
            case 'categories':
            {
                return [
                    'class'      => Category::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'additional' => ['parent_id'],
                    'relations'  => ['parent:id,name,parent_id'],
                    'unsets'     => ['name', 'parent', 'parent_id'],
                ];
            }
            case 'parent-categories':
            {
                return [
                    'class'      => Category::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'scopes'     => ['parentOnly'],
                ];
            }
            case 'child-categories':
            {
                return [
                    'class'      => Category::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'additional' => ['parent_id'],
                    'scopes'     => ['childOnly'],
                    'relations'  => ['parent:id,name,parent_id'],
                    'unsets'     => ['name', 'parent', 'parent_id'],
                ];
            }
            case 'warehouse-shelves':
            {
                return [
                    'class'      => Shelf::class,
                    'field'      => 'name',
                    'idValue'    => 'id',
                    'labelValue' => 'name',
                    'additional' => ['warehouse_id'],
                    'relations'  => ['warehouse:id,name'],
                    'scopes'     => ['company'],
                    'unsets'     => ['warehouse_id'],
                ];
            }
            default:
            {
                return null;
            }
        }
    }

    private function getOptionData($type)
    {
        if (!method_exists($this, $type)) {
            return false;
        }
        if (ModelCacheService::has($type)) {
            return ModelCacheService::get($type);
        }
        $data = $this->{$type}();
        ModelCacheService::set($type, $data);
        return $data;
    }
}
