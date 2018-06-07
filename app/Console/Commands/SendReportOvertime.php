<?php

namespace App\Console\Commands;

use App\Mail\OvertimeReport;
use App\Mail\RemindAboutReport;
use App\Models\Auth\User;
use App\Service\Statistics;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;

class SendReportOvertime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-overtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send report with overtime';

    protected $stats;

    public function __construct(Statistics $stats)
    {
        $this->stats = $stats;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*** Starting command "' . $this->signature . '" at ' . Carbon::now()->toIso8601String() . " ***");

        $firstDay = Carbon::parse('first day of previous month 00:00:00');
        $lastDay = Carbon::parse('last day of previous month 23:59:59');
        $data = $this->getOvertimeStats($firstDay, $lastDay);

        if (count($data)) {
            \Mail::to(["slava@ikantam.com", "vellumweb@gmail.com", "alex.poliushkin@gmail.com"])
                ->send(new OvertimeReport($data, $firstDay, $lastDay));
        }
    }

    private function getOvertimeStats(Carbon $from, Carbon $till): array
    {

        $report = $this->stats->getReportsSummary(null, $from, $till);
        $result = [];

        foreach ($report as $list) {
            foreach ($list as $item) {
                if (!$item['total_overtime_minutes']) {
                    continue;
                }
                $key = $item['user']['last_name'] . ' ' . $item['user']['name'];
                if (!isset($result[$key])) {
                    $result[$key] = [
                        'details' => [],
                        'time' => 0,
                    ];
                }
                $result[$key]['details'][$item['date']] = $this->shortTime((int)$item['total_overtime_minutes']);
                $result[$key]['time'] += $item['total_overtime_minutes'];
            }
        }

        foreach ($result as &$item) {
            $item['time'] = $this->shortTime($item['time']);
        }

        return $result;
    }

    private function shortTime(int $minutes)
    {
        $hours = (int)($minutes / 60);
        $minutes = $minutes % 60;

        return str_pad($hours, 2, 0, STR_PAD_LEFT) .
            ':' .
            str_pad($minutes, 2, 0, STR_PAD_LEFT);
    }
}
