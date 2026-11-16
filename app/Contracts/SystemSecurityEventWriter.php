<?php

namespace App\Contracts;

use App\Models\User;

interface SystemSecurityEventWriter
{
    public function recordFirstSuperAdministratorCreation(User $target): int;
}
