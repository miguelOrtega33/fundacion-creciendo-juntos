<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class UserRules
{
    /**
     * @return array<string, array<int, Password|string>>
     */
    public static function create(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }
}
