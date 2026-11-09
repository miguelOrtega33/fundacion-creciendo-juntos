<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Los servicios de aplicación se agregarán en fases posteriores.
    }

    public function boot(): void
    {
        // La configuración común se agregará cuando exista el primer dominio.
    }
}
