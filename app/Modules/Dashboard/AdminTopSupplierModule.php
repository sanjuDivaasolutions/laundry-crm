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
 *  *  Last modified: 12/12/24, 9:55 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Modules\Dashboard;

use App\Models\PurchaseInvoice;

class AdminTopSupplierModule
{
    public string $module = 'AdminTopSupplierModule';

    public string $title = 'Top 5 Suppliers';

    public string $component = 'BasicTableComponent';

    public int $columns = 4;

    public array $filters = [];

    public function __construct($params = [])
    {
        if ($params) {
            $this->title = $params['title'] ?? $this->title;
            $this->component = $params['component'] ?? $this->component;
            $this->columns = $params['columns'] ?? $this->columns;
        }
    }

    public function get(): array
    {
        return [
            'title' => $this->title,
            'component' => $this->component,
            'module' => $this->module,
            'query' => $this->getQuery(),
            'defaultQuery' => $this->getQuery(),
            'filters' => $this->getFilters(),
            'data' => $this->getData(),
            'columns' => $this->columns,
        ];
    }

    public function getData(): array
    {
        $currencySign = '$';

        $columns = $this->getColumns();

        $topFiveSuppliers = PurchaseInvoice::query()
            ->selectRaw('sum(sub_total / currency_rate) as converted_sub_total, supplier_id')
            ->whereHas('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('converted_sub_total')
            ->limit(5)
            ->get();

        $data = [];
        foreach ($topFiveSuppliers as $s) {
            $data[] = [
                'name' => $s->supplier->name,
                'amount' => $currencySign.number_format($s->converted_sub_total, 2),
            ];
        }

        return ['columns' => $columns, 'data' => $data];
    }

    private function getFilters(): array
    {
        return [];
        /*return [
            [
                'outside' => true,
                'type'    => 'date-range',
                'label'   => 'Date Range',
                'name'    => 'f_date_range',
                'field'   => 'f_date_range',
                'value'   => null,
            ],
            [
                'outside'  => true,
                'type'     => 'select-single',
                'label'    => 'Department',
                'name'     => 'department',
                'field'    => 'f_department_id',
                'endpoint' => 'departments',
                'value'    => null,
            ],
        ];*/
    }

    private function getQuery(): array
    {
        return [];
        /*return [
            'f_department_id' => null,
            'f_date_range'    => null,
        ];*/
    }

    private function getColumns(): array
    {
        return [
            [
                'label' => 'Name',
                'value' => 'name',
            ],
            [
                'label' => 'Amount',
                'value' => 'amount',
                'class' => 'text-end',
                'thClass' => '',
                'tdClass' => 'text-info fw-bold',
            ],
        ];
    }
}
