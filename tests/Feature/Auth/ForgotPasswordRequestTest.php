<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendPasswordResetLink;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ForgotPasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_MESSAGE = 'Si existe una cuenta asociada, recibirás un enlace para restablecer tu contraseña.';

    public function test_existing_email_receives_generic_response_and_enqueues_job(): void
    {
        Queue::fake();
        Password::shouldReceive('sendResetLink')->never();
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $response = $this->postRecoveryRequest($user->email);

        $response
            ->assertRedirect('/recuperar-clave')
            ->assertSessionHas('status', self::STATUS_MESSAGE);
        Queue::assertPushed(SendPasswordResetLink::class, 1);
    }

    public function test_unknown_email_receives_same_response_and_enqueues_job(): void
    {
        Queue::fake();
        Password::shouldReceive('sendResetLink')->never();

        $response = $this->postRecoveryRequest('desconocida@example.test');

        $response
            ->assertRedirect('/recuperar-clave')
            ->assertSessionHas('status', self::STATUS_MESSAGE);
        Queue::assertPushed(SendPasswordResetLink::class, 1);
    }

    public function test_job_notifies_existing_user(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        (new SendPasswordResetLink($user->email))->handle();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_job_does_not_notify_unknown_email(): void
    {
        Notification::fake();

        (new SendPasswordResetLink('desconocida@example.test'))->handle();

        Notification::assertNothingSent();
    }

    public function test_job_is_queued_and_encrypted(): void
    {
        $job = new SendPasswordResetLink('maria@example.test');

        self::assertInstanceOf(ShouldQueue::class, $job);
        self::assertInstanceOf(ShouldBeEncrypted::class, $job);
        self::assertSame('database', $job->connection);
    }

    public function test_smtp_failure_does_not_change_the_http_response(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp-inexistente.invalid',
            'queue.default' => 'database',
        ]);
        $email = 'maria@example.test';

        $this->postRecoveryRequest($email)
            ->assertRedirect('/recuperar-clave')
            ->assertSessionHas('status', self::STATUS_MESSAGE);

        self::assertDatabaseCount('jobs', 1);
        self::assertStringNotContainsString(
            $email,
            (string) DB::table('jobs')->value('payload'),
        );
    }

    #[DataProvider('invalidEmails')]
    public function test_invalid_email_is_rejected(string $email): void
    {
        $this->postRecoveryRequest($email)
            ->assertRedirect('/recuperar-clave')
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
        Queue::fake();

        for ($request = 1; $request <= 5; $request++) {
            $this->postRecoveryRequest('persona@example.test')
                ->assertRedirect();
        }

        $this->postRecoveryRequest('persona@example.test')
            ->assertTooManyRequests();
    }

    public function test_different_emails_behind_same_ip_do_not_share_email_limit(): void
    {
        Queue::fake();

        for ($request = 1; $request <= 6; $request++) {
            $this->postRecoveryRequest("persona{$request}@example.test")
                ->assertRedirect();
        }
    }

    private function postRecoveryRequest(string $email): TestResponse
    {
        return $this->withSession(['_token' => 'test-token'])
            ->from('/recuperar-clave')
            ->post('/recuperar-clave', [
                '_token' => 'test-token',
                'email' => $email,
            ]);
    }
}
