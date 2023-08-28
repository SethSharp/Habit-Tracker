<?php

namespace App\Http\Events\Habits;

use App\Models\Habit;
use App\Models\User;
use Illuminate\Support\Facades\Event;

class HabitGoalEvent extends Event
{
    public Habit $habit;

    public function __construct(Habit $habit)
    {
        $this->habit = $habit;
    }
}