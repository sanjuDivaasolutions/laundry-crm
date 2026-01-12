<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dashboardData = $this->dashboardService->getDashboardData();

        return new DashboardResource($dashboardData);
    }

    /**
     * Get dashboard cards data
     *
     * @return \Illuminate\Http\Response
     */
    public function cards(Request $request)
    {
        $data = $this->dashboardService->getDashboardCardsData();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Dashboard cards data retrieved successfully',
        ]);
    }
}
