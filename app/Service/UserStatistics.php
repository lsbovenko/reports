<?php

namespace App\Service;

use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class Statistics
 * @package App\Service
 */
class UserStatistics
{
    /**
     * @param User|null $user
     * @param Carbon|null $date
     * @param Carbon|null $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getUsersAndLoggedMinutes(
        User $user = null,
        Carbon $date = null,
        Carbon $endDate = null
    ) {
        $query = DB::table('users')
            ->select(DB::raw('users.*, SUM(reports.worked_minutes) as total_worked_minutes'))
            ->join('reports', 'reports.user_id', '=', 'users.id')
            ->orderBy('total_worked_minutes', 'ASC')
            ->groupBy('users.id');

        if (null === $date) {
            $date = Carbon::today();
        }

        $format = 'Y-m-d';
        if (null !== $endDate) {
            $query->whereBetween('reports.date', [$date->format($format), $endDate->format($format)]);

        } else {
            $query->where('reports.date', $date->format($format));
        }

        if (null !== $user) {
            $query->where('reports.user_id', $user->id);
        }

        return $query->get();
    }
}
