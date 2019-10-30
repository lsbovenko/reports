<?php

namespace App\Repositories;

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
}