<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SessionMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        config(['database.connections.session_migration' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
            'prefix' => '',
        ]]);
        DB::setDefaultConnection('session_migration');
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection($this->originalConnection);
        DB::purge('session_migration');

        parent::tearDown();
    }

    public function test_migration_creates_reversible_session_storage(): void
    {
        $migration = require database_path('migrations/2026_11_16_000000_create_sessions_table.php');

        $migration->up();

        self::assertTrue(Schema::hasColumns('sessions', [
            'id',
            'user_id',
            'ip_address',
            'user_agent',
            'payload',
            'last_activity',
        ]));
        self::assertContains(
            ['user_id'],
            array_column(Schema::getIndexes('sessions'), 'columns'),
        );

        $migration->down();

        self::assertFalse(Schema::hasTable('sessions'));

        $migration->up();

        self::assertTrue(Schema::hasTable('sessions'));
    }
}
