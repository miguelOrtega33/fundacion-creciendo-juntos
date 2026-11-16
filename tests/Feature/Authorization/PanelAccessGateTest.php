<?php

namespace Tests\Feature\Authorization;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class PanelAccessGateTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('activeRoles')]
    public function test_active_roles_can_access_the_panel(UserRole $role): void
    {
        $user = $this->user($role);

        self::assertTrue(Gate::forUser($user)->allows('panel.acceder'));
        $this->actingAs($user)->get('/panel')->assertOk();
    }

    public function test_disabled_user_is_denied_by_the_gate(): void
    {
        $user = $this->user(UserRole::SuperAdministrator);
        $user->forceFill(['disabled_at' => now()])->save();

        self::assertFalse(Gate::forUser($user)->allows('panel.acceder'));
    }

    public static function activeRoles(): array
    {
        return [
            'editor' => [UserRole::Editor],
            'administrador' => [UserRole::Administrator],
            'superadministrador' => [UserRole::SuperAdministrator],
        ];
    }

    private function user(UserRole $role): User
    {
        $user = User::create([
            'name' => 'Usuario '.$role->value,
            'email' => $role->value.'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
