<?php

namespace App\Enums;

enum SecurityEventAction: string
{
    case UserCreated = 'usuario.crear';
    case RoleAssigned = 'rol.asignar';
    case UserDisabled = 'usuario.desactivar';
}
