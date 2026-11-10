<?php

namespace Tests\MySql;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FailedJobPruningTest extends TestCase
{
    public function test_only_failed_jobs_older_than_retention_are_pruned(): void
    {
        $preexistingUuid = (string) Str::uuid();
        $expiredUuid = (string) Str::uuid();
        $retainedUuid = (string) Str::uuid();

        $this->insertFailedJob($preexistingUuid, 200);

        try {
            DB::beginTransaction();
            $this->insertFailedJob($expiredUuid, 169);
            $this->insertFailedJob($retainedUuid, 167);

            $this->artisan('queue:prune-failed', [
                '--hours' => 168,
            ])->assertSuccessful();

            self::assertDatabaseMissing('failed_jobs', ['uuid' => $preexistingUuid]);
            self::assertDatabaseMissing('failed_jobs', ['uuid' => $expiredUuid]);
            self::assertDatabaseHas('failed_jobs', ['uuid' => $retainedUuid]);

            DB::rollBack();

            self::assertDatabaseHas('failed_jobs', ['uuid' => $preexistingUuid]);
            self::assertDatabaseMissing('failed_jobs', ['uuid' => $expiredUuid]);
            self::assertDatabaseMissing('failed_jobs', ['uuid' => $retainedUuid]);
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            DB::table('failed_jobs')->whereIn('uuid', [
                $preexistingUuid,
                $expiredUuid,
                $retainedUuid,
            ])->delete();
        }
    }

    private function insertFailedJob(string $uuid, int $ageInHours): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'prune-test',
            'payload' => '{}',
            'exception' => 'Excepción ficticia para probar retención.',
            'failed_at' => now()->subHours($ageInHours),
        ]);
    }
}
