<?php

namespace Tests\Feature\Console;

use App\Enums\UserRole;
use App\Services\Auth\FirstSuperAdministratorCreator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CreateFirstSuperAdministratorCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_first_super_administrator_with_hidden_confirmation(): void
    {
        $password = 'ClaveSegura123!';

        $this->artisan('app:create-first-super-administrator', [
            'name' => 'Administración inicial',
            'email' => 'admin@example.test',
        ])
            ->expectsQuestion('Contraseña', $password)
            ->expectsQuestion('Confirma la contraseña', $password)
            ->expectsOutput('Primer superadministrador creado correctamente.')
            ->doesntExpectOutputToContain($password)
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'role' => UserRole::SuperAdministrator->value,
        ]);
        $this->assertDatabaseCount('security_events', 1);
    }

    public function test_rejects_password_confirmation_mismatch(): void
    {
        $this->artisan('app:create-first-super-administrator', [
            'name' => 'Administración inicial',
            'email' => 'admin@example.test',
        ])
            ->expectsQuestion('Contraseña', 'ClaveSegura123!')
            ->expectsQuestion('Confirma la contraseña', 'ClaveDistinta123!')
            ->expectsOutput('Las contraseñas no coinciden.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    #[DataProvider('invalidInput')]
    public function test_rejects_invalid_input(string $name, string $email, string $password): void
    {
        $this->artisan('app:create-first-super-administrator', compact('name', 'email'))
            ->expectsQuestion('Contraseña', $password)
            ->expectsQuestion('Confirma la contraseña', $password)
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('security_events', 0);
    }

    public function test_rejects_creation_when_an_active_super_administrator_exists(): void
    {
        app(FirstSuperAdministratorCreator::class)->create([
            'name' => 'Existente',
            'email' => 'existente@example.test',
            'password' => 'ClaveSegura123!',
        ]);

        $this->artisan('app:create-first-super-administrator', [
            'name' => 'Segundo',
            'email' => 'segundo@example.test',
        ])
            ->expectsQuestion('Contraseña', 'OtraClaveSegura123!')
            ->expectsQuestion('Confirma la contraseña', 'OtraClaveSegura123!')
            ->expectsOutput('Ya existe un superadministrador activo.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('security_events', 1);
    }

    public static function invalidInput(): array
    {
        return [
            'nombre vacío' => ['', 'admin@example.test', 'ClaveSegura123!'],
            'correo inválido' => ['Administración inicial', 'correo-inválido', 'ClaveSegura123!'],
            'contraseña corta' => ['Administración inicial', 'admin@example.test', 'muy-corta'],
        ];
    }
}
