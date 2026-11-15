<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ADMINISTRATOR = 'administrador';

    private const EDITOR = 'editor';

    private const ROLE_CHECK = 'users_role_check';

    private const SUPER_ADMINISTRATOR = 'superadministrador';

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement(sprintf(
                "ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT '%s' CHECK (role IN ('%s', '%s', '%s'))",
                self::EDITOR,
                self::EDITOR,
                self::ADMINISTRATOR,
                self::SUPER_ADMINISTRATOR,
            ));
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('disabled_at')->nullable();
            });

            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default(self::EDITOR);
            $table->timestamp('disabled_at')->nullable();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(sprintf(
                "ALTER TABLE users ADD CONSTRAINT %s CHECK (CAST(role AS BINARY) IN ('%s', '%s', '%s'))",
                self::ROLE_CHECK,
                self::EDITOR,
                self::ADMINISTRATOR,
                self::SUPER_ADMINISTRATOR,
            ));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(sprintf('ALTER TABLE users DROP CHECK %s', self::ROLE_CHECK));
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'disabled_at']);
        });
    }
};
