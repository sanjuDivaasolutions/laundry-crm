<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function modules()
    {
        $modules = [
            [
                'id' => 'system_overview',
                'module' => 'system_overview',
                'component' => 'SummaryComponent',
                'columns' => 12,
                'title' => 'System Overview',
                'isLoading' => false,
                'data' => $this->getSystemOverviewData(),
                'filters' => [], // No filters for now
                'query' => [],
            ],
            [
                'id' => 'quick_links',
                'module' => 'quick_links',
                'component' => 'SummaryComponent', // Reusing summary for links check
                'columns' => 6,
                'title' => 'Quick Stats',
                'isLoading' => false,
                'data' => [
                    [
                        'id' => '1',
                        'label' => 'Active Tenants',
                        'value' => '1', // Hardcoded for now until Tenant model is fully integrated into this view if needed
                        'variant' => 'success',
                    ]
                ],
                'filters' => [],
                'query' => [],
            ]
        ];

        return response()->json($modules);
    }

    public function fetchData(Request $request)
    {
        $module = $request->get('module');

        $data = [];
        if ($module === 'system_overview') {
            $data = $this->getSystemOverviewData();
        }

        return okResponse([
            'data' => $data,
        ]);
    }

    private function getSystemOverviewData()
    {
        $userCount = User::count();
        $roleCount = Role::count();
        $companyCount = Company::count();

        return [
            [
                'id' => 'users',
                'label' => 'Total Users',
                'value' => (string)$userCount,
                'variant' => 'primary',
            ],
            [
                'id' => 'roles',
                'label' => 'Total Roles',
                'value' => (string)$roleCount,
                'variant' => 'info',
            ],
            [
                'id' => 'companies',
                'label' => 'Total Companies',
                'value' => (string)$companyCount,
                'variant' => 'warning',
            ],
        ];
    }
}
