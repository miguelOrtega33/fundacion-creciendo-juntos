<?php

namespace App\Enums;

enum UserRole: string
{
    case Editor = 'editor';
    case Administrator = 'administrador';
    case SuperAdministrator = 'superadministrador';
}
