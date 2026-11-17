<?php

namespace Tests\Feature;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserRoleAssigner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class UserRoleAssignerTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_role_revokes_only_target_access_and_records_event(): void
    {
        $actor = $this->user('actor', UserRole::SuperAdministrator);
        $target = $this->user('objetivo', UserRole::Editor);
        $other = $this->user('otra', UserRole::Editor);
        $this->seedSession('objetivo-session', $target);
        $this->seedSession('otra-session', $other);
        $target->forceFill(['remember_token' => 'recordarme'])->save();

        $result = app(UserRoleAssigner::class)->assign($actor, $target, UserRole::Administrator);

        self::assertSame(UserRole::Administrator, $result->role);
        self::assertNull($result->remember_token);
        self::assertDatabaseMissing('sessions', ['id' => 'objetivo-session']);
        self::assertDatabaseHas('sessions', ['id' => 'otra-session']);
        self::assertDatabaseHas('security_events', [
            'actor_id' => $actor->id,
            'target_user_id' => $target->id,
            'action' => SecurityEventAction::RoleAssigned->value,
            'previous_values' => json_encode(['role' => UserRole::Editor->value]),
            'new_values' => json_encode(['role' => UserRole::Administrator->value]),
        ]);
    }

    #[DataProvider('unauthorizedActors')]
    public function test_rejects_unauthorized_actor(UserRole $actorRole, bool $self, bool $disabled): void
    {
        config(['session.connection' => null, 'session.driver' => 'database', 'session.table' => 'sessions']);
        $actor = $this->user('actor', $actorRole);
        $target = $self ? $actor : $this->user('objetivo', UserRole::Editor);
        if ($self) {
            $this->user('respaldo', UserRole::SuperAdministrator);
        }
        if ($disabled) {
            $actor->forceFill(['disabled_at' => now()])->save();
        }

        $this->expectException(AuthorizationException::class);

        app(UserRoleAssigner::class)->assign($actor, $target, UserRole::Administrator);
    }

    public static function unauthorizedActors(): array
    {
        return [
            'editor' => [UserRole::Editor, false, false],
            'administrador' => [UserRole::Administrator, false, false],
            'autoasignación' => [UserRole::SuperAdministrator, true, false],
            'actor desactivado' => [UserRole::SuperAdministrator, false, true],
        ];
    }

    public function test_rejects_same_role_without_side_effects(): void
    {
        $actor = $this->user('actor', UserRole::SuperAdministrator);
        $target = $this->user('objetivo', UserRole::Editor);

        try {
            app(UserRoleAssigner::class)->assign($actor, $target, UserRole::Editor);
            self::fail('El rol idéntico debía ser rechazado.');
        } catch (LogicException) {
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public function test_rolls_back_role_and_access_when_event_fails(): void
    {
        $actor = $this->user('actor', UserRole::SuperAdministrator);
        $target = $this->user('objetivo', UserRole::Editor);
        $this->seedSession('objetivo-session', $target);
        $target->forceFill(['remember_token' => 'recordarme'])->save();
        $this->app->bind(SecurityEventWriter::class, fn (): SecurityEventWriter => new class implements SecurityEventWriter
        {
            public function record(
                User $actor,
                User $target,
                SecurityEventAction $action,
                array $previousValues,
                array $newValues,
            ): int {
                throw new RuntimeException('Fallo controlado de auditoría.');
            }
        });

        try {
            app(UserRoleAssigner::class)->assign($actor, $target, UserRole::Administrator);
            self::fail('El fallo de auditoría debía provocar rollback.');
        } catch (RuntimeException) {
            $target->refresh();
            self::assertSame(UserRole::Editor, $target->role);
            self::assertSame('recordarme', $target->remember_token);
            self::assertDatabaseHas('sessions', ['id' => 'objetivo-session']);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    private function user(string $name, UserRole $role): User
    {
        $user = User::create([
            'name' => ucfirst($name),
            'email' => $name.'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function seedSession(string $id, User $user): void
    {
        config(['session.connection' => null, 'session.driver' => 'database', 'session.table' => 'sessions']);
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'payload' => 'prueba',
            'last_activity' => now()->timestamp,
        ]);
    }
}
