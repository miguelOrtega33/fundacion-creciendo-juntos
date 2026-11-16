<?php

namespace Tests\Feature\Authorization;

use App\Enums\UserRole;
use App\Models\User;
use App\Queries\VisibleUsersQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class VisibleUsersQueryTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('visibilityScenarios')]
    public function test_filters_visible_users_in_the_database(UserRole $role, bool $disabled): void
    {
        $actor = $this->user('actor', $role);
        $editor = $this->user('editor', UserRole::Editor);
        $administrator = $this->user('administrador', UserRole::Administrator);
        $superAdministrator = $this->user('super', UserRole::SuperAdministrator);

        if ($disabled) {
            $actor->forceFill(['disabled_at' => now()])->save();
        }

        $query = app(VisibleUsersQuery::class)->for($actor);
        $expectedIds = match (true) {
            $disabled, $role === UserRole::Editor => [],
            $role === UserRole::Administrator => [$editor->id],
            default => [$actor->id, $editor->id, $administrator->id, $superAdministrator->id],
        };

        self::assertSame($expectedIds, $query->pluck('id')->all());

        if ($disabled || $role === UserRole::Editor) {
            self::assertStringContainsString('1 = 0', $query->toSql());
        } elseif ($role === UserRole::Administrator) {
            self::assertStringContainsString('"role" = ?', $query->toSql());
            self::assertSame([UserRole::Editor->value], $query->getBindings());
        } else {
            self::assertStringNotContainsString(' where ', $query->toSql());
        }
    }

    public static function visibilityScenarios(): array
    {
        return [
            'editor' => [UserRole::Editor, false],
            'administrador' => [UserRole::Administrator, false],
            'superadministrador' => [UserRole::SuperAdministrator, false],
            'cuenta desactivada' => [UserRole::SuperAdministrator, true],
        ];
    }

    private function user(string $name, UserRole $role): User
    {
        $user = User::create([
            'name' => ucfirst($name),
            'email' => $name.'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
