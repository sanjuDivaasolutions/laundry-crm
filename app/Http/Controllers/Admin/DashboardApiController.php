<?php

namespace App\Http\Controllers\Admin;


use App\Services\DashboardService;

class DashboardApiController
{
    public function index()
    {
        return DashboardService::getModules();
    }

    public function fetchData()
    {
        $module = request('module');
        if (!$module) return ['data' => []];
        return DashboardService::fetchData($module);
    }

    public function cards()
    {
        $dashboardService = new DashboardService();
        $data = $dashboardService->getDashboardCardsData();
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Dashboard cards data retrieved successfully'
        ]);
    }
}
