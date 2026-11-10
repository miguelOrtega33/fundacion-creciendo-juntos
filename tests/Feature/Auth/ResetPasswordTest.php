<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_page_preserves_token_and_email(): void
    {
        $response = $this->get('/restablecer-clave/token-prueba?email=maria%40example.test');

        $response
            ->assertOk()
            ->assertViewIs('auth.reset-password')
            ->assertSee('Crear nueva contraseña')
            ->assertSee('Usa entre 15 y 64 caracteres. Puedes incluir espacios y usar una frase larga.')
            ->assertSee('value="token-prueba"', false)
            ->assertSee('value="maria@example.test"', false);

        $html = (string) $response->getContent();

        self::assertSame(2, substr_count($html, 'minlength="15"'));
        self::assertSame(0, substr_count($html, 'maxlength='));
        self::assertSame(2, substr_count($html, 'aria-describedby="password-help"'));
    }

    public function test_valid_token_changes_password_and_cannot_be_reused(): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);

        $this->postReset($token, $user->email, 'NuevaClaveSegura123!')
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $updatedPassword = $user->fresh()->password;

        self::assertTrue(Hash::check('NuevaClaveSegura123!', $updatedPassword));
        self::assertSame('argon2id', password_get_info($updatedPassword)['algoName']);
        self::assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->postReset($token, $user->email, 'OtraClaveSegura123!')
            ->assertSessionHasErrors('email');
        self::assertTrue(Hash::check('NuevaClaveSegura123!', $user->fresh()->password));
    }

    #[DataProvider('validResetPasswordBoundaries')]
    public function test_valid_password_boundaries_can_reset_password(string $password): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);

        $this->postReset($token, $user->email, $password)
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        self::assertTrue(Hash::check($password, $user->fresh()->password));
    }

    public function test_invalid_token_does_not_change_password(): void
    {
        $user = $this->createUser();

        $this->postReset('token-invalido', $user->email, 'NuevaClaveSegura123!')
            ->assertSessionHasErrors('email');

        self::assertTrue(Hash::check('ClaveSegura123!', $user->fresh()->password));
    }

    public function test_expired_token_does_not_change_password(): void
    {
        $user = $this->createUser();
        $token = Password::createToken($user);
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);

        $this->postReset($token, $user->email, 'NuevaClaveSegura123!')
            ->assertSessionHasErrors('email');

        self::assertTrue(Hash::check('ClaveSegura123!', $user->fresh()->password));
    }

    /**
     * @param  array<string, string>  $overrides
     */
    #[DataProvider('invalidResetData')]
    public function test_invalid_reset_input_is_rejected(array $overrides, string $field): void
    {
        $payload = array_replace([
            '_token' => 'test-token',
            'token' => 'token-prueba',
            'email' => 'maria@example.test',
            'password' => 'NuevaClaveSegura123!',
            'password_confirmation' => 'NuevaClaveSegura123!',
        ], $overrides);

        $this->withSession(['_token' => 'test-token'])
            ->from('/restablecer-clave/token-prueba')
            ->post('/restablecer-clave', $payload)
            ->assertSessionHasErrors($field);
    }

    public function test_excessive_invalid_token_attempts_are_rate_limited(): void
    {
        for ($request = 1; $request <= 5; $request++) {
            $this->postReset('token-invalido', 'maria@example.test', 'NuevaClaveSegura123!')
                ->assertRedirect();
        }

        $this->postReset('token-invalido', 'maria@example.test', 'NuevaClaveSegura123!')
            ->assertTooManyRequests();
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function invalidResetData(): array
    {
        return [
            'token vacío' => [['token' => ''], 'token'],
            'contraseña de 14 caracteres' => [['password' => '12345678901234', 'password_confirmation' => '12345678901234'], 'password'],
            'contraseña de 65 emojis' => [['password' => str_repeat('😀', 65), 'password_confirmation' => str_repeat('😀', 65)], 'password'],
            'confirmación distinta' => [['password_confirmation' => 'OtraClaveSegura123!'], 'password'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validResetPasswordBoundaries(): array
    {
        return [
            '15 caracteres' => [str_repeat('a', 15)],
            '64 emojis' => [str_repeat('😀', 64)],
        ];
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);
    }

    private function postReset(string $token, string $email, string $password): TestResponse
    {
        return $this->withSession(['_token' => 'test-token'])
            ->from('/restablecer-clave/'.$token.'?email='.urlencode($email))
            ->post('/restablecer-clave', [
                '_token' => 'test-token',
                'token' => $token,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]);
    }
}
