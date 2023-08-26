<?php

namespace App\Http\Controllers\Actions\Habits;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Habit;
use App\Models\HabitSchedule;
use Illuminate\Support\Collection;
use App\Http\Controllers\Traits\DateHelper;
use App\Http\Controllers\Traits\ScheduledHabits;

class UpdateHabitAction
{
    use ScheduledHabits;
    use DateHelper;

    public function __invoke(Habit $habit, Collection $data, User $user): void
    {
        $occurrences = json_decode($habit->occurrence_days);
        $scheduledDate = Carbon::now();

        while ($scheduledDate <= Carbon::now()->endOfMonth()) {
            $scheduledHabitsForToday = $user->scheduledHabits()
                ->where([
                    'habit_id' => $habit->id,
                    'scheduled_completion' => $scheduledDate->toDateString(),
                ])
                ->get();

            // if today is a day in occurrences add to list
            if (in_array($scheduledDate->dayOfWeek, $occurrences)) {
                if (count($scheduledHabitsForToday) === 0) {
                    HabitSchedule::factory()->create([
                        'habit_id' => $habit->id,
                        'user_id' => $user->id,
                        'scheduled_completion' => $scheduledDate
                    ]);
                }
            } else {
                ray($scheduledHabitsForToday);
                $scheduledHabitsForToday->each(function ($habit) {
                    ray('removing: ' . $habit->id);
                    $habit->delete();
                });
            }

            $scheduledDate->addDay();
        }
    }
}
