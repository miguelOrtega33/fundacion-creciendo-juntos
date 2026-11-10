<?php

use App\Services\Auth\AtomicPasswordResetter;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$startAt = (float) getenv('RESET_START_AT');

while (microtime(true) < $startAt) {
    usleep(1000);
}

$password = (string) getenv('RESET_PASSWORD');
$status = $app->make(AtomicPasswordResetter::class)->reset([
    'email' => (string) getenv('RESET_EMAIL'),
    'password' => $password,
    'password_confirmation' => $password,
    'token' => (string) getenv('RESET_TOKEN'),
]);

echo $status;
