<?php

namespace App\Service;

use App\Dto\ProjectManagerData;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ProjectManagerStatistics
 * @package App\Service
 */
class ProjectManagerStatistics
{
    /**
     * @param Carbon|null $date
     * @param Carbon|null $endDate
     * @param Project|null $project
     * @return array
     */
    public function getProjectManagerStatistics(
        Carbon $date = null,
        Carbon $endDate = null,
        Project $project = null
    )
    {
        if (!isset($project)) {
            return [];
        }
        $query = DB::table('users')
            ->join('reports', 'reports.user_id', '=', 'users.id')
            ->select(DB::raw('users.name, users.last_name, SUM(reports.worked_minutes) as time'))
            ->where('reports.is_tracked', '=', 1)
            ->where('reports.project_id', '=', $project->id)
            ->groupBy('users.id');

        $format = 'Y-m-d';
        if ($endDate !== null) {
            $query->whereBetween('reports.date', [$date->format($format), $endDate->format($format)]);
        } else {
            $query->where('reports.date', $date->format($format));
        }

        return $this->calculateProjectManagerStatistics($query->get());
    }

    /**
     * @param Collection $collection
     * @return array
     */
    private function calculateProjectManagerStatistics(Collection $collection)
    {
        $statistics = [];

        if ($collection->count()) {
            $minutesPerHour = Carbon::MINUTES_PER_HOUR;
            foreach ($collection as $item) {
                $employee = $item->name." ".$item->last_name;
                $hours = intval($item->time / $minutesPerHour);
                $minutes = date('i',mktime(0, $item->time % $minutesPerHour));    //output in mm format
                $timeDec = round($item->time / $minutesPerHour, 2);
                $statistics[] = new ProjectManagerData($employee, $hours, $minutes, $timeDec);
            }
        }

        return $statistics;
    }
}