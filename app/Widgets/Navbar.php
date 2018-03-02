<?php

namespace App\Widgets;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Spatie\Menu\Laravel\MenuFacade as Menu;
use App\Models\Auth\Role;

/**
 * Class Navbar
 * @package App\Widgets
 */
class Navbar
{

    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        Menu::macro('main', function () {
            $menu = Menu::new()
                ->addClass('nav navbar-nav');
            $user = Auth::user();
            if (isset($user)) {
                $menu
                    ->route('main', 'Новый отчёт')
                    ->route('statistics.index', 'Статистика');

                if ($user->hasRole(Role::ROLE_SUPERADMIN) || $user->hasRole(Role::ROLE_ADMIN)) {
                    $menu
                        ->route('projects.index', 'Проекты')
                        ->route('hours.index', 'Часы')
                        ->route('revenues.index', 'Доход');
                }
            }

            $menu->setActive(\url()->current());

            return $menu;
        });
    }
}
