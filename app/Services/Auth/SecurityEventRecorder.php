<?php

namespace App\Services\Auth;

use App\Contracts\SecurityEventWriter;
use App\Contracts\SystemSecurityEventWriter;
use App\Enums\SecurityEventAction;
use App\Enums\UserRole;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class SecurityEventRecorder implements SecurityEventWriter, SystemSecurityEventWriter
{
    /** @param array<string, mixed> $previousValues @param array<string, mixed> $newValues */
    public function record(
        User $actor,
        User $target,
        SecurityEventAction $action,
        array $previousValues,
        array $newValues,
    ): int {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('El evento de seguridad requiere una transacción activa.');
        }

        if (! $actor->exists || ! $target->exists) {
            throw new InvalidArgumentException('Actor y objetivo deben estar persistidos.');
        }

        $this->validateSnapshots($action, $previousValues, $newValues);

        return DB::table('security_events')->insertGetId([
            'actor_id' => $actor->getKey(),
            'target_user_id' => $target->getKey(),
            'action' => $action->value,
            'previous_values' => json_encode($previousValues, JSON_THROW_ON_ERROR),
            'new_values' => json_encode($newValues, JSON_THROW_ON_ERROR),
            'result' => 'exitoso',
            'occurred_at' => now(),
        ]);
    }

    public function recordFirstSuperAdministratorCreation(User $target): int
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('El evento de seguridad requiere una transacción activa.');
        }

        if (! $target->exists) {
            throw new InvalidArgumentException('El objetivo debe estar persistido.');
        }

        if ($target->role !== UserRole::SuperAdministrator || $target->disabled_at !== null) {
            throw new InvalidArgumentException('El arranque solo registra un superadministrador activo.');
        }

        return DB::table('security_events')->insertGetId([
            'actor_id' => null,
            'target_user_id' => $target->getKey(),
            'action' => SecurityEventAction::UserCreated->value,
            'previous_values' => json_encode([], JSON_THROW_ON_ERROR),
            'new_values' => json_encode(['role' => $target->role->value, 'disabled_at' => null], JSON_THROW_ON_ERROR),
            'result' => 'exitoso',
            'occurred_at' => now(),
        ]);
    }

    /** @param array<string, mixed> $previous @param array<string, mixed> $new */
    private function validateSnapshots(SecurityEventAction $action, array $previous, array $new): void
    {
        match ($action) {
            SecurityEventAction::UserCreated => $this->validateCreation($previous, $new),
            SecurityEventAction::RoleAssigned => $this->validateRoleChange($previous, $new),
            SecurityEventAction::UserDisabled => $this->validateDisablement($previous, $new),
        };
    }

    private function validateCreation(array $previous, array $new): void
    {
        $this->requireExactKeys($previous, []);
        $this->requireExactKeys($new, ['role', 'disabled_at']);
        $this->requireRole($new['role']);

        if ($new['disabled_at'] !== null) {
            throw new InvalidArgumentException('Una cuenta nueva debe comenzar activa.');
        }
    }

    private function validateRoleChange(array $previous, array $new): void
    {
        $this->requireExactKeys($previous, ['role']);
        $this->requireExactKeys($new, ['role']);
        $this->requireRole($previous['role']);
        $this->requireRole($new['role']);
    }

    private function validateDisablement(array $previous, array $new): void
    {
        $this->requireExactKeys($previous, ['disabled_at']);
        $this->requireExactKeys($new, ['disabled_at']);

        if ($previous['disabled_at'] !== null || ! $this->isAtomDate($new['disabled_at'])) {
            throw new InvalidArgumentException('La desactivación requiere null y una fecha ISO 8601 válida.');
        }
    }

    private function requireExactKeys(array $values, array $keys): void
    {
        $actualKeys = array_keys($values);
        sort($actualKeys);
        sort($keys);

        if ($actualKeys !== $keys) {
            throw new InvalidArgumentException('Los campos de seguridad no coinciden con la lista permitida.');
        }
    }

    private function requireRole(mixed $role): void
    {
        if (! is_string($role) || UserRole::tryFrom($role) === null) {
            throw new InvalidArgumentException('El rol no pertenece a la lista permitida.');
        }
    }

    private function isAtomDate(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(DATE_ATOM, $value);

        return $date !== false && $date->format(DATE_ATOM) === $value;
    }
}
