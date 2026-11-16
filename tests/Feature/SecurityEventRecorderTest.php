<?php

namespace Tests\Feature;

use App\Enums\SecurityEventAction;
use App\Models\User;
use App\Services\Auth\SecurityEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

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
