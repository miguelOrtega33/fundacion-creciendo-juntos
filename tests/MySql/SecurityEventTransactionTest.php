<?php

namespace Tests\MySql;

use App\Enums\SecurityEventAction;
use App\Models\User;
use App\Services\Auth\SecurityEventRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SecurityEventTransactionTest extends TestCase
{
    public function test_event_rolls_back_with_the_privileged_change(): void
    {
        $actor = $this->createUser('actor');
        $target = $this->createUser('objetivo');

        try {
            DB::beginTransaction();
            $eventId = app(SecurityEventRecorder::class)->record(
                $actor,
                $target,
                SecurityEventAction::RoleAssigned,
                ['role' => 'editor'],
                ['role' => 'administrador'],
            );
            self::assertSame(1, DB::table('security_events')->where('id', $eventId)->count());

            DB::rollBack();

            self::assertSame(0, DB::table('security_events')->where('id', $eventId)->count());
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            DB::table('security_events')->where('actor_id', $actor->id)->delete();
            DB::table('users')->whereIn('id', [$actor->id, $target->id])->delete();
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
}
