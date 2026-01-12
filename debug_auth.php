<?php

$user = App\Models\User::find(1);
if (!$user) {
    echo "User 1 not found!\n";
    exit;
}

// Logic from AdminAuthGates
$roles = App\Models\Role::with('permissions')->get();
echo "Roles found: " . $roles->count() . "\n";

$permissionsArray = [];
foreach ($roles as $role) {
    foreach ($role->permissions as $permissions) {
        $permissionsArray[$permissions->title][] = $role->id;
    }
}

// Check role_access
$roleAccessRoles = $permissionsArray['role_access'] ?? [];
echo "Roles with role_access: " . implode(',', $roleAccessRoles) . "\n";
echo "User roles: " . implode(',', $user->roles->pluck('id')->toArray()) . "\n";

$allowed = count(array_intersect($user->roles->pluck('id')->toArray(), $roleAccessRoles)) > 0;
echo "Allowed: " . ($allowed ? 'YES' : 'NO') . "\n";
