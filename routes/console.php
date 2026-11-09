<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about', function (): void {
    $this->comment('Fundación Creciendo Juntos: esqueleto técnico de Fase 0.');
})->purpose('Mostrar información básica de la aplicación');
