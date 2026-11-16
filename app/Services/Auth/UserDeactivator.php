<?php

namespace App\Services\Auth;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final readonly class UserDeactivator
{
    public function __construct(
        private SecurityEventWriter $events,
        private UserSessionRevoker $sessions,
    ) {}

    public function deactivate(User $actor, User $target): User
    {
        if (! $actor->exists || ! $target->exists) {
            throw new InvalidArgumentException('Actor y objetivo deben estar persistidos.');
        }

        return DB::transaction(function () use ($actor, $target): User {
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

            $this->authorize($lockedActor, $lockedTarget);

            if ($lockedTarget->role === UserRole::SuperAdministrator) {
                $activeSuperAdministrators = User::query()
                    ->where('role', UserRole::SuperAdministrator->value)
                    ->whereNull('disabled_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($activeSuperAdministrators->count() <= 1) {
                    throw new AuthorizationException('No se puede desactivar al último superadministrador activo.');
                }
            }

            $disabledAt = now();
            $lockedTarget->forceFill([
                'disabled_at' => $disabledAt,
                'remember_token' => null,
            ])->save();
            $this->sessions->revoke($lockedTarget);
            DB::table('password_reset_tokens')->where('email', $lockedTarget->email)->delete();
            $this->events->record(
                $lockedActor,
                $lockedTarget,
                SecurityEventAction::UserDisabled,
                ['disabled_at' => null],
                ['disabled_at' => $disabledAt->toAtomString()],
            );

            return $lockedTarget;
        }, 3);
    }

    private function authorize(User $actor, User $target): void
    {
        if ($actor->disabled_at !== null) {
            throw new AuthorizationException('Una cuenta desactivada no puede administrar usuarios.');
        }

        if ($actor->is($target)) {
            throw new AuthorizationException('No puedes desactivar tu propia cuenta.');
        }

        if ($target->disabled_at !== null) {
            throw new LogicException('La cuenta ya está desactivada.');
        }

        $allowed = $actor->role === UserRole::SuperAdministrator
            || ($actor->role === UserRole::Administrator && $target->role === UserRole::Editor);

        if (! $allowed) {
            throw new AuthorizationException('No tienes permiso para desactivar esta cuenta.');
        }
    }
}
