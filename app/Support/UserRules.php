<?php

namespace App\Support;

final class UserRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function create(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
