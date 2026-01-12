<?php

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

if (!function_exists('adminAuth')) {
    function adminAuth(): \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Auth\Factory
    {
        //return auth(config('system.auth.admin','admin'));
        return Auth::guard(config('system.auth.admin', 'admin'));
    }
}
if (!function_exists('getUserId')) {
    function getUserId()
    {
        return adminAuth()->id();
    }
}
//get setting key value
if (!function_exists('getUserSetting')) {
    function getUserSetting($key, $default = null)
    {
        $user = adminAuth()->user();
        return $user->settings[$key] ?? $default;
    }
}
