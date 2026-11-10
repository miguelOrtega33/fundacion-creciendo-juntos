<?php

namespace Tests\Unit\Console;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

final class ScheduleTest extends TestCase
{
    public function test_old_failed_jobs_are_pruned_daily(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (object $event): bool => str_contains(
                (string) $event->command,
                'queue:prune-failed --hours=168',
            ));

        self::assertNotNull($event);
        self::assertSame('0 3 * * *', $event->expression);
        self::assertTrue($event->withoutOverlapping);
    }
}
