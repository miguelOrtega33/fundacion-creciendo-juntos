<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

final class UserRoleTest extends TestCase
{
    public function test_exposes_only_approved_persisted_values(): void
    {
        self::assertSame(
            ['editor', 'administrador', 'superadministrador'],
            array_column(UserRole::cases(), 'value'),
        );
    }
}
