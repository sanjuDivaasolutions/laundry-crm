<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ActivityLogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_if(Gate::denies('activity_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Activity::with('causer')
            ->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->input('causer_id'))
                ->where('causer_type', 'App\\Models\\User');
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'App\\Models\\'.$request->input('subject_type'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $activities = $query->paginate($request->input('per_page', 25));

        return $this->success(ActivityResource::collection($activities));
    }

    public function show(int $id): JsonResponse
    {
        abort_if(Gate::denies('activity_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $activity = Activity::with('causer')->findOrFail($id);

        return $this->success(new ActivityResource($activity));
    }
}
