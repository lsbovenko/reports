<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Class SetStamp
 *
 * @package App\Console\Commands
 */
class SetStamp extends Command
{
    const APP_VERSION = 'APP_VERSION';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stamp:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set stamp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $env = app()->environmentFilePath();
        if (!file_exists($env)) {
            throw new \Exception('.env file not found');
        }

        $content = file_get_contents($env);
        $pos = strpos($content, self::APP_VERSION);
        $previousTimestamp = config('app.version');
        $currentTimestamp = Carbon::now()->timestamp;

        if ($pos === false) {
            file_put_contents($env, PHP_EOL . self::APP_VERSION . '=' . $currentTimestamp . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($env, str_replace(
                self::APP_VERSION . '=' . $previousTimestamp,
                self::APP_VERSION . '=' . $currentTimestamp,
                $content
            ));
        }
    }
}
