<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLE_CHECK = 'users_role_check';

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default(UserRole::Editor->value);
            $table->timestamp('disabled_at')->nullable();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(sprintf(
                "ALTER TABLE users ADD CONSTRAINT %s CHECK (role IN ('%s', '%s', '%s'))",
                self::ROLE_CHECK,
                UserRole::Editor->value,
                UserRole::Administrator->value,
                UserRole::SuperAdministrator->value,
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
