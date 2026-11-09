<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // La política de middleware de negocio se definirá en fases posteriores.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // El manejo específico de errores se definirá junto con la observabilidad.
    })->create();
