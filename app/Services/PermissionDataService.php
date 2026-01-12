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
 *  *  Last modified: 05/02/25, 4:03 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\PermissionGroup;

class PermissionDataService
{
    public static function getData()
    {
        $simple = [
            ['module' => 'dashboard', 'group' => 'General'],
            ['module' => 'pos_access', 'group' => 'General'],
            ['module' => 'user_menu_access', 'group' => 'General'],
            ['module' => 'module_menu_access', 'group' => 'General'],
            ['module' => 'inventory_menu_access', 'group' => 'Inventory'],
            ['module' => 'contact_menu_access', 'group' => 'Contact'],
            ['module' => 'purchase_menu_access', 'group' => 'Purchases'],
            ['module' => 'sales_menu_access', 'group' => 'Sales'],
        ];

        $defaultModules = [
            ['module' => 'permission_group', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'permission', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'role', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'user', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'language', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'language_term', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'translation', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'language_term_group', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'country', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'state', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'city', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'language', 'actions' => ['create', 'edit', 'show', 'delete', 'access'],'group'=>'General'],
        ];

        $groups = PermissionGroup::all();

        $projectModules = [
            ['module' => 'company', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'unit', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'warehouse', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'shelf', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'buyer', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'supplier', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'currency', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'payment_term', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'category', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Inventory'],
            ['module' => 'product', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Inventory'],
            ['module' => 'feature', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'purchase_order', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'purchase_invoice', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'sales_order', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'sales_invoice', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'sales_return', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'package', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'shipment', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'payment', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'packing_type', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'General'],
            ['module' => 'shipment_mode', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'General'],
            ['module' => 'contract', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Contract'],
            ['module' => 'contract_term', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Contract'],
            ['module' => 'service', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Inventory'],
            ['module' => 'service_invoice', 'actions' => ['create', 'edit', 'show', 'delete', 'access', 'clone', 'convert_si', 'download'], 'group' => 'Sales'],
            ['module' => 'inward', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'expense_type', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'payment_mode', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'expense', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Purchases'],
            ['module' => 'quotation', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Sales'],
            ['module' => 'package', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Sales'],
            ['module' => 'inventory_adjustment', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Sales'],
            ['module' => 'subscriber', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Newsletter'],
            ['module' => 'message', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'Newsletter'],
        ];

        $simpleCustom = [
            ['module' => 'report_access', 'group' => 'Report'],
            ['module' => 'newsletter_access', 'group' => 'Newsletter'],
        ];

        $data = [];
        foreach ($simple as $s) {
            $g = $groups->where('name', $s['group'])->first();
            $data[] = ['permission' => $s['module'], 'group_id' => $g->id];
        }
        foreach ($defaultModules as $m) {
            foreach ($m['actions'] as $a) {
                $g = $groups->where('name', $m['group'])->first();
                $data[] = ['permission' => $m['module'] . '_' . $a, 'group_id' => $g->id];
            }
        }

        foreach ($projectModules as $m) {
            foreach ($m['actions'] as $a) {
                $g = $groups->where('name', $m['group'])->first();
                if (!$g) {
                    dd($m['group']);
                }
                $data[] = ['permission' => $m['module'] . '_' . $a, 'group_id' => $g->id];
            }
        }

        foreach ($simpleCustom as $s) {
            $g = $groups->where('name', $s['group'])->first();
            $data[] = ['permission' => $s['module'], 'group_id' => $g->id];
        }
        return $data;
    }

    public static function getGroupData()
    {
        return [
            'General',
            'Inventory',
            'Contact',
            'Purchases',
            'Sales',
            'Contract',
            'Report',
            'Newsletter'
        ];
    }
}
