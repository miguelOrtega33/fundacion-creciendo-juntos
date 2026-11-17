<?php

namespace App\Services\Auth;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use LogicException;

final readonly class UserRoleAssigner
{
    public function __construct(
        private SecurityEventWriter $events,
        private UserSessionRevoker $sessions,
    ) {}

    public function assign(User $actor, User $target, UserRole $newRole): User
    {
        if (! $actor->exists || ! $target->exists) {
            throw new InvalidArgumentException('Actor y objetivo deben estar persistidos.');
        }

        return DB::transaction(function () use ($actor, $target, $newRole): User {
            $users = User::query()
                ->whereKey([$actor->getKey(), $target->getKey()])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy(fn (User $user): int => $user->getKey());
            $lockedActor = $users->get($actor->getKey());
            $lockedTarget = $users->get($target->getKey());

            if (! $lockedActor || ! $lockedTarget) {
                throw new InvalidArgumentException('Actor y objetivo deben existir.');
            }

            Gate::forUser($lockedActor)->authorize('assignRole', [$lockedTarget, $newRole]);

            if ($lockedTarget->role === $newRole) {
                throw new LogicException('La cuenta ya tiene el rol solicitado.');
            }

            if ($lockedTarget->role === UserRole::SuperAdministrator) {
                $activeSuperAdministrators = User::query()
                    ->where('role', UserRole::SuperAdministrator->value)
                    ->whereNull('disabled_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($activeSuperAdministrators->count() <= 1) {
                    throw new AuthorizationException('No se puede degradar al último superadministrador activo.');
                }
            }

            $previousRole = $lockedTarget->role;
            $lockedTarget->forceFill([
                'role' => $newRole,
                'remember_token' => null,
            ])->save();
            $this->sessions->revoke($lockedTarget);
            $this->events->record(
                $lockedActor,
                $lockedTarget,
                SecurityEventAction::RoleAssigned,
                ['role' => $previousRole->value],
                ['role' => $newRole->value],
            );

            return $lockedTarget;
        }, 3);
    }
}
