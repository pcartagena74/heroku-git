<?php

namespace App\Console;

use App\Jobs\SendCampaignEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
// set_time_limit(0);
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ConnectToStreamingAPI::class,
        //\Spatie\LinkChecker\CheckLinksCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->job(new SendCampaignEmail)->everyFifteenMinutes();
        $schedule->job(new SendCampaignEmail)->everyMinute();//as heroku support only 10 mins we will take care of reset from job itself
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
