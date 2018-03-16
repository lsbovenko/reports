<?php

namespace App\Service;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Class RevenueStatistics
 * @package App\Service
 */
class RevenueStatistics
{
    /**
     * @param Carbon|null $date
     * @param Carbon|null $endDate
     * @param Project|null $project
     * @param bool $isFixedPrice
     * @return float
     */
    public function getRevenue(
        Carbon $date = null,
        Carbon $endDate = null,
        Project $project = null,
        $isFixedPrice = false
    )
    {
        $query = DB::table('reports')
            ->select(DB::raw('reports.project_id, projects.rate, SUM(reports.worked_minutes) as total_worked_minutes'))
            ->join('projects', 'reports.project_id', '=', 'projects.id')
            ->where('reports.is_tracked', '=', 1);

        if (isset($project)) {
            if ($project->children->count()) {
                $query->whereIn('reports.project_id', $this->getIds($project->children));
            } else {
                $query->where('reports.project_id', '=', $project->id);
            }
        }

        if (isset($isFixedPrice)) {
            $query->where('projects.is_fixed_price', '=', (int)$isFixedPrice);
        }

        $query->groupBy('projects.id');

        $format = 'Y-m-d';
        if (null !== $endDate) {
            $query->whereBetween('reports.date', [$date->format($format), $endDate->format($format)]);

        } else {
            $query->where('reports.date', $date->format($format));
        }

        return $this->calculateRevenue($query->get());

    }

    /**
     * @param Collection $collection
     * @return float
     */
    private function calculateRevenue(Collection $collection)
    {
        $revenue = 0;
        if ($collection->count()) {
            $minutesInHour = 60;
            foreach ($collection as $item) {
                $item->total_worked_minutes = (int)$item->total_worked_minutes;

                $minutes = $item->total_worked_minutes % $minutesInHour;
                if ($minutes) {
                    $rateForMinute = $item->rate / $minutesInHour;
                    $revenue += $minutes * $rateForMinute;
                }

                $hours = ($item->total_worked_minutes - $minutes) / $minutesInHour;
                $revenue += $hours * $item->rate;
            }
        }

        return round($revenue, 2);
    }

    /**
     * @param $projects
     * @return array
     */
    private function getIds($projects)
    {
        $result = [];
        foreach ($projects as $project) {
            $result[] = $project->id;
        }

        return $result;
    }
}
