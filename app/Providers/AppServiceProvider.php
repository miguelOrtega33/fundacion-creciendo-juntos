<?php

namespace App\Providers;

use App\Auth\ActiveUserProvider;
use App\Contracts\SecurityEventWriter;
use App\Contracts\SystemSecurityEventWriter;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Auth\SecurityEventRecorder;
use App\Services\Auth\SessionConfiguration;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SecurityEventWriter::class, SecurityEventRecorder::class);
        $this->app->bind(SystemSecurityEventWriter::class, SecurityEventRecorder::class);
    }

    public function boot(SessionConfiguration $sessionConfiguration): void
    {
        Auth::provider(
            'active-eloquent',
            fn ($app, array $config): ActiveUserProvider => new ActiveUserProvider($app['hash'], $config['model']),
        );

        Gate::define('panel.acceder', fn (User $user): bool => $user->disabled_at === null);
        Gate::policy(User::class, UserPolicy::class);

        if (! $this->app->runningUnitTests() || config('session.driver') === 'database') {
            $sessionConfiguration->assertCompatible();
        }

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
