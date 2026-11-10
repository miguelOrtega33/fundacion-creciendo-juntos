<?php

namespace Tests\MySql;

use App\Jobs\SendPasswordResetLink;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Tests\TestCase;

final class QueuedPasswordResetDeliveryTest extends TestCase
{
    public function test_encrypted_jobs_are_consumed_and_deliver_the_reset_email(): void
    {
        $queue = 'reset-'.Str::uuid();
        $email = 'cola.'.Str::uuid().'@example.test';

        config([
            'mail.default' => 'array',
            'queue.default' => 'database',
            'queue.connections.database.queue' => $queue,
        ]);

        $transport = Mail::mailer('array')->getSymfonyTransport();
        self::assertInstanceOf(ArrayTransport::class, $transport);
        $transport->flush();

        User::create([
            'name' => 'Usuario de cola',
            'email' => $email,
            'password' => 'ClaveAnterior123!',
        ]);

        try {
            SendPasswordResetLink::dispatch($email);

            $outerPayload = (string) DB::table('jobs')
                ->where('queue', $queue)
                ->value('payload');

            $this->assertEncryptedPayload($outerPayload, $email);
            self::assertSame(1, DB::table('jobs')->where('queue', $queue)->count());

            $this->runOneWorker($queue);

            self::assertDatabaseHas('password_reset_tokens', ['email' => $email]);
            self::assertSame(1, DB::table('jobs')->where('queue', $queue)->count());

            $notificationPayload = (string) DB::table('jobs')
                ->where('queue', $queue)
                ->value('payload');

            $this->assertEncryptedPayload($notificationPayload, $email);

            $this->runOneWorker($queue);

            self::assertSame(0, DB::table('jobs')->where('queue', $queue)->count());
            self::assertSame(0, DB::table('failed_jobs')->where('queue', $queue)->count());
            self::assertCount(1, $transport->messages());
            self::assertSame(
                [$email],
                array_map(
                    static fn (Address $recipient): string => $recipient->getAddress(),
                    $transport->messages()->sole()->getEnvelope()->getRecipients(),
                ),
            );
        } finally {
            DB::table('jobs')->where('queue', $queue)->delete();
            DB::table('failed_jobs')->where('queue', $queue)->delete();
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            User::where('email', $email)->delete();
            $transport->flush();
        }
    }

    public function test_transient_mail_failure_is_delayed_and_can_recover(): void
    {
        $queue = 'smtp-'.Str::uuid();
        $email = 'smtp.'.Str::uuid().'@example.test';
        $transport = null;

        config([
            'logging.default' => 'null',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
            'queue.default' => 'database',
            'queue.connections.database.queue' => $queue,
        ]);
        Mail::purge('smtp');

        $user = User::create([
            'name' => 'Usuario con falla SMTP',
            'email' => $email,
            'password' => 'ClaveAnterior123!',
        ]);

        try {
            $user->notify(new ResetPasswordNotification('token-prueba'));
            $failureStartedAt = now()->timestamp;

            $this->runOneWorker($queue);

            $releasedJob = DB::table('jobs')->where('queue', $queue)->first();

            self::assertNotNull($releasedJob);
            self::assertSame(1, $releasedJob->attempts);
            self::assertGreaterThanOrEqual($failureStartedAt + 55, $releasedJob->available_at);
            self::assertLessThanOrEqual(now()->timestamp + 65, $releasedJob->available_at);
            self::assertSame(0, DB::table('failed_jobs')->where('queue', $queue)->count());

            DB::table('jobs')->where('queue', $queue)->update([
                'available_at' => now()->timestamp,
            ]);
            config(['mail.default' => 'array']);
            $transport = Mail::mailer('array')->getSymfonyTransport();
            self::assertInstanceOf(ArrayTransport::class, $transport);
            $transport->flush();

            $this->runOneWorker($queue);

            self::assertSame(0, DB::table('jobs')->where('queue', $queue)->count());
            self::assertSame(0, DB::table('failed_jobs')->where('queue', $queue)->count());
            self::assertCount(1, $transport->messages());
        } finally {
            DB::table('jobs')->where('queue', $queue)->delete();
            DB::table('failed_jobs')->where('queue', $queue)->delete();
            User::where('email', $email)->delete();
            $transport?->flush();
        }
    }

    private function runOneWorker(string $queue): void
    {
        $this->artisan('queue:work', [
            'connection' => 'database',
            '--queue' => $queue,
            '--once' => true,
            '--tries' => 3,
        ])->assertSuccessful();
    }

    private function assertEncryptedPayload(string $payload, string $secret): void
    {
        /** @var array{data: array{command: string}} $decodedPayload */
        $decodedPayload = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        $command = $decodedPayload['data']['command'];

        self::assertNotSame('', $command);
        self::assertFalse(str_starts_with($command, 'O:'));
        self::assertStringNotContainsString($secret, $payload);
    }
}
