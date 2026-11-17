<?php

namespace Tests\MySql;

use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserCreator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class UserCreatorConcurrencyTest extends TestCase
{
    public function test_concurrent_duplicate_email_becomes_validation_error(): void
    {
        $actors = [$this->superAdministrator(), $this->superAdministrator()];
        $email = 'carrera.'.Str::uuid().'@example.test';
        DB::beginTransaction();
        User::query()->where('email', $email)->lockForUpdate()->get();

        try {
            $startAt = (string) (microtime(true) + 1.5);
            $processes = [$this->start('create', $actors[0], $email, $startAt), $this->start('create', $actors[1], $email, $startAt)];
            usleep(2_000_000);
            DB::commit();
            foreach ($processes as $process) {
                $process->wait();
                self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
            }
            self::assertEqualsCanonicalizing(['creado', 'correo_duplicado'], array_map(
                fn (Process $process): string => trim($process->getOutput()),
                $processes,
            ));
            $target = User::query()->where('email', $email)->sole();
            self::assertSame(1, DB::table('security_events')->where('target_user_id', $target->id)
                ->where('action', SecurityEventAction::UserCreated->value)->count());
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $actorIds = array_map(fn (User $user): int => $user->id, $actors);
            DB::table('security_events')->whereIn('actor_id', $actorIds)->delete();
            User::query()->whereIn('id', $actorIds)->orWhere('email', $email)->delete();
        }
    }

    public function test_waits_for_actor_role_change_before_authorizing(): void
    {
        $actor = $this->superAdministrator();
        $email = 'bloqueo.'.Str::uuid().'@example.test';
        $process = $this->start('degrade', $actor);

        try {
            self::assertTrue($process->waitUntil(fn (): bool => str_contains($process->getOutput(), 'actualizado')));
            try {
                app(UserCreator::class)->create($actor, UserRole::Editor, [
                    'name' => 'Cuenta bloqueada', 'email' => $email, 'password' => 'ClaveSegura123!',
                ]);
                self::fail('La creación debía usar el rol confirmado por la transacción concurrente.');
            } catch (AuthorizationException) {
                self::assertSame(UserRole::Editor, $actor->refresh()->role);
                self::assertDatabaseMissing('users', ['email' => $email]);
                self::assertDatabaseCount('security_events', 0);
            }
        } finally {
            $process->wait();
            DB::table('security_events')->where('actor_id', $actor->id)->delete();
            User::query()->where('id', $actor->id)->orWhere('email', $email)->delete();
        }
    }

    private function start(string $mode, User $actor, string $email = '', string $startAt = '0'): Process
    {
        $process = new Process([PHP_BINARY, base_path('tests/Support/run_user_creation.php')], base_path(), [
            'USER_CREATOR_ACTOR_ID' => (string) $actor->id,
            'USER_CREATOR_EMAIL' => $email,
            'USER_CREATOR_MODE' => $mode,
            'USER_CREATOR_START_AT' => $startAt,
        ]);
        $process->setTimeout(20);
        $process->start();

        return $process;
    }

    private function superAdministrator(): User
    {
        $user = User::create([
            'name' => 'Actor', 'email' => Str::uuid().'@example.test', 'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => UserRole::SuperAdministrator])->save();

        return $user;
    }
}
