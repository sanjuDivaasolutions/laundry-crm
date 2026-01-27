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
        ];

        $groups = PermissionGroup::all();

        $projectModules = [
            ['module' => 'company', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'item', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
            ['module' => 'category', 'actions' => ['create', 'edit', 'show', 'delete', 'access'], 'group' => 'General'],
        ];

        $simpleCustom = [];

        $data = [];
        foreach ($simple as $s) {
            $g = $groups->where('name', $s['group'])->first();
            if ($g) {
                $data[] = ['permission' => $s['module'], 'group_id' => $g->id];
            }
        }
        foreach ($defaultModules as $m) {
            foreach ($m['actions'] as $a) {
                $g = $groups->where('name', $m['group'])->first();
                if ($g) {
                    $data[] = ['permission' => $m['module'].'_'.$a, 'group_id' => $g->id];
                }
            }
        }

        foreach ($projectModules as $m) {
            foreach ($m['actions'] as $a) {
                $g = $groups->where('name', $m['group'])->first();
                if ($g) {
                    $data[] = ['permission' => $m['module'].'_'.$a, 'group_id' => $g->id];
                }
            }
        }

        return $data;
    }

    public static function getGroupData()
    {
        return [
            'General',
        ];
    }
}
