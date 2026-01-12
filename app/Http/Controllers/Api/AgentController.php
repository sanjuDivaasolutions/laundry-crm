<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AgentStoreRequest;
use App\Http\Requests\API\AgentUpdateRequest;
use App\Http\Resources\API\AgentResource;
use App\Models\Agent;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AgentController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $agents = Agent::with(['user'])
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->when($request->has('commission_type'), function ($query) use ($request) {
                $query->where('commission_type', $request->get('commission_type'));
            })
            ->advancedFilter();

        return AgentResource::collection($agents);
    }

    public function store(AgentStoreRequest $request): JsonResource
    {
        $agent = Agent::create($request->validated());

        return new AgentResource($agent->load('user'));
    }

    public function show(Agent $agent): JsonResource
    {
        $agent->load(['user', 'commissions' => function ($query) {
            $query->with(['commissionable', 'approvedBy', 'paidBy'])
                ->latest();
        }]);

        return new AgentResource($agent);
    }

    public function update(AgentUpdateRequest $request, Agent $agent): JsonResource
    {
        $agent->update($request->validated());

        return new AgentResource($agent->load('user'));
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $agent->delete();

        return response()->json(['message' => 'Agent deleted successfully']);
    }

    public function getCommissionSummary(Agent $agent, Request $request): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $commissions = $agent->commissions()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('commission_date', [$startDate, $endDate]);
            })
            ->get();

        $summary = [
            'total_commissions' => $commissions->count(),
            'total_amount' => $commissions->sum('commission_amount'),
            'pending_count' => $commissions->where('status', 'pending')->count(),
            'pending_amount' => $commissions->where('status', 'pending')->sum('commission_amount'),
            'approved_count' => $commissions->where('status', 'approved')->count(),
            'approved_amount' => $commissions->where('status', 'approved')->sum('commission_amount'),
            'paid_count' => $commissions->where('status', 'paid')->count(),
            'paid_amount' => $commissions->where('status', 'paid')->sum('commission_amount'),
        ];

        return response()->json($summary);
    }

    public function approveCommissions(Request $request): JsonResponse
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:agent_commissions,id',
        ]);

        $orderService = new OrderService();
        $approvedCount = $orderService->approveCommissions($request->get('commission_ids'));

        return response()->json([
            'message' => "Successfully approved {$approvedCount} commissions",
            'approved_count' => $approvedCount,
        ]);
    }

    public function markCommissionsAsPaid(Request $request): JsonResponse
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:agent_commissions,id',
        ]);

        $orderService = new OrderService();
        $paidCount = $orderService->markCommissionsAsPaid($request->get('commission_ids'));

        return response()->json([
            'message' => "Successfully marked {$paidCount} commissions as paid",
            'paid_count' => $paidCount,
        ]);
    }
}