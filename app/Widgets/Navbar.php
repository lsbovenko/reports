<?php

namespace App\Widgets;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Spatie\Menu\Laravel\MenuFacade as Menu;

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
            }

            $menu->setActive(\url()->current());

            return $menu;
        });
    }
}
