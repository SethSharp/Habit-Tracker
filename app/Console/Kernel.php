<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\Counters\HabitStreak;
use App\Console\Commands\Habits\SendDailyHabitReminder;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ScheduledHabits\ScheduleHabitsForWeek;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // scheduling habits
        $schedule->command(ScheduleHabitsForWeek::class)->mondays(); // also calls cleanup

        // counters
        $schedule->command(HabitStreak::class)->daily();

        // Notifications
        $schedule->command(SendDailyHabitReminder::class)->dailyAt('17:00');
        // TODO: Start of the week notification
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
