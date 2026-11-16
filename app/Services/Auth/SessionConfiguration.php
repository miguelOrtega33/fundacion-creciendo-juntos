<?php

namespace App\Services\Auth;

use LogicException;

final class SessionConfiguration
{
    public function primaryConnection(): string
    {
        $primaryConnection = config('database.default');
        $sessionConnection = config('session.connection');

        if (! is_string($primaryConnection) || $primaryConnection === '') {
            throw new LogicException('La conexión principal de base de datos no está configurada.');
        }

        if ($sessionConnection !== null && $sessionConnection !== $primaryConnection) {
            throw new LogicException(
                'Las sesiones deben usar la conexión principal porque su tabla se migra allí.',
            );
        }

        return $primaryConnection;
    }
}
