<?php

namespace App\Service;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use App\Models\Auth\User;
use Illuminate\Support\Facades\{
    Auth,
    App
};

/**
 * Class JWTService
 * @package App\Service
 */
class JWTService
{
    /**
     * @var []
     */
    protected $oldCustomClaim;

    /**
     * @param User $user
     * @return mixed
     */
    public function createTokenFromUser(User $user)
    {
        if (JWTAuth::getToken()) {
            $this->oldCustomClaim = JWTAuth::getPayload()->toArray()['user'];
        } else {
            throw new \Exception('User needs authorize!');
        }
        $token = JWTAuth::fromUser($user);

        if ($user) {
            $this->authenticateUser($user);
        }

        return $this->saveToken();
    }

    /**
     *
     */
    public function authenticateFromRequest()
    {
        if (!JWTAuth::getToken() && isset($_COOKIE[config('jwt.cookie_name')])) {
            JWTAuth::setToken($_COOKIE[config('jwt.cookie_name')]);
        }

        if (JWTAuth::getToken()) {
            $user = $this->getUserFromToken();
            $this->authenticateUser($user);
        }
    }

    /**
     * @param User $user
     * @return $this
     */
    protected function authenticateUser(User $user)
    {
        Auth::login($user);
        event('tymon.jwt.valid', $user);
        //JWTAuth::authenticate();// this not working if identifier is email

        return $this;
    }

    /**
     * return void
     */
    public function clearToken()
    {
        $this->setCookie();
    }

    /**
     * @return mixed
     */
    protected function saveToken()
    {
        $payload = JWTFactory::make($this->oldCustomClaim);
        $token = JWTAuth::encode($payload);
        $token = $token->get();

        $this->setCookie($token);

        return $token;
    }

    /**
     * @param string $token
     */
    protected function setCookie(string $token = '')
    {
        setcookie(config('jwt.cookie_name'), $token, time() + config('jwt.ttl') * 60, '/', config('app.main_domain'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    protected function getUserFromToken()
    {
        $payload = JWTAuth::getPayload()->toArray();
        $email = $payload['sub'];
        $user = User::where(compact('email'))->first();

        if (!$user) {
            $user = App::make('user_creator')->createFromJwt($payload);
        }

        return $user;
    }
}