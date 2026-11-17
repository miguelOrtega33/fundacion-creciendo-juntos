<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

final class UserPolicy
{
    public function before(User $actor): ?bool
    {
        return $actor->disabled_at !== null ? false : null;
    }

    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, [UserRole::Administrator, UserRole::SuperAdministrator], true);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->is($target)
            || $actor->role === UserRole::SuperAdministrator
            || ($actor->role === UserRole::Administrator && $target->role === UserRole::Editor);
    }

    public function create(User $actor, UserRole $role): bool
    {
        return $actor->role === UserRole::SuperAdministrator
            || ($actor->role === UserRole::Administrator && $role === UserRole::Editor);
    }

    public function update(User $actor, User $target): bool
    {
        return $this->view($actor, $target);
    }

    public function deactivate(User $actor, User $target): bool
    {
        if ($actor->is($target) || $target->disabled_at !== null) {
            return false;
        }

        if ($actor->role === UserRole::Administrator) {
            return $target->role === UserRole::Editor;
        }

        return $actor->role === UserRole::SuperAdministrator
            && ! $this->isLastActiveSuperAdministrator($target);
    }

    public function assignRole(User $actor, User $target, UserRole $newRole): bool
    {
        if ($actor->role !== UserRole::SuperAdministrator
            || $actor->is($target)
            || $target->disabled_at !== null) {
            return false;
        }

        return $newRole === UserRole::SuperAdministrator
            || ! $this->isLastActiveSuperAdministrator($target);
    }

    private function isLastActiveSuperAdministrator(User $target): bool
    {
        return $target->role === UserRole::SuperAdministrator
            && $target->disabled_at === null
            && User::query()
                ->where('role', UserRole::SuperAdministrator->value)
                ->whereNull('disabled_at')
                ->count() <= 1;
    }
}
