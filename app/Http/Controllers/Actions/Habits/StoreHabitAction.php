<?php

namespace App\Http\Controllers\Actions\Habits;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Habit;
use Illuminate\Support\Collection;
use App\Http\Controllers\Traits\HabitStorage;

class StoreHabitAction
{
    use HabitStorage;

    public function __invoke(User $user, Habit $habit, string|null $scheduledTo, Collection $data): void
    {
        $scheduledDate = Carbon::now()->startOfWeek();

        $isSet = isset($scheduledTo) && ! is_null($data['scheduled_to']);
        $endDate = $isSet
            ? Carbon::parse($scheduledTo) > Carbon::now()->endOfMonth()
                ? Carbon::now()->endOfMonth()
                : Carbon::parse($scheduledTo)
            : Carbon::now()->endOfMonth();

        if (isset($data['start_next_week']) && ! is_null($data['start_next_week']) && $data['start_next_week']) {
            $scheduledDate->addWeek();
        }

        $this->scheduledHabitsOverTimeframe($user, $habit, $scheduledDate, $endDate);
    }
}
