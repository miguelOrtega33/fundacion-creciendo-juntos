<?php

namespace Tests\Feature\Authorization;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('permissions')]
    public function test_permission_matrix(
        UserRole $actorRole,
        string $ability,
        ?UserRole $targetRole,
        bool $self,
        ?UserRole $newRole,
        bool $expected,
    ): void {
        $actor = $this->user('actor', $actorRole);
        $target = $self ? $actor : ($targetRole ? $this->user('objetivo', $targetRole) : null);
        $arguments = match ($ability) {
            'viewAny' => [User::class],
            'create' => [User::class, $newRole],
            'assignRole' => [$target, $newRole],
            default => [$target],
        };

        self::assertSame($expected, Gate::forUser($actor)->allows($ability, $arguments));
    }

    public static function permissions(): array
    {
        return [
            'editor no lista' => [UserRole::Editor, 'viewAny', null, false, null, false],
            'administrador lista' => [UserRole::Administrator, 'viewAny', null, false, null, true],
            'superadministrador lista' => [UserRole::SuperAdministrator, 'viewAny', null, false, null, true],
            'editor se ve' => [UserRole::Editor, 'view', null, true, null, true],
            'editor no ve otra cuenta' => [UserRole::Editor, 'view', UserRole::Editor, false, null, false],
            'administrador ve editor' => [UserRole::Administrator, 'view', UserRole::Editor, false, null, true],
            'administrador no ve administrador' => [UserRole::Administrator, 'view', UserRole::Administrator, false, null, false],
            'superadministrador ve administrador' => [UserRole::SuperAdministrator, 'view', UserRole::Administrator, false, null, true],
            'editor no crea' => [UserRole::Editor, 'create', null, false, UserRole::Editor, false],
            'administrador crea editor' => [UserRole::Administrator, 'create', null, false, UserRole::Editor, true],
            'administrador no crea administrador' => [UserRole::Administrator, 'create', null, false, UserRole::Administrator, false],
            'superadministrador crea superadministrador' => [UserRole::SuperAdministrator, 'create', null, false, UserRole::SuperAdministrator, true],
            'editor actualiza sus datos' => [UserRole::Editor, 'update', null, true, null, true],
            'editor no actualiza otra cuenta' => [UserRole::Editor, 'update', UserRole::Editor, false, null, false],
            'administrador actualiza editor' => [UserRole::Administrator, 'update', UserRole::Editor, false, null, true],
            'administrador no actualiza superadministrador' => [UserRole::Administrator, 'update', UserRole::SuperAdministrator, false, null, false],
            'superadministrador actualiza administrador' => [UserRole::SuperAdministrator, 'update', UserRole::Administrator, false, null, true],
            'administrador desactiva editor' => [UserRole::Administrator, 'deactivate', UserRole::Editor, false, null, true],
            'administrador no desactiva administrador' => [UserRole::Administrator, 'deactivate', UserRole::Administrator, false, null, false],
            'superadministrador desactiva editor' => [UserRole::SuperAdministrator, 'deactivate', UserRole::Editor, false, null, true],
            'superadministrador no se desactiva' => [UserRole::SuperAdministrator, 'deactivate', null, true, null, false],
            'administrador no asigna rol' => [UserRole::Administrator, 'assignRole', UserRole::Editor, false, UserRole::Editor, false],
            'superadministrador asigna rol' => [UserRole::SuperAdministrator, 'assignRole', UserRole::Editor, false, UserRole::Administrator, true],
            'superadministrador no cambia su rol' => [UserRole::SuperAdministrator, 'assignRole', null, true, UserRole::Editor, false],
        ];
    }

    public function test_disabled_actor_is_denied_for_every_ability(): void
    {
        $actor = $this->user('actor-inactivo', UserRole::SuperAdministrator);
        $target = $this->user('objetivo', UserRole::Editor);
        $actor->forceFill(['disabled_at' => now()])->save();
        $checks = [
            ['viewAny', [User::class]],
            ['view', [$target]],
            ['create', [User::class, UserRole::Editor]],
            ['update', [$target]],
            ['deactivate', [$target]],
            ['assignRole', [$target, UserRole::Administrator]],
        ];

        foreach ($checks as [$ability, $arguments]) {
            self::assertFalse(Gate::forUser($actor)->allows($ability, $arguments));
        }
    }

    public function test_super_administrator_cannot_deactivate_self_with_another_super_active(): void
    {
        $actor = $this->user('actor', UserRole::SuperAdministrator);
        $this->user('respaldo', UserRole::SuperAdministrator);

        self::assertFalse(Gate::forUser($actor)->allows('deactivate', [$actor]));
    }

    public function test_super_administrator_cannot_assign_own_role_with_another_super_active(): void
    {
        $actor = $this->user('actor', UserRole::SuperAdministrator);
        $this->user('respaldo', UserRole::SuperAdministrator);

        self::assertFalse(Gate::forUser($actor)->allows('assignRole', [$actor, UserRole::Editor]));
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
}
