<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AnnouncementApiController
 *
 * Admin controller for managing system-wide announcements.
 */
class AnnouncementApiController extends Controller
{
    /**
     * List all announcements.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage-announcements');

        $query = Announcement::query()
            ->with('creator')
            ->orderBy('created_at', 'desc');

        if ($request->has('active_only')) {
            $query->active();
        }

        $announcements = $query->paginate($request->input('per_page', 15));

        return response()->json($announcements);
    }

    /**
     * Create a new announcement.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage-announcements');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'type' => ['required', 'in:info,warning,maintenance,feature,promotion'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_dismissible' => ['boolean'],
            'send_email' => ['boolean'],
            'email_target' => ['nullable', 'in:admin_only,all_users'],
            'target' => ['required', 'in:all,specific_tenants,specific_plans'],
            'target_ids' => ['nullable', 'array'],
            'action_url' => ['nullable', 'url'],
            'action_text' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $validated['created_by'] = auth()->id();

        $announcement = Announcement::create($validated);

        // TODO: If send_email is true, dispatch email job

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement,
        ], 201);
    }

    /**
     * Get a single announcement.
     */
    public function show(Announcement $announcement): JsonResponse
    {
        $this->authorize('manage-announcements');

        return response()->json([
            'announcement' => $announcement->load('creator'),
        ]);
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $this->authorize('manage-announcements');

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'content' => ['sometimes', 'string'],
            'type' => ['sometimes', 'in:info,warning,maintenance,feature,promotion'],
            'priority' => ['sometimes', 'in:low,normal,high,urgent'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_dismissible' => ['boolean'],
            'send_email' => ['boolean'],
            'email_target' => ['nullable', 'in:admin_only,all_users'],
            'target' => ['sometimes', 'in:all,specific_tenants,specific_plans'],
            'target_ids' => ['nullable', 'array'],
            'action_url' => ['nullable', 'url'],
            'action_text' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $announcement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully.',
            'announcement' => $announcement->fresh(),
        ]);
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->authorize('manage-announcements');

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }
}
