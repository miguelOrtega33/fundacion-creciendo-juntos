#!/bin/sh

set -eu

echo "Esperando APP_KEY y tablas de cola..."

until php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    try {
        $probe = bin2hex(random_bytes(16));
        $encryptedProbe = Illuminate\Support\Facades\Crypt::encryptString($probe);
        $keyIsUsable = hash_equals(
            $probe,
            Illuminate\Support\Facades\Crypt::decryptString($encryptedProbe),
        );
        $ready = $keyIsUsable
            && Illuminate\Support\Facades\Schema::hasTable("jobs")
            && Illuminate\Support\Facades\Schema::hasTable("failed_jobs");
    } catch (Throwable) {
        $ready = false;
    }

    exit($ready ? 0 : 1);
' >/dev/null 2>&1; do
    sleep 2
done

echo "APP_KEY utilizable y tablas de cola disponibles; iniciando worker."

exec php artisan queue:work --sleep=1 --tries=3 --timeout=30
