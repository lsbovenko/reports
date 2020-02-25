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
                    ->route('main', trans('reports.new_report'))
                    ->route('statistics.index', trans('reports.statistics'))
                    ->route('my-stats', trans('reports.my_statistics'), ['user_id' => $user->id]);

                if ($user->hasRole(Role::ROLE_SUPERADMIN) || $user->hasRole(Role::ROLE_ADMIN)) {
                    $menu
                        ->route('projects.index', trans('reports.projects'))
                        ->route('hours.index', trans('reports.hours'))
                        ->route('revenues.index', trans('reports.revenue'))
                        ->route('planned-hours.index', trans('reports.planned_hours'))
                        ->route('pm.index', trans('reports.pm'));
                }
            }

            $menu->setActive(\url()->current());

            return $menu;
        });
    }
}
