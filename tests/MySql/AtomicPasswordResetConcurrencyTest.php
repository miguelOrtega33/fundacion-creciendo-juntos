<?php

namespace Tests\MySql;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class AtomicPasswordResetConcurrencyTest extends TestCase
{
    public function test_only_one_concurrent_request_consumes_the_token(): void
    {
        $email = 'concurrencia.'.Str::uuid().'@example.test';
        $user = User::create([
            'name' => 'Usuario de concurrencia',
            'email' => $email,
            'password' => 'ClaveAnterior123!',
        ]);
        $token = Password::createToken($user);
        $passwords = [
            'NuevaClaveA123!',
            'NuevaClaveB123!',
            'NuevaClaveC123!',
            'NuevaClaveD123!',
        ];

        try {
            $processes = $this->startConcurrentResets($email, $token, $passwords);
            $statuses = [];

            foreach ($processes as $process) {
                $process->wait();
                self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
                $statuses[] = trim($process->getOutput());
            }

            self::assertSame(1, count(array_filter(
                $statuses,
                fn (string $status): bool => $status === Password::PASSWORD_RESET,
            )));
            self::assertSame(3, count(array_filter(
                $statuses,
                fn (string $status): bool => $status === Password::INVALID_TOKEN,
            )));
            self::assertTrue(collect($passwords)->contains(
                fn (string $password): bool => Hash::check($password, $user->fresh()->password),
            ));
            self::assertDatabaseMissing('password_reset_tokens', ['email' => $email]);
        } finally {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            User::where('email', $email)->delete();
        }
    }

    /**
     * @param  array<int, string>  $passwords
     * @return array<int, Process>
     */
    private function startConcurrentResets(string $email, string $token, array $passwords): array
    {
        $startAt = (string) (microtime(true) + 1.5);

        return array_map(function (string $password) use ($email, $token, $startAt): Process {
            $process = new Process(
                [PHP_BINARY, base_path('tests/Support/run_atomic_password_reset.php')],
                base_path(),
                [
                    'APP_ENV' => 'testing',
                    'MAIL_MAILER' => 'array',
                    'RESET_EMAIL' => $email,
                    'RESET_PASSWORD' => $password,
                    'RESET_START_AT' => $startAt,
                    'RESET_TOKEN' => $token,
                ],
            );
            $process->setTimeout(20);
            $process->start();

            return $process;
        }, $passwords);
    }
}
