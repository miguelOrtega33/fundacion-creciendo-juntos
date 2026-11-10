<?php

namespace Tests\MySql;

use App\Jobs\SendPasswordResetLink;
use App\Models\User;
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

    private function runOneWorker(string $queue): void
    {
        $this->artisan('queue:work', [
            'connection' => 'database',
            '--queue' => $queue,
            '--once' => true,
            '--tries' => 1,
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
