<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use App\Models\Auth\User;

class UpdateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->getRemoteUserRepository()->getAll() as $user) {
            $userModel = User::where('email', '=', $user['email'])->first();
            if ($userModel && $this->isNeedRevenue($user)) {
                $userModel->is_revenue_required = 1;
                $userModel->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * @param array $user
     * @return bool
     */
    private function isNeedRevenue(array $user): bool
    {
        $departmentsWithoutRevenue = [
            'Маркетинга и продаж',
            'Кадров',
        ];

        return !in_array($user['department'], $departmentsWithoutRevenue);
    }

    /**
     * @return \App\Repositories\RemoteUser;
     */
    private function getRemoteUserRepository()
    {
        return App::make('repository.remote_user');
    }
}
