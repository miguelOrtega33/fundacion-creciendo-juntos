<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ResetPasswordRequest;
use App\Services\Auth\AtomicPasswordResetter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class ResetPasswordController
{
    public function __construct(private readonly AtomicPasswordResetter $passwordResetter) {}

    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->string('email')->toString(),
            'token' => $token,
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->passwordResetter->reset(
            $request->safe()->only(['email', 'password', 'password_confirmation', 'token']),
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.');
    }
}
