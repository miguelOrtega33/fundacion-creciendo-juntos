<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response = $this
            ->withSession(['_token' => 'test-token'])
            ->actingAs($user)
            ->post('/logout', ['_token' => 'test-token']);

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_guest_cannot_logout(): void
    {
        $this
            ->withSession(['_token' => 'test-token'])
            ->post('/logout', ['_token' => 'test-token'])
            ->assertRedirect('/login');
    }
}
