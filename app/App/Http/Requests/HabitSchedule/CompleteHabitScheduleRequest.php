<?php

namespace App\App\Http\Requests\HabitSchedule;

use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\HabitSchedule\Models\HabitSchedule;

class CompleteHabitScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('manage', [HabitSchedule::class, $this->route('habitSchedule')]);
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator)
    {
        $validator->validate();

        $validator->after(function (Validator $validator) {
            if ($this->route('habitSchedule')->completed) {
                $validator->errors()->add('habit_schedule', 'Habit is already completed!');
            }

            if (Carbon::now()->lte(Carbon::parse($this->route('habitSchedule')->scheduled_completion))) {
                $validator->errors()->add('habit_schedule', 'Cannot complete a future habit');
            }
        });
    }
}
