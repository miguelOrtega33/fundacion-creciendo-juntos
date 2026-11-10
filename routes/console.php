<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('about', function (): void {
    $this->comment('Fundación Creciendo Juntos: esqueleto técnico de Fase 0.');
})->purpose('Mostrar información básica de la aplicación');

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:00')
    ->withoutOverlapping();
