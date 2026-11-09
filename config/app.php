<?php

return [
    'name' => env('APP_NAME', 'Fundación Creciendo Juntos'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8080'),
    'timezone' => 'America/Santiago',
    'locale' => 'es',
    'fallback_locale' => 'es',
    'faker_locale' => 'es_CL',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'maintenance' => [
        'driver' => 'file',
    ],
];
