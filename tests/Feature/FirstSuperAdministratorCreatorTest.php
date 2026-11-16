<?php

namespace Tests\Feature;

use App\Contracts\SystemSecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\FirstSuperAdministratorCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class FirstSuperAdministratorCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_first_super_administrator_and_system_event_atomically(): void
    {
        $user = app(FirstSuperAdministratorCreator::class)->create($this->validAttributes());

        self::assertSame(UserRole::SuperAdministrator, $user->role);
        self::assertTrue(Hash::check('ClaveSegura123!', $user->password));
        self::assertDatabaseHas('security_events', [
            'actor_id' => null,
            'target_user_id' => $user->id,
            'action' => SecurityEventAction::UserCreated->value,
            'result' => 'exitoso',
        ]);

        $event = DB::table('security_events')->where('target_user_id', $user->id)->first();
        self::assertSame([], json_decode($event->previous_values, true, flags: JSON_THROW_ON_ERROR));
        self::assertSame(
            ['role' => UserRole::SuperAdministrator->value, 'disabled_at' => null],
            json_decode($event->new_values, true, flags: JSON_THROW_ON_ERROR),
        );
        self::assertStringNotContainsString('ClaveSegura123!', json_encode($event, JSON_THROW_ON_ERROR));
    }

    public function test_rejects_creation_when_an_active_super_administrator_exists(): void
    {
        app(FirstSuperAdministratorCreator::class)->create($this->validAttributes());

        $this->expectException(LogicException::class);

        app(FirstSuperAdministratorCreator::class)->create(array_replace(
            $this->validAttributes(),
            ['email' => 'otra@example.test'],
        ));
    }

    public function test_allows_creation_when_only_disabled_super_administrator_exists(): void
    {
        $disabled = User::create(array_replace($this->validAttributes(), ['email' => 'inactivo@example.test']));
        $disabled->forceFill([
            'role' => UserRole::SuperAdministrator,
            'disabled_at' => now(),
        ])->save();

        $created = app(FirstSuperAdministratorCreator::class)->create($this->validAttributes());

        self::assertSame(UserRole::SuperAdministrator, $created->role);
        self::assertNull($created->disabled_at);
    }

    public function test_rolls_back_user_when_security_event_fails(): void
    {
        $this->app->bind(SystemSecurityEventWriter::class, fn (): SystemSecurityEventWriter => new class implements SystemSecurityEventWriter
        {
            public function recordFirstSuperAdministratorCreation(User $target): int
            {
                throw new RuntimeException('Fallo controlado de auditoría.');
            }
        });

        try {
            app(FirstSuperAdministratorCreator::class)->create($this->validAttributes());
            self::fail('El fallo de auditoría debía provocar rollback.');
        } catch (RuntimeException) {
            self::assertDatabaseCount('users', 0);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public function test_migration_preserves_system_events_by_rejecting_unsafe_rollback(): void
    {
        app(FirstSuperAdministratorCreator::class)->create($this->validAttributes());
        $migration = require database_path('migrations/2026_11_16_020000_allow_system_security_event_actor.php');

        $this->expectException(LogicException::class);

        $migration->down();
    }

    #[DataProvider('invalidAttributes')]
    public function test_rejects_invalid_attributes(array $attributes): void
    {
        try {
            app(FirstSuperAdministratorCreator::class)->create($attributes);
            self::fail('Los datos inválidos debían ser rechazados.');
        } catch (ValidationException) {
            self::assertDatabaseCount('users', 0);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public static function invalidAttributes(): array
    {
        $valid = [
            'name' => 'Administración inicial',
            'email' => 'admin@example.test',
            'password' => 'ClaveSegura123!',
        ];

        return [
            'nombre vacío' => [array_replace($valid, ['name' => ''])],
            'correo inválido' => [array_replace($valid, ['email' => 'correo-inválido'])],
            'contraseña corta' => [array_replace($valid, ['password' => 'muy-corta'])],
        ];
    }

    private function validAttributes(): array
    {
        return [
            'name' => 'Administración inicial',
            'email' => 'admin@example.test',
            'password' => 'ClaveSegura123!',
        ];
    }
}
