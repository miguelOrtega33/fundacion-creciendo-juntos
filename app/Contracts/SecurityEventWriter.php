<?php

namespace App\Contracts;

use App\Enums\SecurityEventAction;
use App\Models\User;

interface SecurityEventWriter
{
    /** @param array<string, mixed> $previousValues @param array<string, mixed> $newValues */
    public function record(
        User $actor,
        User $target,
        SecurityEventAction $action,
        array $previousValues,
        array $newValues,
    ): int;
}
