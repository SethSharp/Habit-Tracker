<?php

namespace Tests\Console\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Mail\HabitReminder;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class SendHabitScheduleReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function mail_is_sent_when_command_is_called()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->artisan('habits:send-habit-reminder')
            ->assertSuccessful();

        Mail::assertSent(HabitReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function mail_is_not_sent_if_email_is_not_verified()
    {
        Mail::fake();

        User::factory()->create([
            'email_verified_at' => null
        ]);

        $this->artisan('habits:send-habit-reminder')
            ->assertSuccessful();

        Mail::assertNotSent(HabitReminder::class);
    }
}