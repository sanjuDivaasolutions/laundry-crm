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
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use App\Services\ModelCacheService;
use App\Services\QueryService;
use Illuminate\Support\Str;

class SearchApiController extends Controller
{
    public string $idValue = 'value';

    public string $labelValue = 'label';

    public function keys()
    {
        return [
            'keys' => config('system.query.search.key_preserve_list', []),
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
            if (! method_exists($this, $type)) {
                return errorResponse('Invalid search type');
            }

            return okResponse($this->{$type}());

        }
        $additional = $meta['additional'] ?? [];
        $scopes = $meta['scopes'] ?? [];
        $withoutScopes = $meta['withoutScopes'] ?? [];
        $wheres = $meta['wheres'] ?? [];
        $relations = $meta['relations'] ?? [];
        $appends = $meta['appends'] ?? [];
        $unsets = $meta['unsets'] ?? [];

        return okResponse(QueryService::search($meta['class'], $meta['field'], $q, $meta['idValue'], $meta['labelValue'], $scopes, $additional, $wheres, $relations, $appends, $unsets, $withoutScopes));
    }

    private function getSearchMeta($type)
    {
        switch ($type) {
            case 'users':
                return [
                    'class' => User::class,
                    'field' => 'name',
                    'idValue' => $this->idValue,
                    'labelValue' => $this->labelValue,
                ];

            case 'roles':
                return [
                    'class' => Role::class,
                    'field' => 'title',
                    'idValue' => 'id',
                    'labelValue' => 'title',
                ];

            case 'currencies':
                return [
                    'class' => Currency::class,
                    'field' => 'name',
                    'idValue' => 'id',
                    'labelValue' => 'name',
                    'additional' => ['code', 'rate'],
                ];

            case 'countries':
                return [
                    'class' => Country::class,
                    'field' => 'name',
                    'idValue' => 'id',
                    'labelValue' => 'name',
                ];

            case 'cities':
                return [
                    'class' => City::class,
                    'field' => 'name',
                    'idValue' => 'id',
                    'labelValue' => 'name',
                ];

            case 'states':
                return [
                    'class' => State::class,
                    'field' => 'name',
                    'idValue' => 'id',
                    'labelValue' => 'name',
                ];

            case 'companies':
                return [
                    'class' => \App\Models\Company::class,
                    'field' => 'name',
                    'idValue' => 'id',
                    'labelValue' => 'name',
                ];

            default:
                return null;

        }
    }

    private function getOptionData($type)
    {
        if (! method_exists($this, $type)) {
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
