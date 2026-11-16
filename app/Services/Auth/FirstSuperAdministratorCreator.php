<?php

namespace App\Services\Auth;

use App\Contracts\SystemSecurityEventWriter;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use LogicException;
use RuntimeException;

final readonly class FirstSuperAdministratorCreator
{
    private const LOCK_NAME = 'fundacion:first-super-administrator';

    private const LOCK_TIMEOUT_SECONDS = 10;

    public function __construct(private SystemSecurityEventWriter $events) {}

    /** @param array{name: string, email: string, password: string} $attributes */
    public function create(array $attributes): User
    {
        $validated = Validator::make($attributes, UserRules::create())->validate();

        return $this->withBootstrapLock(fn (): User => DB::transaction(function () use ($validated): User {
            if (User::query()
                ->where('role', UserRole::SuperAdministrator->value)
                ->whereNull('disabled_at')
                ->exists()) {
                throw new LogicException('Ya existe un superadministrador activo.');
            }

            $user = User::create($validated);
            $user->forceFill(['role' => UserRole::SuperAdministrator])->save();
            $this->events->recordFirstSuperAdministratorCreation($user);

            return $user;
        }, 3));
    }

    /** @template T @param callable(): T $operation @return T */
    private function withBootstrapLock(callable $operation): mixed
    {
        if (DB::getDriverName() !== 'mysql') {
            return $operation();
        }

        $lock = DB::selectOne('SELECT GET_LOCK(?, ?) AS acquired', [
            self::LOCK_NAME,
            self::LOCK_TIMEOUT_SECONDS,
        ]);

        if ((int) ($lock->acquired ?? 0) !== 1) {
            throw new RuntimeException('No fue posible obtener el bloqueo de arranque.');
        }

        try {
            return $operation();
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [self::LOCK_NAME]);
        }
    }
}
