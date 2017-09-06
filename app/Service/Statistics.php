<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 17.08.2017
 * Time: 18:21
 */

namespace App\Service;


use App\Models\Auth\User;
use App\Models\Report;
use Carbon\Carbon;

/**
 * Class Statistics
 * @package App\Service
 */
class Statistics
{
    const TRACKED_COLOR = 'rgb(217, 237, 247)';

    const UNTRACKED_COLOR = 'rgb(225, 217, 247)';

    /**
     * Compose summary report
     *
     * @param User|null $user - filter reports by user
     * @param Carbon $date - filter reports by this date (may be used as start date if 3rd parameter is passed) today by default
     * @param Carbon|null $endDate - retrieve reports for period between $date and $endDate if passed
     *
     * @return array
     */
    public function getReportsSummary(User $user = null, Carbon $date = null, Carbon $endDate = null)
    {
        $query = Report::query();

        if (null !== $user) {
            $query->where('user_id', $user->id);
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

    public function getStackedDatasets(User $user, Carbon $startDate, Carbon $endDate)
    {
        $summary = $this->getReportsSummary($user, $startDate, $endDate);

        $labels = [];
        $datasets = [
            [
                'label' => 'Зафиксированное время',
                'backgroundColor' => static::TRACKED_COLOR,
                'data' => [],
            ],
            [
                'label' => 'Другая активность',
                'backgroundColor' => static::UNTRACKED_COLOR,
                'data' => [],
            ]
        ];

        // we have to show all dates for concrete period
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->modify('+1 day'));
        foreach ($period as $date) {
            $key = $date->format('m/d');

            $labels[] = $key;
            //first of all fill this with 0
            $datasets[0]['data'][$key] = 0;
            $datasets[1]['data'][$key] = 0;

        }

        foreach ((array)array_shift($summary) as $reportItem) {
            $key = Carbon::parse($reportItem['date'])->format('m/d');

            $datasets[0]['data'][$key] = $reportItem['tracked_logged_minutes'] / 60;
            $datasets[1]['data'][$key] = $reportItem['untracked_logged_minutes'] / 60;
        }

        //reset keys
        $datasets[0]['data'] = array_values($datasets[0]['data']);
        $datasets[1]['data'] = array_values($datasets[1]['data']);

        return compact('labels', 'datasets');
    }

    private function composeReports(Report ...$reports)
    {
        $result = [];

        /** @var Report $report */
        foreach ($reports as $report) {

            if (!isset($result[$report->user_id][$report->date])) {
                $result[$report->user_id][$report->date] = [];
                $item = &$result[$report->user_id][$report->date];
                $item['date'] = $report->date;
                $item['total_logged_minutes'] = 0;

                $user = $report->user()->first();
                $item['user'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                ];
                $item['tracked'] = $item['untracked'] = [];
                $item['tracked_logged_minutes'] = 0;
                $item['untracked_logged_minutes'] = 0;
            }

            $item = &$result[$report->user_id][$report->date];
            $item['total_logged_minutes'] += $report->worked_minutes;

            if ($report->is_tracked) {
                $item['tracked_logged_minutes'] += $report->worked_minutes;
                $item['tracked'][] = [
                    'created' => $report->created_at->format('Y-m-d H:i:s'),
                    'project_name' => $report->project()->first()->name,
                    'descirption' => $report->description,
                    'total_minutes' => $report->worked_minutes,
                    'minutes' => $report->worked_minutes % 60,
                    'hours' => (int)($report->worked_minutes / 60),
                ];
            } else {
                $item['untracked_logged_minutes'] += $report->worked_minutes;
                $item['untracked'][] = [
                    'created' => $report->created_at->format('Y-m-d H:i:s'),
                    'task' => $report->task,
                    'descirption' => $report->description,
                    'total_minutes' => $report->worked_minutes,
                    'minutes' => $report->worked_minutes % 60,
                    'hours' => (int)($report->worked_minutes / 60),
                ];
            }

        }

        foreach ($result as $userId => &$resultItem) {
            $resultItem = array_values($resultItem);
        }

        return array_values($result);
    }
}
