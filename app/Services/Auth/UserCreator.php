<?php

namespace App\Services\Auth;

use App\Contracts\SecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserRules;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final readonly class UserCreator
{
    public function __construct(private SecurityEventWriter $events) {}

    /** @param array{name: string, email: string, password: string} $attributes */
    public function create(User $actor, UserRole $role, array $attributes): User
    {
        if (! $actor->exists) {
            throw new InvalidArgumentException('El actor debe estar persistido.');
        }

        try {
            return DB::transaction(function () use ($actor, $role, $attributes): User {
                $lockedActor = User::query()->lockForUpdate()->find($actor->getKey());

                if (! $lockedActor) {
                    throw new InvalidArgumentException('El actor debe existir.');
                }

                Gate::forUser($lockedActor)->authorize('create', [User::class, $role]);
                $validated = Validator::make($attributes, UserRules::create())->validate();
                $user = User::create($validated);
                $user->forceFill(['role' => $role])->save();
                $this->events->record(
                    $lockedActor,
                    $user,
                    SecurityEventAction::UserCreated,
                    [],
                    ['role' => $role->value, 'disabled_at' => null],
                );

                return $user;
            }, 3);
        } catch (QueryException $exception) {
            if (! str_contains($exception->getMessage(), 'users_email_unique')) {
                throw $exception;
            }

            throw ValidationException::withMessages(['email' => 'El correo ya está registrado.']);
        }
    }
}
