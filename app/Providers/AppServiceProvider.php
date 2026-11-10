<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Los servicios de aplicación se agregarán en fases posteriores.
    }

    public function boot(): void
    {
        Password::defaults(fn (): Password => Password::min(15)->max(64));

        RateLimiter::for('password-recovery', fn (Request $request): array => [
            Limit::perMinute(5)->by('email:'.$this->emailKey($request)),
            Limit::perMinute(30)->by('ip:'.$request->ip()),
        ]);

        RateLimiter::for('password-reset', fn (Request $request): array => [
            Limit::perMinute(5)->by('email:'.$this->emailKey($request)),
            Limit::perMinute(20)->by('ip:'.$request->ip()),
        ]);
    }

    private function emailKey(Request $request): string
    {
        $email = Str::lower(trim($request->string('email')->toString()));

        return hash('sha256', $email);
    }
}
