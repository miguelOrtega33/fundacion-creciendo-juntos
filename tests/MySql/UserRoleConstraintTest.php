<?php

namespace Tests\MySql;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserRoleConstraintTest extends TestCase
{
    public function test_database_rejects_an_unknown_role(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Rol inválido',
            'email' => 'rol-invalido@example.test',
            'password' => 'hash-no-utilizable',
            'role' => 'propietario',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
