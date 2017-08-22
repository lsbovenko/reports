<?php

namespace App\Console\Commands;

use App\Mail\RemindAboutReport;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;

class SendReportReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find users who have not created report previous working day and notify them';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*** Starting command "' . $this->signature . '" at ' . Carbon::now()->toIso8601String() . " ***");

        $users = $this->findUsers();

        if (null !== $users) {

            $date = $this->getPreviousWorkingDay();
            $this->info('Reminder target date is ' . $date->format('Y-m-d') );

            foreach ($users as $user) {
                $this->info('Send reminder to ' . $user->getFullName());
                \Mail::to($user)
                    ->send(new RemindAboutReport($user, $date));
            }
        } else {
            $this->info('No users were found.');
        }
    }

    private function findUsers()
    {
        $date = $this->getPreviousWorkingDay();

        return User::query()
            ->where('is_active', 1)
            ->where('is_report_required', 1)
            /* subquery which retrieves ids for those users who created report */
            ->whereNotIn('id', function(Builder $query) use ($date){
                $query
                    ->from('users')
                    ->select('users.id')
                    ->join('reports', 'reports.user_id', '=', 'users.id')
                    ->where('is_active', 1)
                    ->where('is_report_required', 1)
                    ->where('reports.date', $date);
            })
            ->get();
    }

    private function getPreviousWorkingDay() : Carbon
    {
        $workingDay = Carbon::yesterday();
        if ($workingDay->isSaturday() || $workingDay->isSunday()) {
            $workingDay = Carbon::parse('last friday');
        }

        return $workingDay;
    }
}
