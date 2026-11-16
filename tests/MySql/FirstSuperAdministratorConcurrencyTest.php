<?php

namespace Tests\MySql;

use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class FirstSuperAdministratorConcurrencyTest extends TestCase
{
    public function test_concurrent_bootstrap_creates_exactly_one_super_administrator(): void
    {
        $suffix = Str::uuid()->toString();
        $emails = ["primero.$suffix@example.test", "segundo.$suffix@example.test"];
        $startAt = (string) (microtime(true) + 1.5);

        try {
            $processes = [
                $this->start('Primero', $emails[0], $startAt),
                $this->start('Segundo', $emails[1], $startAt),
            ];
            $statuses = [];

            foreach ($processes as $process) {
                $process->wait();
                self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
                $statuses[] = trim($process->getOutput());
            }

            $users = User::query()->whereIn('email', $emails)->get();

            self::assertEqualsCanonicalizing(['creado', 'denegado'], $statuses);
            self::assertCount(1, $users);
            self::assertSame(UserRole::SuperAdministrator, $users->first()->role);
            self::assertSame(1, DB::table('security_events')
                ->whereNull('actor_id')
                ->where('target_user_id', $users->first()->id)
                ->where('action', SecurityEventAction::UserCreated->value)
                ->count());
        } finally {
            $userIds = User::query()->whereIn('email', $emails)->pluck('id');
            DB::table('security_events')->whereIn('target_user_id', $userIds)->delete();
            User::query()->whereKey($userIds)->delete();
        }
    }

    private function start(string $name, string $email, string $startAt): Process
    {
        $process = new Process(
            [PHP_BINARY, base_path('tests/Support/run_first_super_administrator_creation.php')],
            base_path(),
            [
                'BOOTSTRAP_EMAIL' => $email,
                'BOOTSTRAP_NAME' => $name,
                'BOOTSTRAP_START_AT' => $startAt,
            ],
        );
        $process->setTimeout(20);
        $process->start();

        return $process;
    }
}
