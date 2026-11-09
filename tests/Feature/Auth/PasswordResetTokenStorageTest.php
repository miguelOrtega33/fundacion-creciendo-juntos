<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PasswordResetTokenStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_token_storage_has_required_columns(): void
    {
        self::assertTrue(Schema::hasTable('password_reset_tokens'));
        self::assertTrue(Schema::hasColumns('password_reset_tokens', [
            'email',
            'token',
            'created_at',
        ]));
    }
}
