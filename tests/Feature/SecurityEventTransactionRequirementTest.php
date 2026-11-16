<?php

namespace Tests\Feature;

use App\Enums\SecurityEventAction;
use App\Models\User;
use App\Services\Auth\SecurityEventRecorder;
use LogicException;
use Tests\TestCase;

final class SecurityEventTransactionRequirementTest extends TestCase
{
    public function test_requires_an_active_transaction(): void
    {
        $actor = new User;
        $actor->id = 1;
        $actor->exists = true;
        $target = new User;
        $target->id = 2;
        $target->exists = true;

        $this->expectException(LogicException::class);

        app(SecurityEventRecorder::class)->record(
            $actor,
            $target,
            SecurityEventAction::RoleAssigned,
            ['role' => 'editor'],
            ['role' => 'administrador'],
        );
    }
}
