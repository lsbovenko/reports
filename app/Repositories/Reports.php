<?php

namespace App\Repositories;

use App\Models\Auth\User;
use App\Models\Report as ReportModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $subQuery = ReportModel::select(DB::raw('task, MAX(date) AS max_date'))
            ->where('user_id', $user->id)
            ->where('is_tracked', ReportModel::REPORT_UNTRACKED)
            ->groupBy('task')
            ->orderBy('max_date', 'desc')
            ->limit(50);

        $latestTaskNames = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery->getQuery())
            ->orderBy('task', 'asc')
            ->pluck('task')
            ->toArray();

        return $latestTaskNames ?: [];
    }
}