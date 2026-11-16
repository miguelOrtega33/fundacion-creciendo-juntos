<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_events', function (Blueprint $table): void {
            $table->foreignId('actor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('security_events')->whereNull('actor_id')->exists()) {
            throw new LogicException('No se puede revertir mientras existan eventos de arranque del sistema.');
        }

        Schema::table('security_events', function (Blueprint $table): void {
            $table->foreignId('actor_id')->nullable(false)->change();
        });
    }
};
