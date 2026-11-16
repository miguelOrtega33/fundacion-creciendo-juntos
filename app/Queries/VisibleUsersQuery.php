<?php

namespace App\Queries;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class VisibleUsersQuery
{
    /** @return Builder<User> */
    public function for(User $actor): Builder
    {
        $query = User::query()->orderBy('id');

        if ($actor->disabled_at !== null || $actor->role === UserRole::Editor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->role === UserRole::Administrator) {
            return $query->where('role', UserRole::Editor->value);
        }

        return $query;
    }
}
