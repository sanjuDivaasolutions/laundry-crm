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
 *  *  Last modified: 12/12/24, 11:21 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Modules\Dashboard;

//use App\Models\PettyCashExpense;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Services\DashboardService;

//use App\Models\Transaction;

class AdminSummaryModule
{
    public string $module = 'AdminSummaryModule';
    public string $title = 'Summary';
    public string $component = 'SummaryComponent';
    public int $columns = 12;
    public array $filters = [
        ['request' => 'f_company_id', 'field' => 'company_id', 'operator' => 'in'],
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
    ];

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
            'title'        => $this->title,
            'component'    => $this->component,
            'module'       => $this->module,
            'query'        => $this->getQuery(),
            'defaultQuery' => $this->getQuery(),
            'filters'      => $this->getFilters(),
            'data'         => $this->getData(),
            'columns'      => $this->columns,
        ];
    }

    public function getData(): array
    {
        $currencySign = '$';

        $filters = DashboardService::getQueryFilterQuery($this->filters);

        $salesTotal = $this->getSalesInvoiceTotal($filters);
        $purchaseTotal = $this->getPurchaseInvoiceTotal($filters);
        $expenseTotal = $this->getExpenseTotal($filters);

        $creditorsPayments = $this->getCreditorPayments($filters);
        $debtorsPayments = $this->getDebtorsPayments($filters);

        $creditorDue = $purchaseTotal - $creditorsPayments;
        $debtorsDue = $salesTotal - $debtorsPayments;

        $profitTotal = $salesTotal - ($purchaseTotal + $expenseTotal);

        $NegativeVariant = 'danger';
        $positiveVariant = 'primary';

        return [
            ['label' => 'Sales', 'value' => $currencySign . number_format($salesTotal, 2), 'variant' => $salesTotal < 0 ? $NegativeVariant : $positiveVariant],
            ['label' => 'Purchase', 'value' => $currencySign . number_format($purchaseTotal, 2), 'variant' => $purchaseTotal < 0 ? $NegativeVariant : $positiveVariant],
            ['label' => 'Expense', 'value' => $currencySign . number_format($expenseTotal, 2), 'variant' => $expenseTotal < 0 ? $NegativeVariant : $positiveVariant],
            ['label' => 'Profit', 'value' => $currencySign . number_format($profitTotal, 2), 'variant' => $profitTotal < 0 ? $NegativeVariant : $positiveVariant],
            ['label' => 'Creditor Due', 'value' => $currencySign . number_format($creditorDue, 2), 'variant' => $creditorDue < 0 ? $NegativeVariant : $positiveVariant],
            ['label' => 'Debtors Due', 'value' => $currencySign . number_format($debtorsDue, 2), 'variant' => $debtorsDue < 0 ? $NegativeVariant : $positiveVariant],
        ];
    }

    private function getFilters(): array
    {
        return [
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
                'label'    => 'Company',
                'name'     => 'company',
                'field'    => 'f_company_id',
                'endpoint' => 'companies',
                'value'    => null,
            ],
        ];
    }

    private function getQuery()
    {
        return [
            'f_department_id' => null,
            'f_date_range'    => null,
        ];
    }

    private function getSalesInvoiceTotal($filters)
    {
        $obj = SalesInvoice::query();
        $obj = DashboardService::applyFilters($obj, $filters);
        $obj->selectRaw('sum(sub_total / currency_rate) as converted_sub_total');
        $result = $obj->first();

        return $result->count() ? $result->converted_sub_total : 0;
    }

    private function getPurchaseInvoiceTotal($filters)
    {
        $obj = PurchaseInvoice::query();
        $obj = DashboardService::applyFilters($obj, $filters);
        $obj->selectRaw('sum(sub_total / currency_rate) as converted_sub_total');
        $result = $obj->first();

        return $result->count() ? $result->converted_sub_total : 0;
    }

    private function getDebtorsPayments($filters)
    {
        return 0;
        /*$removeFilter = ['department_id'];
        $transactionFilters = array_values(array_filter($filters, function ($filter) use ($removeFilter) {
            return !in_array($filter['column'], $removeFilter);
        }));
        $departmentFilter = array_values(array_filter($filters, function ($filter) use ($removeFilter) {
            return $filter['column'] == 'department_id';
        }));

        $obj = Transaction::query();
        $obj = DashboardService::applyFilters($obj, $transactionFilters);
        $obj->whereIn('type', ['so', 'si']);
        $obj->whereHas('account', function ($q) {
            $q->whereIn('account_type_id', [config('accounting.accountType.ids.sundryDebtors')]);
        });
        if (count($departmentFilter) == 1 && isset($departmentFilter[0]['query_1'])) {
            $obj->where(function ($q) use ($departmentFilter) {
                $q->orWhereHas('salesInvoice', function ($q) use ($departmentFilter) {
                    $q->where('department_id', $departmentFilter[0]['query_1']);
                });
                $q->orWhereHas('salesOrder', function ($q) use ($departmentFilter) {
                    $q->where('department_id', $departmentFilter[0]['query_1']);
                });
            });
        }
        $obj->selectRaw('sum(credit / currency_rate) as converted_credit_total, sum(debit / currency_rate) as converted_debit_total');
        $result = $obj->first();
        return $result->count() ? $result->converted_debit_total - $result->converted_credit_total : 0;*/
    }

    private function getCreditorPayments($filters)
    {
        return 0;
        /*$removeFilter = ['department_id'];
        $transactionFilters = array_values(array_filter($filters, function ($filter) use ($removeFilter) {
            return !in_array($filter['column'], $removeFilter);
        }));
        $departmentFilter = array_values(array_filter($filters, function ($filter) use ($removeFilter) {
            return $filter['column'] == 'department_id';
        }));

        $obj = Transaction::query();
        $obj = DashboardService::applyFilters($obj, $transactionFilters);
        $obj->whereIn('type', ['po', 'pi']);
        $obj->whereHas('account', function ($q) {
            $q->whereIn('account_type_id', [config('accounting.accountType.ids.sundryCreditors')]);
        });
        if (count($departmentFilter) == 1 && isset($departmentFilter[0]['query_1'])) {
            $obj->where(function ($q) use ($departmentFilter) {
                $q->orWhereHas('purchaseInvoice', function ($q) use ($departmentFilter) {
                    $q->where('department_id', $departmentFilter[0]['query_1']);
                });
                $q->orWhereHas('purchaseOrder', function ($q) use ($departmentFilter) {
                    $q->where('department_id', $departmentFilter[0]['query_1']);
                });
            });
        }
        $obj->selectRaw('sum(credit / currency_rate) as converted_credit_total, sum(debit / currency_rate) as converted_debit_total');
        $result = $obj->first();

        return $result->count() ? $result->converted_credit_total - $result->converted_debit_total : 0;*/
    }

    private function getExpenseTotal($filters)
    {
        /*$pce = PettyCashExpense::query();
        $pce = DashboardService::applyFilters($pce, $filters);
        dd($pce->distinct('account_id')->pluck('account_id')->toArray());*/

        return 0;

        /*$expenses = Transaction::query()
            ->where('type', 'petty')
            ->where('tran_type', 'dr')
            ->whereIn('account_id', function ($query) use ($filters) {
                $pce = PettyCashExpense::query();
                $pce = DashboardService::applyFilters($pce, $filters);
                $query->select('account_id')
                    ->fromSub($pce->distinct('account_id'), 'filtered_expenses');
            })
            ->selectRaw('sum(debit / currency_rate) as total')
            ->first();
        return $expenses->total ?? 0;*/
    }
}
