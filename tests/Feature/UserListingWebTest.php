<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserListingWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_listing(): void
    {
        $this->get(route('users.index'))->assertRedirect('/login');
    }

    public function test_editor_cannot_access_user_listing(): void
    {
        $editor = $this->user(UserRole::Editor, 'actor-editor');

        $this->actingAs($editor)->get(route('users.index'))->assertForbidden();
        $this->get(route('panel'))->assertDontSee('Administrar usuarios');
    }

    public function test_administrator_receives_only_editor_accounts(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'actor-administrador');
        $editor = $this->user(UserRole::Editor, 'editor-visible');
        $this->user(UserRole::Administrator, 'administrador-oculto');
        $this->user(UserRole::SuperAdministrator, 'super-oculto');

        $this->actingAs($administrator)->get(route('users.index'))
            ->assertOk()
            ->assertViewIs('users.index')
            ->assertViewHas('users', function (LengthAwarePaginator $users) use ($editor): bool {
                return $users->getCollection()->modelKeys() === [$editor->id];
            });
        $this->get(route('panel'))->assertSee('Administrar usuarios');
    }

    public function test_super_administrator_receives_every_account(): void
    {
        $superAdministrator = $this->user(UserRole::SuperAdministrator, 'actor-super');
        $editor = $this->user(UserRole::Editor, 'editor');
        $administrator = $this->user(UserRole::Administrator, 'administrador');

        $this->actingAs($superAdministrator)->get(route('users.index'))
            ->assertOk()
            ->assertViewHas('users', function (LengthAwarePaginator $users) use (
                $superAdministrator,
                $editor,
                $administrator,
            ): bool {
                return $users->getCollection()->modelKeys() === [
                    $superAdministrator->id,
                    $editor->id,
                    $administrator->id,
                ];
            });
    }

    public function test_listing_renders_authorized_details_and_account_status(): void
    {
        $superAdministrator = $this->user(UserRole::SuperAdministrator, 'actor-super');
        $disabledEditor = $this->user(UserRole::Editor, 'editora-desactivada');
        $disabledEditor->forceFill(['disabled_at' => now()])->save();

        $this->actingAs($superAdministrator)->get(route('users.index'))
            ->assertOk()
            ->assertSee('Actor-super')
            ->assertSee('actor-super@example.test')
            ->assertSee('Superadministrador')
            ->assertSee('Activa')
            ->assertSee('Editora-desactivada')
            ->assertSee('editora-desactivada@example.test')
            ->assertSee('Desactivada')
            ->assertDontSee('Eliminar cuenta');
    }

    public function test_administrator_sees_accessible_empty_state_without_editors(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'actor-administrador');

        $this->actingAs($administrator)->get(route('users.index'))
            ->assertOk()
            ->assertSee('No hay usuarios para mostrar')
            ->assertSee('role="status"', false)
            ->assertDontSee('<table', false);
    }

    private function user(UserRole $role, string $name): User
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
