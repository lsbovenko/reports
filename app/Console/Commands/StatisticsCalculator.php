<?php
/**
 * Created by PhpStorm.
 * User: valeriy
 * Date: 06.03.19
 * Time: 12:12
 */

namespace App\Console\Commands;


use Illuminate\Console\Command;

class StatisticsCalculator extends Command
{
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

    }
}