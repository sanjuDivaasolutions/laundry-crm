<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 12/12/24, 12:07 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Support;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\ValidationException;

trait HasAdvancedFilter
{
    public function scopeAdvancedFilter($query)
    {
        $data = [
            'order_column'    => explode(',', request('sort', 'id')),
            'order_direction' => request('order', 'desc'),
            'limit'           => request('limit', 10),
            's'               => request('s', null),
        ];
        $f = (request('f')) ? request('f') : [];

        //replace $data['order_column'] with $this->orderableColumns
        if (isset($this->overrideOrderFields) && !empty($this->overrideOrderFields)) {
            $orderColumns = [];
            foreach ($data['order_column'] as $oc) {
                if (array_key_exists($oc, $this->overrideOrderFields)) {
                    $orderColumns[] = $this->overrideOrderFields[$oc];
                } else {
                    $orderColumns[] = $oc;
                }
            }
            $data['order_column'] = $orderColumns;
        }

        $strFilter = (request('strFilter')) ? request('strFilter') : [];
        if (!empty($f)) {
            foreach ($f as $item) {
                $data['f'][] = $item;
            }
        }
        if (!empty($strFilter)) {
            $data['strFilter'] = $strFilter;
        }
        if (empty($data['f'])) {
            unset($data['f']);
        }
        if (request('addSelectRaw')) {
            $data['addSelectRaw'] = request('addSelectRaw');
        }
        unset($data['s']);
        return $this->processQuery($query, $data)
            ->paginate(request('limit', 10));
    }

    /**
     * @throws BindingResolutionException
     */
    public function processQuery($query, $data)
    {
        $data = $this->processGlobalSearch($data);

        $v = validator()->make($data, [
            'order_column'         => 'sometimes|required|array|in:' . $this->orderableColumns(),
            'order_direction'      => 'sometimes|required|in:asc,desc',
            'limit'                => 'sometimes|required|integer|min:1',
            's'                    => 'sometimes|nullable|string',

            // advanced filter
            'filter_match'         => 'sometimes|required|in:and,or',
            'f'                    => 'sometimes|required|array',
            'f.*.column'           => 'required|in:' . $this->whiteListColumns(),
            'f.*.operator'         => 'required_with:f.*.column|in:' . $this->allowedOperators(),
            'f.*.query_1'          => 'required',
            'f.*.query_2'          => 'required_if:f.*.operator,between,not_between',
            'strFilter'            => 'sometimes|required|array',
            'strFilter.*.column'   => 'required|in:' . $this->whiteListColumns(),
            'strFilter.*.operator' => 'required_with:f.*.column|in:' . $this->allowedOperators(),
            'strFilter.*.query_1'  => 'required',
            'strFilter.*.query_2'  => 'required_if:f.*.operator,between,not_between',
            'addSelectRaw'         => 'sometimes|required|array',
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $data = $v->validated();

        return (new FilterQueryBuilder())->apply($query, $data);
    }

    protected function orderableColumns()
    {
        return implode(',', $this->orderable);
    }

    protected function whiteListColumns()
    {
        return implode(',', $this->filterable);
    }

    protected function allowedOperators()
    {
        return implode(',', [
            'contains', 'equals', 'in', 'orContains', 'between', 'date_range', 'check_bool', 'boolNotEqualsZero', 'scope'
        ]);
    }

    protected function processGlobalSearch($data)
    {
        return $data;
        if (isset($data['f']) || !isset($data['s'])) {
            return $data;
        }

        $data['filter_match'] = 'or';

        $data['f'] = array_map(function ($column) use ($data) {
            return [
                'column'   => $column,
                'operator' => 'contains',
                'query_1'  => $data['s'],
            ];
        }, $this->filterable);

        return $data;
    }
}
