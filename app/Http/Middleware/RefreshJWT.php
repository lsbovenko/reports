<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;

/**
 * Class RefreshJWT
 * @package App\Http\Middleware
 */
class RefreshJWT
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
        $user = $request->user();
        if (!$user) {
            return redirect(config('app.auth_url'));
        }

        // обновляем токен как только сессия истекла
        if (!$request->session()->get('jwt_updated')) {
            App::make('jwt_service')->createTokenFromUser($user);
            $request->session()->put('jwt_updated', '1');
        }

        return $next($request);
    }
}
