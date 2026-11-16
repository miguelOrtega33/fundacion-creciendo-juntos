<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class RoleMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        config(['database.connections.role_migration' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
            'prefix' => '',
        ]]);
        DB::setDefaultConnection('role_migration');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection($this->originalConnection);
        DB::purge('role_migration');

        parent::tearDown();
    }

    public function test_migration_up_down_and_up_preserves_safe_defaults(): void
    {
        DB::table('users')->insert([
            'name' => 'Usuario existente',
            'email' => 'existente@example.test',
            'password' => 'hash-no-utilizable',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $migration = require database_path('migrations/2026_11_13_000000_add_role_and_disabled_at_to_users_table.php');

        $migration->up();

        self::assertSame('editor', DB::table('users')->value('role'));
        self::assertNull(DB::table('users')->value('disabled_at'));
        foreach (['administrador', 'superadministrador'] as $role) {
            DB::table('users')->insert([
                'name' => 'Rol válido',
                'email' => $role.'@example.test',
                'password' => 'hash-no-utilizable',
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $migration->down();

        self::assertFalse(Schema::hasColumn('users', 'role'));
        self::assertFalse(Schema::hasColumn('users', 'disabled_at'));

        $migration->up();

        self::assertTrue(Schema::hasColumn('users', 'role'));
        self::assertSame(3, DB::table('users')->where('role', 'editor')->count());
    }
}
