<?php
/**
 * Created by PhpStorm.
 * User: valeriy
 * Date: 06.03.19
 * Time: 12:12
 */

namespace App\Console\Commands;

use App\Models\Auth\User;
use Carbon\Carbon;
use App\Models\Report;
use Illuminate\Console\Command;

class StatisticsCalculator extends Command
{
    /**
     * From the current date, it is exactly 7 days
     * There will always be monday
     */
    const SUB_DAYS = 7;

    /**
     * Monday is the first day of the week and we add 6 days to it to get a full week (Monday ... Sunday)
     */
    const ADD_DAYS = 6;

    /**
     * Translation of 8 hour working day to minutes
     */
    const WORK_HOURS = 480;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skills:send-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send statistics';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $format = 'Y-m-d';
        /** Get the current day last week */
        $previousWeek = Carbon::now()->subDays(self::SUB_DAYS);
        /** We get the dates of Monday last week */
        $previousMonday = $previousWeek->startOfWeek()->format($format);
        /** We get the dates of Sunday last week */
        $previousSunday = $previousWeek->addDays(self::ADD_DAYS)->format($format);

        $users = User::query()->where('is_active', 1)->get()->all();

        $result = [];

        foreach ($users as $user) {
            $query = Report::query();
            $reports = $query->whereBetween('date', [$previousMonday, $previousSunday])
                ->where('user_id', $user->id)->get()->all();
            foreach ($reports as $report) {
                $reportDay = Carbon::createFromFormat($format, $report->date);
                $day = $report->date;

                if (!isset($result[$user->id])) {
                    //Init user
                    $result[$user->id] = [
                        'email' => $user->email,
                        'max_available_time' => 0,
                        'tracked_time' => 0,
                        'un_tracked_time' => 0
                    ];

                    $daysWithReports = [];
                }

                $this->addWorkedTime($result[$user->id], $report->is_tracked, $report->worked_minutes);

                if (!$this->isExistsWorkingDay($daysWithReports, $day) && !$reportDay->isWeekend()) {
                    $daysWithReports[] = $day;
                    //At the weekend can not be fixed as a worker. We can only mark time as spent
                    //f the user is on vacation or is sick, the working days for him are not taken
                    // into account and are not entered ['max_available_time']
                    $result[$user->id]['max_available_time'] += self::WORK_HOURS;
                }
            }
        }

        return $result;
    }

    /**
     * Check for report repetitions on the same day
     * @param array $inputArray
     * @param string $day
     * @return bool
     */
    protected function isExistsWorkingDay(array $inputArray, string $day)
    {
        return in_array($day, $inputArray);
    }

    /**
     * @param array $result
     * @param int $tracked
     * @param int $workTime
     * @return mixed
     */
    protected function addWorkedTime(array &$result, int $tracked, int $workTime)
    {
        $trackKey = $tracked ? 'tracked_time' : 'un_tracked_time';
        $result[$trackKey] += $workTime;
        return $result;
    }
}