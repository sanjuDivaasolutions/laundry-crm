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
 *  *  Last modified: 07/01/25, 4:30 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

trait SearchFilters
{
    public function __construct()
    {
        if (isset($this->filterMethods) && count($this->filterMethods)) {
            if (Route::current()) {
                $method = Route::current()->getActionMethod();
                if (in_array($method, $this->filterMethods)) {
                    $this->setupSearch();
                }
            }
        }
    }

    protected function setupSearch()
    {
        if (isset($this->fields)) {
            $this->prepStringSearch($this->fields);
        }
        if (isset($this->filters)) {
            $this->prepFilters($this->filters);
        }
    }

    protected function prepStringSearch($strSearchFields)
    {

        if ($strSearchFields) {
            $strFilter = [];
            if (request('s')) {
                $strings = explode(',', request('s'));
                foreach ($strings as $string) {
                    foreach ($strSearchFields as $field) {
                        $trimmed = trim($string);
                        if ($trimmed) {
                            $strFilter[] = ['column' => $field, 'operator' => 'orContains', 'query_1' => $trimmed];
                        }
                    }
                }
            }
            request()->merge(['strFilter' => $strFilter]);
        }

    }

    protected function prepFilters($data)
    {

        if ($data) {
            $filter = [];
            foreach ($data as $d) {
                $request = request($d['request']);
                if (! is_null($request)) {
                    $operator = $d['operator'] ?? 'in';
                    if ($operator == 'date_range') {
                        $range = is_array($request) ? $request : explode($d['separator'], request($d['request'], []));

                        $to_string = ($range[1]) ?? $range[0];

                        $filter_date_from = ($range[0]) ? Carbon::createFromFormat(config('project.date_format'), $range[0]) : null;
                        $filter_date_to = ($to_string) ? Carbon::createFromFormat(config('project.date_format'), $to_string) : null;
                        if ($filter_date_from && $filter_date_to) {
                            $filter_date_from->setTime(0, 0);
                            $filter_date_to->setTime(23, 59, 59);
                            $filter[] = ['column' => $d['field'], 'operator' => $operator, 'query_1' => $filter_date_from, 'query_2' => $filter_date_to];
                        }
                    } elseif ($operator == 'check_bool') {
                        if ($request == 'true') {
                            $filter[] = ['column' => $d['field'], 'operator' => 'in', 'query_1' => $d['collection']];
                        }
                    } elseif ($operator == 'scope') {
                        if ($request == 'true') {
                            $fields = explode(',', $d['field']);
                            $filter[] = ['column' => 'id', 'operator' => 'scope', 'query_1' => $fields];
                        }
                    } else {
                        $ids = explode(',', $request);
                        if ($ids) {
                            $filter[] = ['column' => $d['field'], 'operator' => $operator, 'query_1' => $ids];
                        }
                    }
                }
            }
            request()->merge(['f' => $filter]);
        }
    }
}
