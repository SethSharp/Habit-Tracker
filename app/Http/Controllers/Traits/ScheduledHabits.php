<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;
use App\Models\User;
use App\Http\CacheKeys;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait ScheduledHabits
{
    public function getDailyScheduledHabits(User $user): array
    {
        return $user->scheduledHabits()
            ->where('scheduled_completion', '=', Carbon::now()->toDateString())
            ->with('habit')
            ->get()
            ->toArray();
    }

    public function getCompletedDailyHabits(user $user): array
    {
        return $user->scheduledHabits()
            ->where('scheduled_completion', '=', Carbon::now()->toDateString())
            ->where('completed', '=', 1)
            ->with('habit')
            ->get()
            ->toArray();
    }

    public function getWeeklyScheduledHabits(User $user): Collection
    {
        $week = $this->getWeekDatesStartingFromMonday();

        $thisWeeksHabits = $user->scheduledHabits()
            ->where('scheduled_completion', '>=', Carbon::now()->startOfWeek(0)->toDateString())
            ->where('scheduled_completion', '<=', Carbon::now()->endOfWeek(-1)->toDateString())
            ->with(['habit' => fn ($query) => $query->withTrashed()])
            ->get();

        return $week->reduce(function (Collection $carry, string $date) use ($thisWeeksHabits) {
            $carry[$date] = $thisWeeksHabits->filter(fn ($habit) => $habit->scheduled_completion === $date)->toArray();
            return $carry;
        }, collect());
    }

    public function monthlyScheduledHabits(User $user, string|null $month, bool $withCaching=false): array|null
    {
        if (is_null($month)) {
            $month = Carbon::now()->monthName;
        }

        $startDate = Carbon::parse("1 $month")->startOfMonth();
        $endDate = Carbon::parse("1 $month")->endOfMonth();

        if ($startDate > Carbon::now()) {
            return [];
        }

        if ($withCaching) {
            $data = $this->getMonthlyHabitsByDate($user, $startDate, $endDate);
            Cache::put(CacheKeys::scheduledHabitsForTheMonth($user, $month), $data, now()->addWeek());
        } else {
            return Cache::remember(CacheKeys::scheduledHabitsForTheMonth($user, $month), now()->addWeek(), fn () => $this->getMonthlyHabitsByDate($user, $startDate, $endDate));
        }

        return null;
    }

    protected function getMonthlyHabitsByDate(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $habits = $user->scheduledHabits()
            ->whereBetween('scheduled_completion', [$startDate, $endDate])
            ->with(['habit' => fn ($query) => $query->withTrashed()])
            ->orderBy('scheduled_completion', 'desc')
            ->get();

        $period = CarbonPeriod::create($startDate, $endDate);

        $habitByDate = [];
        $index = 0;
        foreach ($period as $date) {
            $formattedDate = $date->toDateString();

            $habitsForDate = collect();

            foreach ($habits as $scheduledHabit) {
                if ($scheduledHabit->scheduled_completion === $formattedDate) {
                    $habitsForDate->push($scheduledHabit);
                }
            }

            $habitByDate[$index] = $habitsForDate;
            $index++;
        }

        return $habitByDate;
    }

    public function getHabitFiltersForMonth(User $user, ?string $month): Collection
    {
        if (is_null($month)) {
            $month = Carbon::now()->monthName;
        }

        $startDate = Carbon::parse("1 $month")->startOfMonth();
        $endDate = Carbon::parse("1 $month")->endOfMonth();

        $data = $user->scheduledHabits()
            ->with('habit')
            ->whereBetween('scheduled_completion', [$startDate, $endDate])
            ->get();

        $habits = collect();

        foreach ($data as $scheduledHabit) {
            if (! $habits->contains('id', $scheduledHabit->habit_id)) {
                $habits->push([
                    'id' => $scheduledHabit->habit->id,
                    'title' => $scheduledHabit->habit->name,
                    'attributePath' => 'habit.id',
                    'filterBy' => $scheduledHabit->habit->id,
                    'colour' => $scheduledHabit->habit->colour,
                ]);
            }
        }

        return $habits;
    }

    private function getWeekDatesStartingFromMonday(): Collection
    {
        $dates = collect();

        $startOfWeek = Carbon::now()->startOfWeek(0);

        for ($i = 0; $i < 7; $i++) {
            $dates[] = $startOfWeek->toDateString();
            $startOfWeek->addDay();
        }

        return $dates;
    }
}
