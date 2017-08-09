<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\{
    App,
    Auth
};

/**
 * Class AuthorizeByJWT
 * @package App\Http\Middleware
 */
class AuthorizeByJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            App::make('jwt_service')->authenticateFromRequest();
        } catch (\Exception $e) {
            Auth::logout();
            return redirect(config('app.auth_url'));
        }

        return $next($request);
    }
}
