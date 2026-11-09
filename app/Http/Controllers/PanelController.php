<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

final class PanelController
{
    public function __invoke(): View
    {
        return view('panel');
    }
}
