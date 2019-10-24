<?php

namespace App\Repositories;

use App\Models\PlannedHours as PlannedHoursModel;

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
}