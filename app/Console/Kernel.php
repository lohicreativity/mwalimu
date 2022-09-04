<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\UpdateGatewayPayment;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        App\Console\Commands\UpdateGatewayPayment::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
         //$schedule->command('queue:work')
                //  ->timezone('Africa/Dar_es_Salaam')
                //  ->everyMinute();
        $schedule->command('invoice:update-gateway-payment')->timezone('Africa/Dar_es_Salaam')->everyMinute();
        // $schedule->call(new UpdateGatewayPayment)->timezone('Africa/Dar_es_Salaam')->dailyAt('22:00');
        // $schedule->call(new UpdateGatewayPayment)->timezone('Africa/Dar_es_Salaam')->dailyAt('07:00');
        // $schedule->call(new UpdateGatewayPayment)->timezone('Africa/Dar_es_Salaam')->dailyAt('12:00');
        // $schedule->call(new UpdateGatewayPayment)->timezone('Africa/Dar_es_Salaam')->dailyAt('16:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
