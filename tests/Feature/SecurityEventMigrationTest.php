<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SecurityEventMigrationTest extends TestCase
{
    private string $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = DB::getDefaultConnection();
        config(['database.connections.security_event_migration' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
            'prefix' => '',
        ]]);
        DB::setDefaultConnection('security_event_migration');
    }

    protected function tearDown(): void
    {
        DB::setDefaultConnection($this->originalConnection);
        DB::purge('security_event_migration');

        parent::tearDown();
    }

    public function test_migration_creates_append_only_event_storage(): void
    {
        $migration = require database_path('migrations/2026_11_16_010000_create_security_events_table.php');

        $migration->up();

        self::assertTrue(Schema::hasColumns('security_events', [
            'id',
            'actor_id',
            'target_user_id',
            'action',
            'previous_values',
            'new_values',
            'result',
            'occurred_at',
        ]));
        self::assertFalse(Schema::hasColumn('security_events', 'updated_at'));

        $migration->down();

        self::assertFalse(Schema::hasTable('security_events'));

        $migration->up();

        self::assertTrue(Schema::hasTable('security_events'));
    }
}
