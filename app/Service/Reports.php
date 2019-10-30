<?php

namespace App\Service;

use App\Repositories\PlannedHours as PlannedHoursRepository;
use App\Repositories\Reports as ReportsRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Dto\ReportsStatistics;
use Carbon\Carbon;

class Reports
{
    protected $plannedHoursRepository;
    protected $reportsRepository;
    protected $userId;

    public function __construct(
        PlannedHoursRepository $plannedHoursRepository,
        ReportsRepository $reportsRepository,
        Authenticatable $authenticatable
    ) {
        $this->plannedHoursRepository = $plannedHoursRepository;
        $this->reportsRepository = $reportsRepository;
        $this->userId = $authenticatable->getAuthIdentifier();
    }

    public function getMonthStatsDTO(Carbon $date)
    {
        $plannedHours = $this->plannedHoursRepository->getPlannedHoursByYearAndMonth($date);
        $reportsStatistics = new ReportsStatistics();

        //get the number of working minutes per month on the production calendar
        $plannedMinutes = ($plannedHours) ? Carbon::MINUTES_PER_HOUR * $plannedHours->planned_hours : 0;
        $reportsStatistics->plannedMinutes = $plannedMinutes;
        $reportsStatistics->formattedPlannedTime = $this->getFormattedTime($plannedMinutes);

        //get the number of minutes worked per month
        $workedMinutes = $this->reportsRepository->getWorkedMinutesByYearAndMonth($this->userId, $date);
        $reportsStatistics->workedMinutes = $workedMinutes;
        $reportsStatistics->formattedWorkedTime = $this->getFormattedTime($workedMinutes);

        if ($workedMinutes <= $plannedMinutes) {
            //get the number of left to work minutes per month
            $differenceMinutes = $plannedMinutes - $workedMinutes;

            //get the ratio of already worked minutes per month to the number of working minutes per month according to the production calendar as a percentage
            $reportsStatistics->percent = ($plannedMinutes) ? (int)(100 * $workedMinutes/$plannedMinutes) : 0;
            $reportsStatistics->isExistsOvertime = false;
        } else {
            //get the number of overtime minutes per month
            $differenceMinutes = $workedMinutes - $plannedMinutes;

            //get the ratio of working minutes per month according to the production calendar to the number of already worked minutes per month as a percentage
            $reportsStatistics->percent = (int)(100 * $plannedMinutes/$workedMinutes);
            $reportsStatistics->isExistsOvertime = true;
        }
        $reportsStatistics->differenceMinutes = $differenceMinutes;
        $reportsStatistics->formattedDifferenceTime = $this->getFormattedTime($differenceMinutes);

        return $reportsStatistics;
    }

    protected function getFormattedTime(int $minutes)
    {
        $hours = (int)($minutes / Carbon::MINUTES_PER_HOUR);
        $minutes = $minutes % Carbon::MINUTES_PER_HOUR;
        $time = sprintf('%d%s %02d%s', $hours, trans('reports.time_array')[0], $minutes, trans('reports.time_array')[1]);

        return $time;
    }
}
