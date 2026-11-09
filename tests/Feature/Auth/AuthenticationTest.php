<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_authenticate_and_redirect_to_panel(): void
    {
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->post('/login', [
            '_token' => 'test-token',
            'email' => $user->email,
            'password' => 'ClaveSegura123!',
        ]);

        $response
            ->assertRedirect('/panel')
            ->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected_without_revealing_user_state(): void
    {
        User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response = $this->withSession(['_token' => 'test-token'])->from('/login')->post('/login', [
            '_token' => 'test-token',
            'email' => 'maria@example.test',
            'password' => 'incorrecta',
        ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_panel_requires_authentication(): void
    {
        $this->get('/panel')->assertRedirect('/login');
    }
}
