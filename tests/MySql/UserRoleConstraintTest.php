<?php

namespace Tests\MySql;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class UserRoleConstraintTest extends TestCase
{
    #[DataProvider('invalidRoles')]
    public function test_database_rejects_an_invalid_role(string $role): void
    {
        $email = 'rol-invalido.'.Str::uuid().'@example.test';

        try {
            DB::table('users')->insert([
                'name' => 'Rol inválido',
                'email' => $email,
                'password' => 'hash-no-utilizable',
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            self::fail('MySQL aceptó un rol que debía rechazar.');
        } catch (QueryException $exception) {
            self::assertStringContainsString('users_role_check', $exception->getMessage());
        } finally {
            DB::table('users')->where('email', $email)->delete();
        }
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
