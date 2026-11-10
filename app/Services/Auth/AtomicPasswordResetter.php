<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class AtomicPasswordResetter
{
    /**
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $credentials
     */
    public function reset(array $credentials): string
    {
        return DB::transaction(function () use ($credentials): string {
            $token = DB::table($this->tokenTable())
                ->where('email', $credentials['email'])
                ->lockForUpdate()
                ->first();

            if ($token === null) {
                return Password::INVALID_TOKEN;
            }

            return Password::reset(
                $credentials,
                function (User $user, string $password): void {
                    $user->forceFill([
                        'password' => $password,
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                },
            );
        }, 3);
    }

    private function tokenTable(): string
    {
        $broker = config('auth.defaults.passwords');

        return config("auth.passwords.{$broker}.table");
    }
}
