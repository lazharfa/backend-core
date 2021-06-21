<?php

namespace App\Console;

use App\Console\Commands\CheckReportQurbanImage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\DonationClaim',
        'App\Console\Commands\ScheduleChangePriorityCampaign',
        'App\Console\Commands\SchedulePaymentStatus',
        'App\Console\Commands\AddCampaignProgresses',
        'App\Console\Commands\RecoverMidtransTransaction',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('donation:claim')->everyMinute();
        $schedule->command('campaign:change-priority')->everyMinute();
        $schedule->command('tell:donors')->everyMinute();
        $schedule->command('payment:check-status')
            ->hourly()
            ->timezone('Asia/Jakarta')
            ->between('8:00', '18:00');
        $schedule->command('seed:old:donors')
            ->timezone('Asia/Jakarta')
            ->monthlyOn(8, '00:01');
        $schedule->command('campaign:add-progresses')->everyMinute();
        $schedule->command('reminder-donation')->everyMinute();
        $schedule->command('calculate-total-fund')->daily()->timezone('Asia/Jakarta');


        $schedule->command('generate:donor-identity')->everyFifteenMinutes();
        $schedule->command('generate:donor-registered')->everyFifteenMinutes();

        $schedule->command('balance-zenziva')->everyFiveMinutes();

        if (env('BALANS_ORG')) {
            $schedule->command('balans:sync-campaign')->everyFifteenMinutes();
            $schedule->command('balans:sync-donation')->everyFifteenMinutes();
        }

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
