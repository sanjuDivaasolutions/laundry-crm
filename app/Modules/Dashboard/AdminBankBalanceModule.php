<?php

namespace App\Modules\Dashboard;

use App\Models\Account;

class AdminBankBalanceModule
{
    public string $module = 'AdminBankBalanceModule';

    public string $title = 'Bank Balances';

    public string $component = 'BasicTableComponent';

    public int $limit = 5;

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

        $result = Account::query()
            ->selectRaw('balance,id,name,account_type_id,currency_id')
            ->with(['currency'])
            ->whereIn('account_type_id', [config('accounting.accountType.ids.bank')])
            ->orderByDesc('balance')
            ->limit($this->limit)
            ->get();

        $data = [];
        foreach ($result as $s) {
            $currencySign = $s->currency?->symbol ?? '$';
            $data[] = [
                'name' => $s->name,
                'amount' => $currencySign.number_format($s->balance, 2),
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

    private function getQuery()
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
