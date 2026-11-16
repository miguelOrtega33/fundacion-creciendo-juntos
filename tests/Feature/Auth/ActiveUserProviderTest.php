<?php

namespace Tests\Feature\Auth;

use App\Auth\ActiveUserProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActiveUserProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_rejects_disabled_user_at_every_authentication_boundary(): void
    {
        $user = User::create([
            'name' => 'Cuenta desactivada',
            'email' => 'desactivada@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill([
            'disabled_at' => now(),
            'remember_token' => 'token-recordar',
        ])->save();
        $provider = app('auth')->createUserProvider('users');

        self::assertInstanceOf(ActiveUserProvider::class, $provider);
        self::assertNull($provider->retrieveById($user->id));
        self::assertNull($provider->retrieveByToken($user->id, 'token-recordar'));
        self::assertNull($provider->retrieveByCredentials(['email' => $user->email]));
    }
}
