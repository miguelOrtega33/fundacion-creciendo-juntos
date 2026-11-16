<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\SessionConfiguration;
use App\Services\Auth\UserSessionRevoker;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

final class SessionConfigurationTest extends TestCase
{
    public function test_requires_the_database_session_driver(): void
    {
        config(['session.driver' => 'file']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('SESSION_DRIVER=database');

        app(SessionConfiguration::class)->assertCompatible();
    }

    public function test_requires_the_migrated_session_table(): void
    {
        config([
            'session.driver' => 'database',
            'session.table' => 'user_sessions',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('SESSION_TABLE=sessions');

        app(SessionConfiguration::class)->assertCompatible();
    }

    public function test_rejects_a_separate_session_connection(): void
    {
        config([
            'database.default' => 'mysql',
            'session.driver' => 'database',
            'session.connection' => 'sessions',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Las sesiones deben usar la conexión principal');

        app(SessionConfiguration::class)->primaryConnection();
    }

    public function test_rejects_revocation_for_an_unsaved_user(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Solo se pueden revocar sesiones de usuarios persistidos.');

        app(UserSessionRevoker::class)->revoke(new User);
    }
}
