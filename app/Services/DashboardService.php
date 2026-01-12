<?php

namespace App\Services;

use App\Models\Role;
use App\Models\SaleOrder;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Traits\SearchFilters;
use Illuminate\Support\Carbon;

class DashboardService
{
    use SearchFilters;

    public static function getModules()
    {
        $userId = auth()->id();
        $role = Role::whereHas('users', function ($q) use ($userId) {
            $q->where('id', $userId);
        })->first();

        if (!$role) {
            return [];
        }

        $roleId = $role->id;
        $configModules = config("dashboard.modules.$roleId");

        if (!$configModules) {
            return [];
        }

        $defaultModuleValues = self::getDefaultModuleValues();

        $modules = [];
        $moduleId = 1;
        foreach ($configModules as $cm) {
            $module = $cm['module'];
            $columns = $cm['columns'];
            $moduleClass = "App\\Modules\\Dashboard\\$module";
            $m = new $moduleClass(['columns' => $columns]);
            $moduleData = $m->get();
            $moduleData['id'] = $moduleId++;
            $modules[] = array_merge($defaultModuleValues, $moduleData);
        }

        return $modules;
    }

    public static function fetchData($module)
    {
        $moduleClass = "App\\Modules\\Dashboard\\$module";
        return ['data' => (new $moduleClass)->getData()];
    }

    private static function getDefaultModuleValues()
    {
        return [
            'isLoaded'     => false,
            'filters'      => [],
            'query'        => [],
            'defaultQuery' => [],
            'data'         => [],
        ];
    }

    public static function getQueryFilterQuery($filters, $strFields = [])
    {
        $requestFilters = [];
        $obj = new self;
        if ($strFields) {
            $obj->prepStringSearch($strFields);
            $requestFilters['s'] = request('s');
        }

        if ($filters) {
            $obj->prepFilters($filters);
            $requestFilters = array_merge($requestFilters, request('f'));
        }
        return $requestFilters;
    }

    public static function applyFilters($obj, $filters)
    {
        foreach ($filters as $filter) {
            $column = $filter['column'];
            $operator = $filter['operator'];
            $query_1 = $filter['query_1'];
            $query_2 = $filter['query_2'] ?? null;

            if ($operator == 'in') {
                $obj->whereIn($column, $query_1);
            } elseif ($operator == 'date_range') {
                $obj->whereBetween($column, [$query_1, $query_2]);
            }
        }

        return $obj;
    }

    public static function getFilters()
    {
        $date = request()->get('f_date_range', Carbon::now()->startOfMonth()->format('m/d/Y') . ' to ' . Carbon::now()->endOfMonth()->format('m/d/Y'));
        $date = explode(' to ', $date);
        $date = [
            'start' => Carbon::createFromFormat('m/d/Y', $date[0])->setTime(0, 0),
            'end'   => Carbon::createFromFormat('m/d/Y', $date[1])->setTime(23, 59, 59),
        ];

        $departmentId = request()->get('f_department_id', []);

        $filters = [
            'date' => $date,
        ];
        if ($departmentId) {
            $filters['department_id'] = explode(',', $departmentId);
        }

        return $filters;
    }

    public function getDashboardCardsData()
    {
        $today = Carbon::today();
        $companyId = request()->header('Company-Id') ?? auth()->user()->company_id ?? null;

        return [
            'today_sales' => $this->getTodaySales($today, $companyId),
            'today_purchase' => $this->getTodayPurchase($today, $companyId),
            'today_sales_invoices' => $this->getTodaySalesInvoices($today, $companyId),
            'today_purchase_invoices' => $this->getTodayPurchaseInvoices($today, $companyId),
            'low_stock_items' => $this->getLowStockItems($companyId),
            'pending_quotations' => $this->getPendingQuotations($companyId),
        ];
    }

    private function getTodaySales($today, $companyId)
    {
        return SaleOrder::whereDate('created_at', $today)
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->sum('total_amount');
    }

    private function getTodayPurchase($today, $companyId)
    {
        return PurchaseOrder::whereDate('created_at', $today)
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->sum('total_amount');
    }

    private function getTodaySalesInvoices($today, $companyId)
    {
        $invoices = SalesInvoice::whereDate('created_at', $today)
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            });

        return [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
        ];
    }

    private function getTodayPurchaseInvoices($today, $companyId)
    {
        $invoices = PurchaseInvoice::whereDate('created_at', $today)
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            });

        return [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
        ];
    }

    private function getLowStockItems($companyId)
    {
        return Product::whereColumn('current_stock', '<=', 'reorder_level')
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->select('id', 'name', 'sku', 'current_stock', 'reorder_level', 'unit')
            ->get()
            ->toArray();
    }

    private function getPendingQuotations($companyId)
    {
        return Quotation::where('status', 'pending')
            ->when($companyId, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->count();
    }
}
