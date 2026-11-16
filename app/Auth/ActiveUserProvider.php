<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

final class ActiveUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return $this->onlyActive(parent::retrieveById($identifier));
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return $this->onlyActive(parent::retrieveByToken($identifier, $token));
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return $this->onlyActive(parent::retrieveByCredentials($credentials));
    }

    private function onlyActive(?Authenticatable $user): ?Authenticatable
    {
        if (! $user instanceof User || $user->disabled_at !== null) {
            return null;
        }

        return $user;
    }
}
