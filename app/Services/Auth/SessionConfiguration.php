<?php

namespace App\Services\Auth;

use LogicException;

final class SessionConfiguration
{
    public function assertCompatible(): void
    {
        $primaryConnection = config('database.default');
        $sessionConnection = config('session.connection');

        if (config('session.driver') !== 'database') {
            throw new LogicException('La aplicación requiere SESSION_DRIVER=database.');
        }

        if (config('session.table') !== 'sessions') {
            throw new LogicException('La aplicación requiere SESSION_TABLE=sessions.');
        }

        if (! is_string($primaryConnection) || $primaryConnection === '') {
            throw new LogicException('La conexión principal de base de datos no está configurada.');
        }

        if ($sessionConnection !== null && $sessionConnection !== $primaryConnection) {
            throw new LogicException(
                'Las sesiones deben usar la conexión principal porque su tabla se migra allí.',
            );
        }

    }

    public function primaryConnection(): string
    {
        $this->assertCompatible();

        return config('database.default');
    }

    public function table(): string
    {
        $this->assertCompatible();

        return 'sessions';
    }
}
