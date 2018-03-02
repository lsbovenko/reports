<?php

namespace App\Service;

use App\Models\Auth\{
    User,
    Role
};
use Illuminate\Support\Facades\Log;

/**
 * Class UserCreator
 * @package App\Service
 */
class UserCreator
{
    const DEFAULT_ROLE = 'user';
    const SERVICE_NAME = 'report';

    /**
     * @param array $payload
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createFromJwt(array $payload)
    {
        $userData = $payload['user'];
        $userData['email'] = $payload['sub'];
        if (empty($userData)) {
            throw new \Exception('Payload is empty');
        }

        try {
            $role = $this->getRole($userData);
            $userData['is_revenue_required'] = $this->isNeedRevenue($userData);
            $user = User::create($userData);
            $user->attachRole($role);
        } catch (\Exception $exception) {
            Log::error($exception);
            throw $exception;
        }

        return $user;
    }

    /**
     * @param array $userData
     * @return $this|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function getRole(array $userData)
    {
        $roleName = $userData['roles'][self::SERVICE_NAME] ?? self::DEFAULT_ROLE;
        $role = Role::where('name', '=', $roleName)->get()->first();

        if (!$role) {
            $role = Role::create(['name' => $roleName]);
        }

        return $role;
    }


    /**
     * @param array $userData
     * @return bool
     */
    private function isNeedRevenue(array $userData): bool
    {
        $departmentsWithoutRevenue = [
            'Маркетинга и продаж',
            'Кадров',
        ];

        return !in_array($userData['department'], $departmentsWithoutRevenue);
    }
}
