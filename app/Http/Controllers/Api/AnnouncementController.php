<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AnnouncementController
 *
 * Handles announcements for the current tenant's users.
 */
class AnnouncementController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Get active announcements for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();
        $user = $request->user();

        if (! $tenant || ! $user) {
            return $this->success(['announcements' => []]);
        }

        $announcements = Announcement::getForTenantUser($tenant, $user);

        return $this->success([
            'announcements' => $announcements->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'type' => $a->type,
                'type_config' => $a->getTypeConfig(),
                'priority' => $a->priority,
                'is_dismissible' => $a->is_dismissible,
                'action_url' => $a->action_url,
                'action_text' => $a->action_text,
                'starts_at' => $a->starts_at?->toIso8601String(),
                'ends_at' => $a->ends_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Dismiss an announcement for the current user.
     */
    public function dismiss(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->error('Unauthenticated.', 401);
        }

        $announcement->dismissFor($user);

        return $this->success(null, 'Announcement dismissed.');
    }
}
