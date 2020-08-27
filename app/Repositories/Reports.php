<?php

namespace App\Repositories;

use App\Models\Auth\User;
use App\Models\Report as ReportModel;
use Carbon\Carbon;

class Reports
{
    public function getWorkedMinutesByYearAndMonth(int $userId, Carbon $date)
    {
        $firstOfMonth = $date->firstOfMonth()->format('Y-m-d');
        $lastOfMonth = $date->lastOfMonth()->format('Y-m-d');
        $workedMinutes = ReportModel::where('user_id', $userId)
            ->whereBetween('date', [$firstOfMonth, $lastOfMonth])
            ->sum('worked_minutes');

        return $workedMinutes;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getLatestTaskNames(User $user)
    {
        $latestTaskNames = ReportModel::select('task')
            ->where('user_id', $user->id)
            ->where('is_tracked', ReportModel::REPORT_UNTRACKED)
            ->distinct()
            ->orderBy('task', 'asc')
            ->limit(50)
            ->pluck('task')
            ->toArray();

        return $latestTaskNames ?: [];
    }
}