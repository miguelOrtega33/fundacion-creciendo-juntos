<?php

namespace Tests\Feature;

use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\SecurityEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TypeError;

final class SecurityEventRecorderTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidSnapshots')]
    public function test_rejects_unapproved_fields_and_values(
        SecurityEventAction $action,
        array $previous,
        array $new,
    ): void {
        [$actor, $target] = $this->users();

        $this->expectException(InvalidArgumentException::class);

        DB::transaction(fn () => app(SecurityEventRecorder::class)
            ->record($actor, $target, $action, $previous, $new));
    }

    public static function invalidSnapshots(): array
    {
        return [
            'clave genérica' => [SecurityEventAction::RoleAssigned, ['valor' => 'editor'], ['role' => 'administrador']],
            'estructura anidada' => [SecurityEventAction::RoleAssigned, ['role' => ['editor']], ['role' => 'administrador']],
            'rol inválido' => [SecurityEventAction::RoleAssigned, ['role' => 'editor'], ['role' => 'propietario']],
            'secreto' => [SecurityEventAction::UserCreated, [], ['role' => 'editor', 'disabled_at' => null, 'password' => 'secreto']],
            'fecha inválida' => [SecurityEventAction::UserDisabled, ['disabled_at' => null], ['disabled_at' => '2026-13-01']],
        ];
    }

    public function test_accepts_approved_fields_in_any_order(): void
    {
        [$actor, $target] = $this->users();

        $eventId = DB::transaction(fn () => app(SecurityEventRecorder::class)->record(
            $actor,
            $target,
            SecurityEventAction::UserCreated,
            [],
            ['disabled_at' => null, 'role' => 'editor'],
        ));

        self::assertDatabaseHas('security_events', ['id' => $eventId]);
    }

    public function test_accepts_system_actor_only_for_user_creation(): void
    {
        $target = $this->createUser('objetivo');

        $target->forceFill(['role' => UserRole::SuperAdministrator])->save();
        $eventId = DB::transaction(fn () => app(SecurityEventRecorder::class)
            ->recordFirstSuperAdministratorCreation($target));

        self::assertDatabaseHas('security_events', [
            'id' => $eventId,
            'actor_id' => null,
            'target_user_id' => $target->id,
        ]);
    }

    public function test_system_writer_rejects_an_ordinary_user(): void
    {
        $target = $this->createUser('objetivo');

        $this->expectException(InvalidArgumentException::class);

        DB::transaction(fn () => app(SecurityEventRecorder::class)
            ->recordFirstSuperAdministratorCreation($target));
    }

    public function test_general_writer_rejects_a_system_actor(): void
    {
        $target = $this->createUser('objetivo');

        $this->expectException(TypeError::class);

        DB::transaction(fn () => app(SecurityEventRecorder::class)->record(
            null,
            $target,
            SecurityEventAction::UserCreated,
            [],
            ['role' => 'editor', 'disabled_at' => null],
        ));
    }

    /** @return array{User, User} */
    private function users(): array
    {
        return [
            $this->createUser('actor'),
            $this->createUser('objetivo'),
        ];
    }

    private function createUser(string $name): User
    {
        return User::create([
            'name' => ucfirst($name),
            'email' => $name.'.'.Str::uuid().'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
    }
}
