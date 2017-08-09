<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\{
    Auth,
    App,
    Log
};
use App\Models\Auth\User;

/**
 * Class CheckUser
 * @package App\Http\Middleware
 */
class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ((int)($request->session()->get('jwt_check_user_time')) < time()) {
            $this->check($request->user());
            $request->session()->put('jwt_check_user_time', time() + config('jwt.time_check_user') * 60);
        }

        return $next($request);
    }

    /**
     * @param $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function check(User $user)
    {
        try {
            $remoteUser = App::make('auth.api.client')->getUser($user->email);
        } catch (\Exception $e) {
            Log::error($e);
            $this->logout();
        }

        if (!$remoteUser['is_active']) {
            $user->is_active = 0;
            $user->save();
            $this->logout();
        }

        $this->updateUser($user, $remoteUser);

    }

    /**
     * @param User $user
     * @param array $remoteUser
     */
    protected function updateUser(User $user, array $remoteUser)
    {
        $isNeedUpdate = false;

        if ($user->name != $remoteUser['name']) {
            $isNeedUpdate = true;
            $user->name = $remoteUser['name'];
        }

        if ($user->last_name != $remoteUser['last_name']) {
            $isNeedUpdate = true;
            $user->last_name = $remoteUser['last_name'];
        }

        if ($isNeedUpdate) {
            $user->save();
        }

        $role = App::make('user_creator')->getRole($remoteUser);

        if ($role->name != $user->roles()->get()->first()->name) {
            $user->detachRoles();
            $user->attachRole($role);
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function logout()
    {
        Auth::logout();
        return redirect(config('app.auth_url'));
    }
}
