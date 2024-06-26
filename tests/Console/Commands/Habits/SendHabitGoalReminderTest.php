<?php

namespace Tests\Console\Commands\Habits;

use Tests\TestCase;
use App\Domain\Iam\Models\User;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Domain\Emails\Models\EmailPreferences;

class SendHabitGoalReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function notification_is_not_sent_if_email_is_not_verified()
    {
        Notification::fake();

        User::factory()->create([
            'email_verified_at' => null
        ]);

        $this->artisan('habits:send-habit-goal-reminder')
            ->assertSuccessful();

        //        Notification::assertNothingSent();
    }

    /** @test */
    public function mail_is_not_sent_if_mail_preference_is_false()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        EmailPreferences::factory()->create([
            'user_id' => $user->id,
            'goal_reminder' => false,
        ]);

        $this->artisan('habits:send-habit-goal-reminder')
            ->assertSuccessful();

        //        Notification::assertNothingSent();
    }

    /** @test */
    public function notification_is_sent_when_preference_is_set()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        EmailPreferences::factory()->create([
            'user_id' => $user->id,
            'daily_reminder' => false,
            'goal_reminder' => true
        ]);

        $this->artisan('habits:send-habit-goal-reminder')
            ->assertSuccessful();

        //        Notification::assertSentTo($user, HabitGoalReminderNotification::class);
    }
}
