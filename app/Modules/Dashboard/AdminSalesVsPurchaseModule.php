<?php

namespace App\Modules\Dashboard;

use App\Models\Payment;
use App\Models\PettyCashExpense;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\Transaction;
use App\Services\DashboardService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSalesVsPurchaseModule
{
    public string $module = 'AdminSalesVsPurchaseModule';
    public string $title = 'Sales vs Purchase';
    public string $component = 'BarChartComponent';
    public int $columns = 12;
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
        $f_date_range_type = request('f_date_range_type', 'monthly');

        //values for $f_date_range_type can be yearly,monthly, weekly, daily
        switch ($f_date_range_type) {
            case 'monthly':
                return $this->getMonthlyData();
            case 'yearly':
                return $this->getYearlyData();
            case 'weekly':
                return $this->getWeeklyData();
        }
    }

    private function getMonthlyData()
    {
        $f_department_id = request('f_department_id', '');
        if ($f_department_id) {
            $f_department_id = explode(',', $f_department_id);
        }

        $months = collect(range(0, 11))->map(function ($month) {
            return now()->subMonths($month)->startOfMonth()->format('Y-m');
        });

        $categories = $months->map(function ($month) {
            return Carbon::parse($month)->format('M y');
        });

        $sales = DB::table(DB::raw('(' . $months->map(function ($month) {
                return "SELECT '" . $month . "' AS month";
            })->implode(' UNION ALL ') . ') as months'))
            ->leftJoin('sales_invoices', function ($join) {
                $join->on(DB::raw("DATE_FORMAT(sales_invoices.date, '%Y-%m')"), '=', 'months.month');
            })
            ->select(
                'months.month',
                DB::raw('COALESCE(SUM(sales_invoices.sub_total / sales_invoices.currency_rate), 0) as total_sales'),
            )
            ->groupBy('months.month')
            ->orderBy('months.month', 'DESC');
        if ($f_department_id) {
            $sales->whereIn('department_id', $f_department_id);
        }
        $sales = $sales->get()->map(function ($item) {
            return round($item->total_sales, 2);
        });

        $purchases = DB::table(DB::raw('(' . $months->map(function ($month) {
                return "SELECT '" . $month . "' AS month";
            })->implode(' UNION ALL ') . ') as months'))
            ->leftJoin('purchase_invoices', function ($join) {
                $join->on(DB::raw("DATE_FORMAT(purchase_invoices.date, '%Y-%m')"), '=', 'months.month');
            })
            ->select(
                'months.month',
                DB::raw('COALESCE(SUM(purchase_invoices.sub_total / purchase_invoices.currency_rate), 0) as total_purchases'),
            )
            ->groupBy('months.month')
            ->orderBy('months.month', 'DESC');
        if ($f_department_id) {
            $purchases->whereIn('department_id', $f_department_id);
        }
        $purchases = $purchases->get()->map(function ($item) {
            return round($item->total_purchases, 2);
        });

        return [
            'categories' => $categories,
            'series'     => [
                [
                    'name' => 'Sales',
                    'data' => $sales
                ],
                [
                    'name' => 'Purchases',
                    'data' => $purchases
                ]
            ],
        ];
    }

    private function getYearlyData()
    {
        $categories = [];
        $sales = [];
        $purchases = [];

        return [
            'categories' => $categories,
            'series'     => [
                [
                    'name' => 'Sales',
                    'data' => $sales
                ],
                [
                    'name' => 'Purchases',
                    'data' => $purchases
                ]
            ],
        ];
    }

    private function getWeeklyData()
    {
        $categories = [];
        $sales = [];
        $purchases = [];

        return [
            'categories' => $categories,
            'series'     => [
                [
                    'name' => 'Sales',
                    'data' => $sales
                ],
                [
                    'name' => 'Purchases',
                    'data' => $purchases
                ]
            ],
        ];
    }

    private function getFilters(): array
    {
        /*
         * {
            outside: true,
            type: "group-button",
            label: "Range",
            name: "f_date_range_type",
            field: "f_date_range_type",
            config: {
                buttons: [
                    { label: "Yearly", value: "yearly" },
                    { label: "Monthly", value: "monthly" },
                    { label: "Weekly", value: "weekly" },
                ],
            },
            value: "monthly",
        },
         */
        return [
            /*[
                'outside' => true,
                'type'    => 'group-button',
                'label'   => 'Range',
                'name'    => 'f_date_range_type',
                'field'   => 'f_date_range_type',
                'config'  => [
                    'buttons' => [
                        ['label' => 'Yearly', 'value' => 'yearly'],
                        ['label' => 'Monthly', 'value' => 'monthly'],
                        ['label' => 'Weekly', 'value' => 'weekly'],
                    ],
                ],
                'value'   => 'monthly',
            ],*/
            [
                'outside'  => true,
                'type'     => 'select-single',
                'label'    => 'Department',
                'name'     => 'department',
                'field'    => 'f_department_id',
                'endpoint' => 'departments',
                'value'    => null,
            ],
        ];
    }

    private function getQuery()
    {
        return [
            'f_department_id'   => null,
            'f_date_range_type' => 'monthly',
        ];
    }
}
