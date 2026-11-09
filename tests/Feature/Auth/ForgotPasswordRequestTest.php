<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ForgotPasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MESSAGE = 'Si existe una cuenta asociada, recibirás un enlace para restablecer tu contraseña.';

    public function test_existing_user_receives_reset_notification(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response = $this->postRecoveryRequest($user->email);

        $response
            ->assertRedirect('/login')
            ->assertSessionHas('status', self::STATUS_MESSAGE);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_receives_same_response_without_notification(): void
    {
        Notification::fake();

        $response = $this->postRecoveryRequest('desconocida@example.test');

        $response
            ->assertRedirect('/login')
            ->assertSessionHas('status', self::STATUS_MESSAGE);
        Notification::assertNothingSent();
    }

    #[DataProvider('invalidEmails')]
    public function test_invalid_email_is_rejected(string $email): void
    {
        $this->postRecoveryRequest($email)
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEmails(): array
    {
        return [
            'correo vacío' => [''],
            'correo inválido' => ['no-es-correo'],
        ];
    }

    public function test_excessive_requests_are_rate_limited(): void
    {
        Notification::fake();

        for ($request = 1; $request <= 5; $request++) {
            $this->postRecoveryRequest("persona{$request}@example.test")
                ->assertRedirect();
        }

        $this->postRecoveryRequest('persona6@example.test')
            ->assertTooManyRequests();
    }

    private function postRecoveryRequest(string $email): TestResponse
    {
        return $this->withSession(['_token' => 'test-token'])
            ->from('/login')
            ->post('/recuperar-clave', [
                '_token' => 'test-token',
                'email' => $email,
            ]);
    }
}
