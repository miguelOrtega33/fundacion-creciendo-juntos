<?php

namespace Tests\Feature;

use App\Enums\SecurityEventAction;
use App\Models\SecurityEvent;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

final class SecurityEventModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_security_event_fields(): void
    {
        [$actor, $target] = $this->users();
        $event = SecurityEvent::findOrFail($this->insertEvent($actor, $target));

        self::assertSame(SecurityEventAction::RoleAssigned, $event->action);
        self::assertSame(['role' => 'editor'], $event->previous_values);
        self::assertSame(['role' => 'administrador'], $event->new_values);
        self::assertInstanceOf(DateTimeImmutable::class, $event->occurred_at);
    }

    public function test_relations_keep_actor_and_target_separate(): void
    {
        [$actor, $target, $third] = $this->users(3);
        $actedEvent = $this->insertEvent($actor, $target);
        $receivedEvent = $this->insertEvent($third, $actor);

        self::assertSame($actor->id, SecurityEvent::findOrFail($actedEvent)->actor->id);
        self::assertSame($target->id, SecurityEvent::findOrFail($actedEvent)->target->id);
        self::assertSame([$actedEvent], $actor->securityEventsAsActor()->pluck('id')->all());
        self::assertSame([$receivedEvent], $actor->securityEventsAsTarget()->pluck('id')->all());
    }

    public function test_rejects_changes_to_append_only_events(): void
    {
        [$actor, $target] = $this->users();
        $eventId = $this->insertEvent($actor, $target);
        $operations = [
            fn (SecurityEvent $event) => $event->forceFill(['result' => 'alterado'])->save(),
            fn (SecurityEvent $event) => $event->delete(),
            fn (SecurityEvent $event) => $event->forceFill(['result' => 'alterado'])->saveQuietly(),
            fn (SecurityEvent $event) => $event->deleteQuietly(),
            fn (SecurityEvent $event) => $event->newQuery()->whereKey($event->getKey())->update(['result' => 'alterado']),
            fn (SecurityEvent $event) => $event->newQuery()->whereKey($event->getKey())->delete(),
            fn (SecurityEvent $event) => $event->newQuery()->insert(['result' => 'alterado']),
            fn (SecurityEvent $event) => SecurityEvent::forceCreate(['result' => 'alterado']),
        ];

        foreach ($operations as $operation) {
            try {
                $operation(SecurityEvent::findOrFail($eventId));
                self::fail('La mutación append-only debía ser rechazada.');
            } catch (LogicException) {
                self::assertDatabaseHas('security_events', ['id' => $eventId, 'result' => 'exitoso']);
            }
        }
    }

    /** @return array<int, User> */
    private function users(int $count = 2): array
    {
        return collect(range(1, $count))->map(fn (int $number): User => User::create([
            'name' => 'Usuario '.$number,
            'email' => 'usuario'.$number.'.'.Str::uuid().'@example.test',
            'password' => 'ClaveSegura123!',
        ]))->all();
    }

    private function insertEvent(User $actor, User $target): int
    {
        return DB::table('security_events')->insertGetId([
            'actor_id' => $actor->id,
            'target_user_id' => $target->id,
            'action' => SecurityEventAction::RoleAssigned->value,
            'previous_values' => json_encode(['role' => 'editor'], JSON_THROW_ON_ERROR),
            'new_values' => json_encode(['role' => 'administrador'], JSON_THROW_ON_ERROR),
            'result' => 'exitoso',
            'occurred_at' => '2026-11-16 10:00:00',
        ]);
    }
}
