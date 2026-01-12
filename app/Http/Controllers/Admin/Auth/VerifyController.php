<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;

class VerifyController extends Controller
{
    public function __invoke() {
        return okResponse(AuthService::getUserResponse());
    }
}
