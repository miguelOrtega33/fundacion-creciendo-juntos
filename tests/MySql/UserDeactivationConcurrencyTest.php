<?php

namespace Tests\MySql;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class UserDeactivationConcurrencyTest extends TestCase
{
    public function test_concurrent_requests_preserve_one_active_super_administrator(): void
    {
        $first = $this->createSuperAdministrator('primero');
        $second = $this->createSuperAdministrator('segundo');
        $startAt = (string) (microtime(true) + 1.5);

        try {
            $processes = [
                $this->start($first, $second, $startAt),
                $this->start($second, $first, $startAt),
            ];
            $statuses = [];

            foreach ($processes as $process) {
                $process->wait();
                self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
                $statuses[] = trim($process->getOutput());
            }

            self::assertEqualsCanonicalizing(['desactivado', 'denegado'], $statuses);
            self::assertSame(1, User::whereKey([$first->id, $second->id])->whereNull('disabled_at')->count());
            self::assertSame(1, DB::table('security_events')->whereIn('target_user_id', [$first->id, $second->id])->count());
        } finally {
            DB::table('security_events')->whereIn('actor_id', [$first->id, $second->id])->delete();
            User::whereKey([$first->id, $second->id])->delete();
        }
    }

    private function start(User $actor, User $target, string $startAt): Process
    {
        $process = new Process(
            [PHP_BINARY, base_path('tests/Support/run_user_deactivation.php')],
            base_path(),
            [
                'DEACTIVATE_ACTOR_ID' => (string) $actor->id,
                'DEACTIVATE_START_AT' => $startAt,
                'DEACTIVATE_TARGET_ID' => (string) $target->id,
                'SESSION_DRIVER' => 'database',
            ],
        );
        $process->setTimeout(20);
        $process->start();

        return $process;
    }

    private function createSuperAdministrator(string $name): User
    {
        $user = User::create([
            'name' => ucfirst($name),
            'email' => $name.'.'.Str::uuid().'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => UserRole::SuperAdministrator])->save();

        return $user;
    }
}
