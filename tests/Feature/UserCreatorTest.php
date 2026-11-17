<?php

namespace Tests\Feature;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\Auth\UserCreator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class UserCreatorTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('allowedCreations')]
    public function test_creates_active_user_with_authorized_role(UserRole $actorRole, UserRole $newRole): void
    {
        $actor = $this->actor($actorRole);

        $user = app(UserCreator::class)->create($actor, $newRole, $this->validAttributes());

        self::assertSame($newRole, $user->role);
        self::assertNull($user->disabled_at);
        self::assertTrue(Hash::check('ClaveSegura123!', $user->password));
        $event = SecurityEvent::query()->sole();
        self::assertSame($actor->id, $event->actor_id);
        self::assertSame($user->id, $event->target_user_id);
        self::assertSame(SecurityEventAction::UserCreated, $event->action);
        self::assertSame([], $event->previous_values);
        self::assertSame(['role' => $newRole->value, 'disabled_at' => null], $event->new_values);
        self::assertStringNotContainsString('ClaveSegura123!', json_encode($event->getAttributes(), JSON_THROW_ON_ERROR));
    }

    public static function allowedCreations(): array
    {
        return [
            'administrador crea editor' => [UserRole::Administrator, UserRole::Editor],
            'superadministrador crea editor' => [UserRole::SuperAdministrator, UserRole::Editor],
            'superadministrador crea administrador' => [UserRole::SuperAdministrator, UserRole::Administrator],
            'superadministrador crea superadministrador' => [UserRole::SuperAdministrator, UserRole::SuperAdministrator],
        ];
    }

    #[DataProvider('deniedCreations')]
    public function test_rejects_unauthorized_creation(UserRole $actorRole, UserRole $newRole, bool $disabled): void
    {
        $actor = $this->actor($actorRole);
        if ($disabled) {
            $actor->forceFill(['disabled_at' => now()])->save();
        }

        try {
            app(UserCreator::class)->create($actor, $newRole, $this->validAttributes());
            self::fail('La creación no autorizada debía ser rechazada.');
        } catch (AuthorizationException) {
            self::assertDatabaseCount('users', 1);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public static function deniedCreations(): array
    {
        return [
            'editor crea editor' => [UserRole::Editor, UserRole::Editor, false],
            'administrador crea administrador' => [UserRole::Administrator, UserRole::Administrator, false],
            'administrador crea superadministrador' => [UserRole::Administrator, UserRole::SuperAdministrator, false],
            'superadministrador desactivado' => [UserRole::SuperAdministrator, UserRole::Editor, true],
        ];
    }

    public function test_authorizes_before_validating_account_data(): void
    {
        $actor = $this->actor(UserRole::Editor);

        $this->expectException(AuthorizationException::class);

        app(UserCreator::class)->create($actor, UserRole::Editor, []);
    }

    public function test_uses_current_actor_role_from_database(): void
    {
        $actor = $this->actor(UserRole::SuperAdministrator);
        DB::table('users')->where('id', $actor->id)->update(['role' => UserRole::Editor->value]);

        $this->expectException(AuthorizationException::class);

        app(UserCreator::class)->create($actor, UserRole::Editor, $this->validAttributes());
    }

    #[DataProvider('invalidAttributes')]
    public function test_rejects_invalid_attributes(array $attributes): void
    {
        $actor = $this->actor(UserRole::SuperAdministrator);

        try {
            app(UserCreator::class)->create($actor, UserRole::Editor, $attributes);
            self::fail('Los datos inválidos debían ser rechazados.');
        } catch (ValidationException) {
            self::assertDatabaseCount('users', 1);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public static function invalidAttributes(): array
    {
        $valid = [
            'name' => 'Nueva persona',
            'email' => 'nueva@example.test',
            'password' => 'ClaveSegura123!',
        ];

        return [
            'nombre vacío' => [array_replace($valid, ['name' => ''])],
            'correo inválido' => [array_replace($valid, ['email' => 'correo-inválido'])],
            'contraseña corta' => [array_replace($valid, ['password' => 'muy-corta'])],
        ];
    }

    public function test_rejects_duplicate_email(): void
    {
        $actor = $this->actor(UserRole::SuperAdministrator);
        User::create($this->validAttributes());

        try {
            app(UserCreator::class)->create($actor, UserRole::Editor, $this->validAttributes());
            self::fail('El correo duplicado debía ser rechazado.');
        } catch (ValidationException) {
            self::assertDatabaseCount('users', 2);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public function test_rolls_back_user_when_security_event_fails(): void
    {
        $actor = $this->actor(UserRole::SuperAdministrator);
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
            app(UserCreator::class)->create($actor, UserRole::Editor, $this->validAttributes());
            self::fail('El fallo de auditoría debía provocar rollback.');
        } catch (RuntimeException) {
            self::assertDatabaseCount('users', 1);
            self::assertDatabaseCount('security_events', 0);
        }
    }

    public function test_rejects_unpersisted_actor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(UserCreator::class)->create(new User, UserRole::Editor, $this->validAttributes());
    }

    private function actor(UserRole $role): User
    {
        $actor = User::create([
            'name' => 'Actor',
            'email' => 'actor@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $actor->forceFill(['role' => $role])->save();

        return $actor;
    }

    private function validAttributes(): array
    {
        return [
            'name' => 'Nueva persona',
            'email' => 'nueva@example.test',
            'password' => 'ClaveSegura123!',
        ];
    }
}
