<?php

namespace Database\Factories\Domain\Iam\Models;

use Codinglabs\Roles\Role;
use Illuminate\Support\Str;
use App\Domain\Iam\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->roles()->attach(Role::whereName(User::ROLE_ADMIN)->first());
        });
    }
}
