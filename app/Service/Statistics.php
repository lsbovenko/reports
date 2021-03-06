<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 17.08.2017
 * Time: 18:21
 */

namespace App\Service;


use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class Statistics
 * @package App\Service
 */
class Statistics
{
    const TRACKED_COLOR = 'rgb(92, 184, 92)';
    const UNTRACKED_COLOR = 'rgb(91, 192, 222)';

    const GRAPH_MIN_TIME = 9;
    const GRAPH_MAX_TIME = 15;

    /**
     * Compose summary report
     *
     * @param User|null $user - filter reports by user
     * @param Carbon $date - filter reports by this date (may be used as start date if 3rd parameter is passed) today by default
     * @param Carbon|null $endDate - retrieve reports for period between $date and $endDate if passed
     * @param int $isMeeting - filter reports by meeting
     *
     * @return array
     */
    public function getReportsSummary(User $user = null, Carbon $date = null, Carbon $endDate = null, int $isMeeting = 0)
    {
        $query = Report::query();

        if (null !== $user) {
            $query->where('user_id', $user->id);
        }

        if ($isMeeting) {
            $query->where('is_meeting', $isMeeting);
        }

        if (null === $date) {
            $date = Carbon::today();
        }

        $format = 'Y-m-d';
        if (null !== $endDate) {
            $query->whereBetween('date', [$date->format($format), $endDate->format($format)]);
            $query
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc');

        } else {
            $query->where('date', $date->format($format));
            $query->orderBy('created_at', 'desc');
        }

        return $this->composeReports(...$query->get()->all());
    }

    public function getTotalLoggedMinutes(User $user = null, Carbon $date = null, Carbon $endDate = null)
    {
        $total = 0;
        foreach ($this->getReportsSummary($user, $date, $endDate) as $personReport) {
            foreach ($personReport as $reportItem) {
                $total += $reportItem['total_logged_minutes'];
            }
        }

        return $total;
    }

    public function getStackedDatasets(User $user, Carbon $startDate, Carbon $endDate)
    {
        $summary = $this->getReportsSummary($user, $startDate, $endDate);

        $labels = [];
        $dates = [];
        $datasets = [
            [
                'label' => trans('reports.fixed_time'),
                'backgroundColor' => static::TRACKED_COLOR,
                'data' => [],
            ],
            [
                'label' => trans('reports.other_activity'),
                'backgroundColor' => static::UNTRACKED_COLOR,
                'data' => [],
            ]
        ];

        // we have to show all dates for concrete period
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
        foreach ($period as $date) {
            $key = $date->format('m/d');

            $labels[] = $key;
            $dates[$key] = $date->format('Y-m-d');
            //first of all fill this with 0
            $datasets[0]['data'][$key] = 0;
            $datasets[1]['data'][$key] = 0;

        }
        $title = 'Date range from ' . array_first($dates) . ' to ' . array_last($dates);

        $maxTime = 0;
        foreach ((array)array_shift($summary) as $reportItem) {
            $key = Carbon::parse($reportItem['date'])->format('m/d');

            $datasets[0]['data'][$key] = $reportItem['tracked_logged_minutes'] / 60;
            $datasets[1]['data'][$key] = $reportItem['untracked_logged_minutes'] / 60;

            $currentTime = ($reportItem['tracked_logged_minutes'] + $reportItem['untracked_logged_minutes']) / 60;
            $maxTime = $maxTime < $currentTime ? $currentTime : $maxTime;
        }
        $maxTime = ceil($maxTime) + 1;
        $maxTime = $maxTime > self::GRAPH_MIN_TIME ? $maxTime : self::GRAPH_MIN_TIME;
        $maxTime = $maxTime < self::GRAPH_MAX_TIME ? $maxTime : self::GRAPH_MAX_TIME;

        //reset keys
        $datasets[0]['data'] = array_values($datasets[0]['data']);
        $datasets[1]['data'] = array_values($datasets[1]['data']);

        return compact('labels', 'datasets', 'maxTime', 'dates', 'title');
    }

    private function composeReports(Report ...$reports)
    {
        $result = [];
        $currentUser = Auth::user();

        /** @var Report $report */
        foreach ($reports as $report) {

            if (!isset($result[$report->user_id][$report->date])) {
                $result[$report->user_id][$report->date] = [];
                $item = &$result[$report->user_id][$report->date];
                $item['date'] = $report->date;
                $item['total_logged_minutes'] = 0;
                $item['total_overtime_minutes'] = 0;

                $user = $report->user()->first();
                $item['user'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                ];
                $item['tracked'] = $item['untracked'] = [];
                $item['tracked_logged_minutes'] = 0;
                $item['untracked_logged_minutes'] = 0;
                $item['editable'] = $currentUser && $user->id === $currentUser->id;
            }

            $item = &$result[$report->user_id][$report->date];
            $item['total_logged_minutes'] += $report->worked_minutes;
            if ($report->is_overtime) {
                $item['total_overtime_minutes'] += $report->worked_minutes;
            }

            if ($report->is_tracked) {
                $item['tracked_logged_minutes'] += $report->worked_minutes;
                $workedMinutes = $report->worked_minutes % Carbon::MINUTES_PER_HOUR;
                $workedHours = (int)($report->worked_minutes / Carbon::MINUTES_PER_HOUR);
                $item['tracked'][] = [
                    'id' => $report->id,
                    'created' => $report->created_at->format('Y-m-d H:i:s'),
                    'project_name' => $report->project()->first()->getFullName(),
                    'is_meeting' => $report->is_meeting,
                    'descirption' => $report->description,
                    'overtime' => $report->is_overtime,
                    'total_minutes' => $report->worked_minutes,
                    'minutes' => $workedMinutes,
                    'hours' => $workedHours,
                    'formatted_time' => sprintf('%02d:%02d', $workedHours, $workedMinutes),
                ];
            } else {
                $project = $report->project()->first();
                $item['untracked_logged_minutes'] += $report->worked_minutes;
                $workedMinutes = $report->worked_minutes % Carbon::MINUTES_PER_HOUR;
                $workedHours = (int)($report->worked_minutes / Carbon::MINUTES_PER_HOUR);
                $item['untracked'][] = [
                    'id' => $report->id,
                    'created' => $report->created_at->format('Y-m-d H:i:s'),
                    'task' => $project ? $project->name : $report->task,
                    'descirption' => $report->description,
                    'overtime' => $report->is_overtime,
                    'total_minutes' => $report->worked_minutes,
                    'minutes' => $workedMinutes,
                    'hours' => $workedHours,
                    'formatted_time' => sprintf('%02d:%02d', $workedHours, $workedMinutes),
                ];
            }

        }

        foreach ($result as $userId => &$resultItem) {
            $resultItem = array_values($resultItem);
        }

        return array_values($result);
    }
}
