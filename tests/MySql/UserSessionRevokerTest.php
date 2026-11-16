<?php

namespace Tests\MySql;

use App\Models\User;
use App\Services\Auth\UserSessionRevoker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class UserSessionRevokerTest extends TestCase
{
    public function test_revokes_only_sessions_owned_by_the_target_user(): void
    {
        config(['session.connection' => null]);
        $target = $this->createUser('objetivo');
        $other = $this->createUser('otra');
        $targetSessions = [(string) Str::uuid(), (string) Str::uuid()];
        $preservedSessions = [(string) Str::uuid(), (string) Str::uuid()];

        try {
            $this->insertSession($targetSessions[0], $target->id);
            $this->insertSession($targetSessions[1], $target->id);
            $this->insertSession($preservedSessions[0], $other->id);
            $this->insertSession($preservedSessions[1], null);

            $deleted = app(UserSessionRevoker::class)->revoke($target);

            self::assertSame(2, $deleted);
            self::assertSame(0, DB::table('sessions')->whereIn('id', $targetSessions)->count());
            self::assertSame(2, DB::table('sessions')->whereIn('id', $preservedSessions)->count());
        } finally {
            DB::table('sessions')->whereIn('id', [...$targetSessions, ...$preservedSessions])->delete();
            DB::table('users')->whereIn('id', [$target->id, $other->id])->delete();
        }
    }

    private function createUser(string $name): User
    {
        return User::create([
            'name' => ucfirst($name),
            'email' => $name.'.'.Str::uuid().'@example.test',
            'password' => 'ClaveSegura123!',
        ]);
    }

    private function insertSession(string $id, ?int $userId): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'datos-ficticios',
            'last_activity' => now()->timestamp,
        ]);
    }
}
