<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtAdminMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = adminAuth()->user();

            /*if(!$user) {
                adminAuth()->refresh();
                $user = adminAuth()->user();
            }*/

            // $user = null;
            if ($user) {
                $id = $user->id;
                if (! $id) {
                    return $this->tokenResponse('Session has been expired');
                }
                if ($user->active == 0) {
                    return $this->tokenResponse('User is not active');
                }

                return $next($request);
            }

            return $this->tokenResponse('Session has been expired');
        } catch (Exception $e) {
            return $this->tokenResponse($e->getMessage());
        }
    }

    private function tokenResponse($message)
    {
        return response()->json($message, 401);
    }
}
