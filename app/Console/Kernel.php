<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
     
     protected $commands =[
         
        //   Commands\TestCron::class,
        //   Commands\Cron::class,
          Commands\Sunday::class,
          Commands\Monfri::class,
          Commands\Saturday::class,
          Commands\Breathinout::class,
          Commands\Monday::class,
          Commands\Breathinoutus::class, 
          Commands\Mondayus::class, 

     ];
     
    protected function schedule(Schedule $schedule): void
    {
     
        
        // $schedule->command('monfri:command')->timezone('Asia/Kolkata')->cron('0 0 * * 1-5');
        // $schedule->command('monfri:command')->timezone('Asia/Kolkata')->cron('* * * * *');
        // $schedule->command('monday:command')->timezone('Asia/Kolkata')->cron('* * * * *');
        $schedule->command('monday:command')->timezone('Asia/Kolkata')->cron('15 0 * * 1');
        $schedule->command('sunday:command')->timezone('Asia/Kolkata')->cron('45 23 * * 0');
        $schedule->command('saturday:command')->timezone('Asia/Kolkata')->cron('0 1 * * 6');
        
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 6 * * 1-5'); // open at 06:00 AM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 7 * * 1-5');  // close at 07:00 AM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 11 * * 1-5'); // open at 11:00 AM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 12 * * 1-5'); // close at 12:00 PM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 16 * * 1-5'); // open at 04:00 PM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 17 * * 1-5'); // close at 05:00 PM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 21 * * 1-5'); // open at 09:00 PM
        $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 22 * * 1-5'); // close at 10:00 PM
        
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 6 * * 1-5');//open
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 7 * * 1-5');//close
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 11 * * 1-5');//open
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 12 * * 1-5');//close
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 16 * * 1-5');//open
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 17 * * 1-5');//close
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 21 * * 1-5');//open
        // $schedule->command('breathinout:command')->timezone('Asia/Kolkata')->cron('0 22 * * 1-5');//close
        
        
        // US TIME ZONE CRONS

        // $schedule->command('mondayus:command')->timezone('America/New_York')->cron('05 14 * * 0');
        // $schedule->command('sunday:command')->timezone('America/New_York')->cron('15 14 * * 6');
        // $schedule->command('saturday:command')->timezone('America/New_York')->cron('45 14 * * 5');
        
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 20 * * 0-4'); // open at 06:00 AM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 21 * * 0-4'); // close at 07:00 AM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 01 * * 0-4'); // open at 11:00 AM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 02 * * 0-4'); // close at 12:00 PM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 06 * * 0-4'); // open at 04:00 PM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 07 * * 0-4'); // close at 05:00 PM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 11 * * 0-4'); // open at 09:00 PM IST
        // $schedule->command('breathinoutus:command')->timezone('America/New_York')->cron('30 12 * * 0-4'); // close at 10:00 PM IST

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
