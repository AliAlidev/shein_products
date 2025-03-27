<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

use App\Models\PaymentGatewayTerminal;
use App\Enums\PaymentGatewayTerminalStatus;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        Log::info('Start Schedule');
        // $schedule->command('inspire')->hourly();

        $this->runMqttOrderQueues($schedule);

        $schedule->command('queue:work', ['--tries' => 3, '--queue' => 'payment_gateway_terminal_queue'])
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('mqtt:process-terminal-messages')->everyMinute();

        Log::info('Schedule End');
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

    private function runMqttOrderQueues($schedule){
        $terminals = PaymentGatewayTerminal::where('status', PaymentGatewayTerminalStatus::ACTIVE)->get();

        foreach ($terminals as $key => $terminal) {
            $schedule->command('queue:work', ['--tries' => 3, '--queue' => ('transaction_' . $terminal->serial_number . '_queue')])
            ->everyMinute()
            ->withoutOverlapping();
        }
    }
}
