<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');
        $credentials['active'] = 1;

        $token = auth('admin')->attempt($credentials);
        if (!$token) {
            return errorResponse('Invalid Email or Password', 401);
        }
        return okResponse(AuthService::getUserResponse());
    }
}
