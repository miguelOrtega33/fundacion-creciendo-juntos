<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class ForgotPasswordController
{
    private const STATUS_MESSAGE = 'Si existe una cuenta asociada, recibirás un enlace para restablecer tu contraseña.';

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->safe()->only('email'));

        return back()->with('status', self::STATUS_MESSAGE);
    }
}
