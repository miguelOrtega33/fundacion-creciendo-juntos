<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserRoleAssigner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$startAt = (float) getenv('ASSIGN_ROLE_START_AT');
while (microtime(true) < $startAt) {
    usleep(1000);
}

try {
    app(UserRoleAssigner::class)->assign(
        User::findOrFail((int) getenv('ASSIGN_ROLE_ACTOR_ID')),
        User::findOrFail((int) getenv('ASSIGN_ROLE_TARGET_ID')),
        UserRole::Editor,
    );
    echo 'asignado';
} catch (AuthorizationException) {
    echo 'denegado';
}
