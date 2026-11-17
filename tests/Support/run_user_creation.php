<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\UserCreator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__, 2).'/vendor/autoload.php';
(require dirname(__DIR__, 2).'/bootstrap/app.php')->make(Kernel::class)->bootstrap();

if (getenv('USER_CREATOR_MODE') === 'degrade') {
    DB::transaction(function (): void {
        DB::table('users')->where('id', getenv('USER_CREATOR_ACTOR_ID'))
            ->update(['role' => UserRole::Editor->value]);
        echo 'actualizado';
        fflush(STDOUT);
        usleep(1_500_000);
    });
    exit;
}

$startAt = (float) getenv('USER_CREATOR_START_AT');
while (microtime(true) < $startAt) {
    usleep(1000);
}

try {
    app(UserCreator::class)->create(
        User::findOrFail((int) getenv('USER_CREATOR_ACTOR_ID')),
        UserRole::Editor,
        ['name' => 'Cuenta concurrente', 'email' => getenv('USER_CREATOR_EMAIL'), 'password' => 'ClaveSegura123!'],
    );
    echo 'creado';
} catch (ValidationException $exception) {
    echo isset($exception->errors()['email']) ? 'correo_duplicado' : 'validacion';
} catch (Throwable $exception) {
    echo 'error:'.$exception::class;
}
