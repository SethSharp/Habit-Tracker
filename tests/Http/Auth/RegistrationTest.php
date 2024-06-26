<?php

namespace Tests\Http\Auth;

use Tests\TestCase;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\App\Http\Events\RegisteredEvent;
use App\App\Providers\RouteServiceProvider;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /** @test */
    public function new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example1.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /** @test */
    public function registered_event_triggered_on_registration()
    {
        Event::fake();

        $this->post('/register', [
            'name' => 'Test User 1',
            'email' => 'test@example2.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Event::assertDispatched(RegisteredEvent::class);
    }

    /** @test */
    public function email_s_verified_instantly()
    {
        Event::fake();

        $this->post('/register', [
            'name' => 'Test User 1',
            'email' => 'test@example2.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Event::assertDispatched(RegisteredEvent::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User 1',
            'email_verified_at' => now()
        ]);
    }
}
