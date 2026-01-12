<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;

class AbilitiesController extends Controller
{
    public function index()
    {
        $permissions = adminAuth()
            ->user()
            ->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('title')
            ->toArray();

        return okResponse($permissions);
    }
}
