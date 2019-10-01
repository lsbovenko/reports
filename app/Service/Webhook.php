<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 25.08.2017
 * Time: 17:07
 */

namespace App\Service;

use App\Models\Auth\User;
use Illuminate\Encryption\Encrypter;

class Webhook
{
    protected $client;

    protected $encrypter;

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function receive(string $data)
    {
        $userData = $this->encrypter->decrypt($data);

        $user = User::where('email', $userData['email'])->first();

        if (null !== $user) {
            foreach ($user->getFillable() as $attribute) {
                if (isset($userData[$attribute])) {
                    $user->{$attribute} = $userData[$attribute];
                }
            }

            $user->save();
        }
    }

    private function config($key, $default = null)
    {
        return config('app.webhook.' . config('app.env') . '.' . $key, $default);
    }
}
