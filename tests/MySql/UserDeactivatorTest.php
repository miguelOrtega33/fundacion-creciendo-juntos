<?php

namespace Tests\MySql;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserDeactivator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

final class UserDeactivatorTest extends TestCase
{
    public function test_deactivation_revokes_access_and_records_the_event(): void
    {
        [$actor, $target, $other] = $this->users();
        $actor->forceFill(['role' => UserRole::Administrator])->save();
        $targetSession = (string) Str::uuid();
        $otherSession = (string) Str::uuid();

        try {
            $this->seedAccess($target, $targetSession);
            $this->insertSession($otherSession, $other->id);

            app(UserDeactivator::class)->deactivate($actor, $target);

            $target->refresh();
            self::assertNotNull($target->disabled_at);
            self::assertNull($target->remember_token);
            self::assertDatabaseMissing('sessions', ['id' => $targetSession]);
            self::assertDatabaseHas('sessions', ['id' => $otherSession]);
            self::assertDatabaseMissing('password_reset_tokens', ['email' => $target->email]);
            self::assertDatabaseHas('security_events', [
                'actor_id' => $actor->id,
                'target_user_id' => $target->id,
                'action' => 'usuario.desactivar',
                'result' => 'exitoso',
            ]);
            $event = DB::table('security_events')->where('target_user_id', $target->id)->first();
            self::assertSame(['disabled_at' => null], json_decode($event->previous_values, true));
            self::assertSame($target->disabled_at->toAtomString(), json_decode($event->new_values, true)['disabled_at']);
        } finally {
            $this->clean([$actor, $target, $other]);
        }
    }

    public function test_critical_authorization_rules_reject_the_operation(): void
    {
        config(['session.connection' => null, 'session.driver' => 'database', 'session.table' => 'sessions']);
        [$super, $editor, $other] = $this->users();
        $administrator = $this->createUser('administrador', UserRole::Administrator);
        $disabledSuper = $this->createUser('super-inactivo', UserRole::SuperAdministrator);
        $disabledSuper->forceFill(['disabled_at' => now()])->save();

        try {
            foreach ([[$super, $super], [$editor, $other], [$administrator, $super], [$disabledSuper, $other]] as [$actor, $target]) {
                try {
                    app(UserDeactivator::class)->deactivate($actor, $target);
                    self::fail('La operación sin permiso debía ser rechazada.');
                } catch (AuthorizationException) {
                    self::assertNull($target->fresh()->disabled_at);
                }
            }
        } finally {
            $this->clean([$super, $editor, $other, $administrator, $disabledSuper]);
        }
    }

    public function test_failure_rolls_back_every_access_change(): void
    {
        [$actor, $target, $other] = $this->users();
        $session = (string) Str::uuid();

        try {
            $this->seedAccess($target, $session);
            $this->app->bind(SecurityEventWriter::class, fn (): SecurityEventWriter => new class implements SecurityEventWriter
            {
                public function record(
                    User $actor,
                    User $target,
                    SecurityEventAction $action,
                    array $previousValues,
                    array $newValues,
                ): int {
                    throw new LogicException('Fallo controlado al registrar auditoría.');
                }
            });

            try {
                app(UserDeactivator::class)->deactivate($actor, $target);
                self::fail('El fallo de auditoría debía provocar un rollback.');
            } catch (LogicException) {
                $target->refresh();
                self::assertNull($target->disabled_at);
                self::assertNotNull($target->remember_token);
                self::assertDatabaseHas('sessions', ['id' => $session]);
                self::assertDatabaseHas('password_reset_tokens', ['email' => $target->email]);
            }
        } finally {
            $this->clean([$actor, $target, $other]);
        }
    }

    /** @return array{User, User, User} */
    private function users(): array
    {
        return [
            $this->createUser('super', UserRole::SuperAdministrator),
            $this->createUser('objetivo', UserRole::Editor),
            $this->createUser('otra', UserRole::Editor),
        ];
    }

    private function createUser(string $name, UserRole $role): User
    {
        $user = User::create([
            'name' => ucfirst($name),
            'email' => $name.'.'.Str::uuid().'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function seedAccess(User $user, string $session): void
    {
        config(['session.connection' => null, 'session.driver' => 'database', 'session.table' => 'sessions']);
        $user->forceFill(['remember_token' => 'token-recordarme'])->save();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => 'token-hasheado',
            'created_at' => now(),
        ]);
        $this->insertSession($session, $user->id);
    }

    private function insertSession(string $id, int $userId): void
    {
        DB::table('sessions')->insert([
            'id' => $id, 'user_id' => $userId, 'payload' => 'prueba',
            'last_activity' => now()->timestamp,
        ]);
    }

    /** @param array<int, User> $users */
    private function clean(array $users): void
    {
        $ids = array_map(fn (User $user): int => $user->id, $users);
        DB::table('security_events')->whereIn('actor_id', $ids)->orWhereIn('target_user_id', $ids)->delete();
        DB::table('sessions')->whereIn('user_id', $ids)->delete();
        DB::table('password_reset_tokens')->whereIn('email', array_column($users, 'email'))->delete();
        User::whereKey($ids)->delete();
    }
}
