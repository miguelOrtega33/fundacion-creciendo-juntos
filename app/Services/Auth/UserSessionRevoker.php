<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class UserSessionRevoker
{
    public function __construct(private SessionConfiguration $configuration) {}

    public function revoke(User $user): int
    {
        if (! $user->exists || $user->getKey() === null) {
            throw new InvalidArgumentException('Solo se pueden revocar sesiones de usuarios persistidos.');
        }

        return DB::connection($this->configuration->primaryConnection())
            ->table(config('session.table'))
            ->where('user_id', $user->getKey())
            ->delete();
    }
}
