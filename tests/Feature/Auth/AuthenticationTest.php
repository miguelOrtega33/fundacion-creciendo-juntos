<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
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

        $loginPage = $this->followRedirects($response);

        $loginPage
            ->assertSee('Revisa los datos ingresados e intenta nuevamente.')
            ->assertSee('value="maria@example.test"', false)
            ->assertDontSee('value="incorrecta"', false);
    }

    public function test_panel_requires_authentication(): void
    {
        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_password_reset_invalidates_an_existing_authenticated_session(): void
    {
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $this->withSession(['_token' => 'test-token'])
            ->post('/login', [
                '_token' => 'test-token',
                'email' => $user->email,
                'password' => 'ClaveSegura123!',
            ])
            ->assertRedirect('/panel');
        $this->get('/panel')->assertOk();

        $token = Password::createToken($user);
        $this->withSession(['_token' => 'test-token'])
            ->post('/restablecer-clave', [
                '_token' => 'test-token',
                'token' => $token,
                'email' => $user->email,
                'password' => 'NuevaClaveSegura123!',
                'password_confirmation' => 'NuevaClaveSegura123!',
            ])
            ->assertRedirect('/login');

        Auth::forgetGuards();

        $this->get('/panel')->assertRedirect('/login');
        $this->assertGuest();
    }
}
