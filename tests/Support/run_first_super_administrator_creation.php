<?php

use App\Services\Auth\FirstSuperAdministratorCreator;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$startAt = (float) getenv('BOOTSTRAP_START_AT');
while (microtime(true) < $startAt) {
    usleep(1000);
}

try {
    app(FirstSuperAdministratorCreator::class)->create([
        'name' => (string) getenv('BOOTSTRAP_NAME'),
        'email' => (string) getenv('BOOTSTRAP_EMAIL'),
        'password' => 'ClaveSegura123!',
    ]);
    echo 'creado';
} catch (LogicException) {
    echo 'denegado';
}
