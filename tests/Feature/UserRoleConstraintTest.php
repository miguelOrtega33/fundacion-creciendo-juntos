<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class UserRoleConstraintTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidRoles')]
    public function test_sqlite_rejects_an_invalid_role(string $role): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Rol inválido',
            'email' => 'rol-invalido@example.test',
            'password' => 'hash-no-utilizable',
            'role' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array<string, array{string}> */
    public static function invalidRoles(): array
    {
        return [
            'desconocido' => ['propietario'],
            'mayúsculas' => ['EDITOR'],
            'espacio final' => ['editor '],
        ];
    }
}
