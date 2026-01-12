<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Gate;

class AdminAuthGates
{
    public function handle($request, Closure $next)
    {
        $user = adminAuth()->user();

        if (!$user) {
            return $next($request);
        }

        $roles            = Role::with('permissions')->get();
        $permissionsArray = [];
        $excludePermissions = config('auth.protected_permissions', []);

        foreach ($roles as $role) {
            foreach ($role->permissions as $permissions) {
                if(!in_array($permissions->title, $excludePermissions)) {
                    $permissionsArray[$permissions->title][] = $role->id;
                }
            }
        }

        foreach ($permissionsArray as $title => $roles) {
            Gate::define($title, function (User $user) use ($roles) {
                return count(array_intersect($user->roles->pluck('id')->toArray(), $roles)) > 0;
            });
        }

        return $next($request);
    }
}
