<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth as AuthFacad;

/**
 * Class CheckActiveUser
 * @package App\Http\Middleware
 */
class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user() || !JWTAuth::getToken()) {
            AuthFacad::logout();
            return redirect(config('app.auth_url'));
        }

        return $next($request);
    }
}
