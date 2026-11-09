<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;

final class LoginController
{
    public function create(): View
    {
        return view('auth.login');
    }
}
