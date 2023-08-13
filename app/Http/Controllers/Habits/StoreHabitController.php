<?php

namespace App\Http\Controllers\Habits;

use Inertia\Inertia;
use App\Models\Habit;
use App\Enums\Frequency;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Habits\StoreHabitRequest;
use App\Http\Controllers\Traits\HabitStorageTrait;
use App\Http\Controllers\Actions\Habits\StoreHabitAction;

class StoreHabitController extends Controller
{
    use HabitStorageTrait;

    public function __invoke(StoreHabitRequest $request, StoreHabitAction $action): Response
    {
        $data = collect($request->validated());

        $freq = Frequency::cases()[$data['frequency']];

        $habit = Habit::factory()->create([
            'user_id' => Auth::user()->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'frequency' => $freq,
            'scheduled_to' => isset($data['scheduled_to']) ?: null,
            'occurrence_days' => $this->calculatedOccurrenceDays($data, $freq->value),
            'colour' => $data['colour']
        ]);

        $action($habit, $data, $freq);

        return Inertia::location(url('habits'));
    }
}
