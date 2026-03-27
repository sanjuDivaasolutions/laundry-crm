<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class AdminAuthGates
{
    public function handle($request, Closure $next)
    {
        $user = adminAuth()->user();

        if (! $user) {
            return $next($request);
        }

        $permissionsArray = Cache::remember('admin_permission_role_map', 300, function () {
            $roles = Role::with('permissions')->get();
            $map = [];
            $excludePermissions = config('auth.protected_permissions', []);

            foreach ($roles as $role) {
                foreach ($role->permissions as $permission) {
                    if (! in_array($permission->title, $excludePermissions)) {
                        $map[$permission->title][] = $role->id;
                    }
                }
            }

            return $map;
        });

        foreach ($permissionsArray as $title => $roles) {
            Gate::define($title, function (User $user) use ($roles) {
                return count(array_intersect($user->roles->pluck('id')->toArray(), $roles)) > 0;
            });
        }

        return $next($request);
    }
}
