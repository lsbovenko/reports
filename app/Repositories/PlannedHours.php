<?php

namespace App\Repositories;

use App\Models\PlannedHours as PlannedHoursModel;
use Carbon\Carbon;

class PlannedHours
{
    public function getYears()
    {
        return PlannedHoursModel::distinct()->select('year')->orderBy('year', 'desc')->limit(100)->get();
    }

    public function getPlannedHoursByYear(int $year)
    {
        return PlannedHoursModel::where('year', $year)->orderBy('month', 'asc')->get();
    }

    public function getPlannedHoursByYearAndMonth(Carbon $date)
    {
        return PlannedHoursModel::where('year', $date->year)->where('month', $date->month)->first();
    }
}