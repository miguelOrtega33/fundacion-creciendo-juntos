<?php

namespace App\Console\Commands;

use App\Services\Auth\FirstSuperAdministratorCreator;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use LogicException;

final class CreateFirstSuperAdministrator extends Command
{
    protected $signature = 'app:create-first-super-administrator
        {name : Nombre de la persona administradora}
        {email : Correo de la persona administradora}';

    protected $description = 'Crear el primer superadministrador de la aplicación';

    public function handle(FirstSuperAdministratorCreator $creator): int
    {
        $password = $this->secret('Contraseña');
        $confirmation = $this->secret('Confirma la contraseña');

        if (! is_string($password)
            || ! is_string($confirmation)
            || ! hash_equals($password, $confirmation)) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        try {
            $creator->create([
                'name' => (string) $this->argument('name'),
                'email' => (string) $this->argument('email'),
                'password' => $password,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        } catch (LogicException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Primer superadministrador creado correctamente.');

        return self::SUCCESS;
    }
}
