<?php

namespace App\Http\Controllers\Traits;

use App\Enums\Frequency;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait ScheduledHabits
{
    use DateHelper;

    public function getDailyScheduledHabits(User $user): array
    {
            return $user->scheduledHabits()
            ->where('scheduled_completion', '=', date('Y-m-d'))
            ->where('completed', '=', 0)
            ->with('habit')
            ->get()
            ->toArray();
    }

    public function getCompletedDailyHabits(user $user)
    {
        return $user->scheduledHabits()
            ->where('scheduled_completion', '=', date('Y-m-d'))
            ->where('completed', '=', 1)
            ->with('habit')
            ->get()
            ->toArray();
    }

    public function getWeeklyScheduledHabits(User $user, string $nextSunday = null, string $nextMonday = null): Collection
    {
        $week = $this->getWeekDatesStartingFromMonday($this->getMonday());

        $thisWeeksHabits = $user->scheduledHabits()
            ->withTrashed()
            ->where('scheduled_completion', '>=', $this->getMonday() ?? $nextMonday)
            ->where('scheduled_completion', '<=', $this->getSunday() ?? $nextSunday)
            ->with(['habit' => fn ($query) => $query->withTrashed()])
            ->get();

        return $week->reduce(function (Collection $carry, string $date, int $key) use ($thisWeeksHabits) {
            $carry[$key] = $thisWeeksHabits->filter(fn ($habit) => $habit->scheduled_completion === $date)->toArray();

            return $carry;
        }, collect());
    }

    public function determineDateForHabitCompletion($freq, $day, $today): string
    {
        return match ($freq) {
            Frequency::DAILY => $today->addDays($day-1),
            Frequency::WEEKLY => $today->copy()->addDays(4)->format('Y-m-d'),
            Frequency::MONTHLY => date('Y-m-d', strtotime(date('Y-m') . '-' . $day)),
            default => now(),
        };
    }
}
