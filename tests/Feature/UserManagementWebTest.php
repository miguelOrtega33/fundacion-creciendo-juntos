<?php

namespace Tests\Feature;

use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class UserManagementWebTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('privateRoutes')]
    public function test_guest_cannot_access_user_creation(string $method, string $route): void
    {
        $this->{$method}(route($route))->assertRedirect('/login');
    }

    public static function privateRoutes(): array
    {
        return [['get', 'users.create'], ['post', 'users.store']];
    }

    public function test_editor_cannot_access_or_submit_user_creation(): void
    {
        $editor = $this->user(UserRole::Editor, 'editor');

        $this->actingAs($editor)->get(route('users.create'))->assertForbidden();
        $this->actingAs($editor)->post(route('users.store'), $this->validData())->assertForbidden();
        self::assertDatabaseCount('users', 1);
    }

    public function test_administrator_sees_only_editor_role_and_panel_link(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'administrador');

        $this->actingAs($administrator)->get(route('panel'))
            ->assertSee('Crear cuenta de usuario');
        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('value="editor"', false)
            ->assertDontSee('value="administrador"', false)
            ->assertDontSee('value="superadministrador"', false);
    }

    public function test_super_administrator_sees_every_role_and_accessible_fields(): void
    {
        $superAdministrator = $this->user(UserRole::SuperAdministrator, 'super');

        $this->actingAs($superAdministrator)->get(route('users.create'))
            ->assertOk()
            ->assertSee('value="editor"', false)
            ->assertSee('value="administrador"', false)
            ->assertSee('value="superadministrador"', false)
            ->assertSee('label for="name"', false)
            ->assertSee('label for="password_confirmation"', false)
            ->assertSee('aria-describedby="password-help"', false);
    }

    public function test_editor_does_not_see_creation_link(): void
    {
        $editor = $this->user(UserRole::Editor, 'editor');

        $this->actingAs($editor)->get(route('panel'))->assertDontSee('Crear cuenta de usuario');
    }

    public function test_administrator_creates_editor_through_web_flow(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'administrador');

        $this->actingAs($administrator)->post(route('users.store'), $this->validData())
            ->assertRedirect(route('users.create'))
            ->assertSessionHas('status', 'La cuenta de Nueva persona fue creada.');

        $created = User::query()->where('email', 'nueva@example.test')->sole();
        self::assertSame(UserRole::Editor, $created->role);
        self::assertTrue(Hash::check('ClaveSegura123!', $created->password));
        self::assertDatabaseHas('security_events', [
            'actor_id' => $administrator->id,
            'target_user_id' => $created->id,
            'action' => SecurityEventAction::UserCreated->value,
        ]);
    }

    public function test_administrator_cannot_create_privileged_role(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'administrador');
        $data = array_replace($this->validData(), ['role' => UserRole::Administrator->value]);

        $this->actingAs($administrator)->post(route('users.store'), $data)->assertForbidden();
        self::assertDatabaseCount('users', 1);
        self::assertDatabaseCount('security_events', 0);
    }

    public function test_request_denies_role_outside_actor_scope(): void
    {
        $administrator = $this->user(UserRole::Administrator, 'administrador');
        $request = CreateUserRequest::create('/', 'POST', ['role' => UserRole::Administrator->value]);
        $request->setUserResolver(fn (): User => $administrator);

        self::assertFalse($request->authorize());
    }

    public function test_invalid_data_returns_spanish_errors_without_creating_user(): void
    {
        $superAdministrator = $this->user(UserRole::SuperAdministrator, 'super');
        $data = array_replace($this->validData(), [
            'email' => 'correo-inválido',
            'password_confirmation' => 'OtraClaveSegura123!',
            'role' => 'propietario',
        ]);

        $this->actingAs($superAdministrator)->from(route('users.create'))->post(route('users.store'), $data)
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors(['email', 'password', 'role']);
        $this->get(route('users.create'))
            ->assertSee('Ingresa un correo electrónico válido.')
            ->assertSee('Las contraseñas no coinciden.')
            ->assertSee('Selecciona un rol permitido.')
            ->assertDontSee('ClaveSegura123!');
        self::assertDatabaseCount('users', 1);
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

    private function validData(): array
    {
        return [
            'name' => 'Nueva persona',
            'email' => 'nueva@example.test',
            'password' => 'ClaveSegura123!',
            'password_confirmation' => 'ClaveSegura123!',
            'role' => UserRole::Editor->value,
        ];
    }
}
