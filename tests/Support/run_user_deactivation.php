<?php

use App\Models\User;
use App\Services\Auth\UserDeactivator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$startAt = (float) getenv('DEACTIVATE_START_AT');
while (microtime(true) < $startAt) {
    usleep(1000);
}

try {
    app(UserDeactivator::class)->deactivate(
        User::findOrFail((int) getenv('DEACTIVATE_ACTOR_ID')),
        User::findOrFail((int) getenv('DEACTIVATE_TARGET_ID')),
    );
    echo 'desactivado';
} catch (AuthorizationException) {
    echo 'denegado';
}
