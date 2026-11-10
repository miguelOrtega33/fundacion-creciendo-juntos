<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\ForgotPasswordRequest;
use App\Jobs\SendPasswordResetLink;
use Illuminate\Http\RedirectResponse;
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
        SendPasswordResetLink::dispatch($request->string('email')->toString());

        return back()->with('status', self::STATUS_MESSAGE);
    }
}
